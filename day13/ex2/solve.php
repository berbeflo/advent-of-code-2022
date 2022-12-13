<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$packets = [];
$packets[] = parseLine('[[2]]', true);
$packets[] = parseLine('[[6]]', true);

foreach ($input as $line) {
    $packets[] = parseLine($line);
}

usort($packets, compare(...));
$packets = array_filter($packets, fn (ValueList $packet) : bool => $packet->divider());
[$divider1, $divider2] = array_keys($packets);

echo ($divider1 + 1) * ($divider2 + 1);

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

    $left->backup();
    $right->backup();
    while (true) {
        $result = compare($left->removeFirst(), $right->removeFirst());
        if ($result !== 0) {
            $left->restore();
            $right->restore();
            return $result;
        }

        if (!$left->hasElements() && !$right->hasElements()) {
            $left->restore();
            $right->restore();
            return 0;
        }
    }
}

function parseLine(string $line, bool $isDivider = false) : ValueList
{
    $current = null;
    preg_match_all('/(\d+|\[|\])/', $line, $matches);
    foreach ($matches[0] as $element) {
        switch ($element) {
            case '[':
                $valueList = new ValueList($current, $isDivider);
                $isDivider = false;
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
    private array $backupList = [];

    public function __construct(private readonly ?ValueList $parent = null, private readonly bool $isDivider = false)
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

    public function divider() : bool
    {
        return $this->isDivider;
    }

    public function backup() : void
    {
        $this->backupList = $this->list;
    }

    public function restore() : void
    {
        $this->list = $this->backupList;
    }
}