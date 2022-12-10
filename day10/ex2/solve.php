<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$start = new Noop();
$current = $start;
$register = new Register();

$image = array_fill(0, 240, '.');
$sprite = new Sprite($register);

foreach ($input as $line) {
    if ($line === 'noop') {
        $current = $current->setNext(new Noop());
        continue;
    }

    preg_match('/addx (?<value>\-?\d+)/', $line, $matches);
    $current = $current->setNext(new Noop())->setNext(new AddX((int) $matches['value']));
}

$current = $start;
for ($instruction = 0; $current !== null; $instruction++) {
    if ($instruction > 0) {
        $position = translateCycleToPosition($instruction);
        if ($sprite->isPositionInSprite($position)) {
            $image[$position] = '#';
        }

        if ($instruction % 40 === 0) {
            $sprite->nextLine();
        }
    }

    $current->execute($register);
    $current = $current->getNextInstruction();
}

foreach ($image as $position => $pixel) {
    if ($position > 0 && $position % 40 === 0) {
        echo "\n";
    }
    echo $pixel;
}

class Register
{
    public int $x = 1;
}

abstract class Instruction
{
    private ?Instruction $next = null;

    public function __construct()
    {
    }

    public function setNext(Instruction $next) : self
    {
        $this->next = $next;

        return $this->next;
    }

    public function getNextInstruction() : ?Instruction
    {
        return $this->next;
    }

    public abstract function execute(Register $register): void;
}

class Noop extends Instruction
{
    public function execute(Register $register): void
    {
        return;
    }
}

class AddX extends Instruction
{
    public function __construct(private int $valueToAdd)
    {
    }

    public function execute(Register $register): void
    {
        $register->x += $this->valueToAdd;
    }
}

class Sprite
{
    private int $line = 0;

    public function __construct(private Register $register)
    {
    }

    private function getMiddle() : int
    {
        return ($this->line * 40) + $this->register->x;
    }

    public function isPositionInSprite(int $position) : bool
    {
        return ($this->getMiddle() - 1 <= $position && $this->getMiddle() + 1 >= $position);
    }

    public function nextLine() : void
    {
        $this->line++;
    }
}

function translateCycleToPosition(int $cycle) : int
{
    return $cycle - 1;
}