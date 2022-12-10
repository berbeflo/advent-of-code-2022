<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$start = new Noop();
$current = $start;
$register = new Register();

$signalStrengths = [];

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
    if ($instruction >= 20 && (($instruction - 20) % 40) === 0) {
        $signalStrengths[$instruction] = $instruction * $register->x;
    }

    $current->execute($register);
    $current = $current->getNextInstruction();
}

echo array_sum($signalStrengths);

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