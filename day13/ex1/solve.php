<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$pairs = [];
$index = 0;
$currentPair = null;
foreach ($input as $counter => $line) {
    if ($counter % 2 === 0) {
        if ($currentPair !== null) {
            $pairs[++$index] = $currentPair;
        }
        
        $currentPair = [];
    }
    $currentPair[] = parseLine($line);
}

$pairs = array_filter($pairs, fn (array $pair) : bool => compare(... $pair) === -1 ? true : false);

echo array_sum(array_keys($pairs));

function compare(ValueList | int | null $left, ValueList | int | null $right) : int
{
    if ($left === null && $right !== null) {
        return -1;
    }

    if ($left !== null && $right === null) {
        return 1;
    }

    if ($left === null && $right === null) {
        return 0;
    }

    if (is_int($left) && is_int($right)) {
        return $left <=> $right;
    }

    if (is_int($left)) {
        $list = new ValueList(null);
        $list->add($left);

        return compare($list, $right);
    }

    if (is_int($right)) {
        $list = new ValueList(null);
        $list->add($right);

        return compare($left, $list);
    }

    while (true) {
        $result = compare($left->removeFirst(), $right->removeFirst());
        if ($result !== 0) {
            return $result;
        }

        if (!$left->hasElements() && !$right->hasElements()) {
            return 0;
        }
    }
}

function parseLine(string $line) : ValueList
{
    $current = null;
    preg_match_all('/(\d+|\[|\])/', $line, $matches);
    foreach ($matches[0] as $element) {
        switch ($element) {
            case '[':
                $valueList = new ValueList($current);
                if ($current !== null) {
                    $current->add($valueList);
                }
                $current = $valueList;
                break;
            case ']':
                $current = $current->getParent() ?? $current;
                break;
            default:
                $current->add((int) $element);
        }
    }

    return $current;
}

class ValueList
{
    private array $list = [];

    public function __construct(private readonly ?ValueList $parent = null)
    {
    }

    public function getParent() : ?ValueList
    {
        return $this->parent;
    }

    public function add(ValueList | int $element) : void
    {
        $this->list[] = $element;
    }

    public function removeFirst() : ValueList | int | null
    {
        return array_shift($this->list);
    }

    public function hasElements() : bool
    {
        return count($this->list) > 0;
    }
}