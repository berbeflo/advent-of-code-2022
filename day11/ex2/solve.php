<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$monkeys = [];
$monkeyRelations = [];
$nextStep = 'create';
$monkey = null;
$monkeyNumber = 0;
$tests = 1;
foreach ($input as $line) {
    switch ($nextStep) {
        case 'create':
            $monkey = new Monkey();
            $monkeys[$monkeyNumber] = $monkey;
            $nextStep = 'items';
            break;
        case 'items':
            preg_match_all('/\d+/', $line, $matches);
            foreach ($matches[0] as $value) {
                $monkey->addItem((int) $value);
            }
            $nextStep = 'operation';
            break;
        case 'operation':
            preg_match('/new = old (?<operator>[*+]) (?<operand>(\d+|old))/', $line, $matches);
            ['operator' => $operator, 'operand' => $operand] = $matches;

            $callable = createFunction($operator, $operand);
            $monkey->setOperation($callable);
            $nextStep = 'test';
            break;
        case 'test':
            preg_match('/\d+/', $line, $matches);
            $monkey->setTestValue((int) $matches[0]);
            $tests *= (int) $matches[0];
            $nextStep = 'iftrue';
            break;
        case 'iftrue':
            preg_match('/\d+/', $line, $matches);
            $monkeyRelations[$monkeyNumber] = ['true' => (int) $matches[0]];
            $nextStep = 'iffalse';
            break;
        case 'iffalse':
            preg_match('/\d+/', $line, $matches);
            $monkeyRelations[$monkeyNumber]['false'] = (int) $matches[0];
            $nextStep = 'create';
            $monkeyNumber++;
            break;
    }
}

foreach ($monkeyRelations as $monkeyNumber => $relations) {
    $monkey = $monkeys[$monkeyNumber];
    $monkey->setThrowOnTrue($monkeys[$relations['true']]);
    $monkey->setThrowOnFalse($monkeys[$relations['false']]);
    $monkey->setTestModulo($tests);
}

for ($round = 0; $round < 10000; $round++) {
    foreach ($monkeys as $monkey) {
        while ($monkey->hasItems()) {
            $monkey->inspectItem();
        }
    }
}

$inspectedCount = [];
foreach ($monkeys as $monkey) {
    $inspectedCount[] = $monkey->countInspectedItems();
}

rsort($inspectedCount);

echo $inspectedCount[0] * $inspectedCount[1];

function add(int $a, int $b) : int
{
    return $a + $b;
}

function mul(int $a, int $b) : int
{
    return $a * $b;
}

function createFunction(string $operator, string $operand) : Closure
{
    $innerFunction = match ($operator) {
        '+' => add(...),
        '*' => mul(...),
    };
    
    return match ($operand) {
        'old' => fn ($old) => $innerFunction($old, $old),
        default => fn ($old) => $innerFunction($old, (int) $operand),
    };
}

class Monkey
{
    private array $items = [];
    private readonly int $testValue;
    private readonly Monkey $throwOnTrue;
    private readonly Monkey $throwOnFalse;
    private readonly Closure $operation;
    private readonly int $testModulo;

    private int $itemsInspected = 0;

    public function addItem(int $value) : void
    {
        array_push($this->items, $value);
    }

    public function setTestValue(int $testValue) : void
    {
        $this->testValue = $testValue;
    }

    public function setThrowOnTrue(Monkey $monkey) : void
    {
        $this->throwOnTrue = $monkey;
    }

    public function setThrowOnFalse(Monkey $monkey) : void
    {
        $this->throwOnFalse = $monkey;
    }

    public function setOperation(Closure $callable) : void
    {
        $this->operation = $callable;
    }

    public function hasItems() : bool
    {
        return count($this->items) > 0;
    }

    public function countInspectedItems() : int
    {
        return $this->itemsInspected;
    }

    public function setTestModulo(int $test) : void
    {
        $this->testModulo = $test;
    }

    public function inspectItem() : void
    {
        $this->itemsInspected++;
        $item = array_shift($this->items);
        $operation = $this->operation;
        $item = $operation($item);
        $item = $item % $this->testModulo;

        if ($item % $this->testValue === 0) {
            $this->throwOnTrue->addItem($item);
        } else {
            $this->throwOnFalse->addItem($item);
        }
    }
}