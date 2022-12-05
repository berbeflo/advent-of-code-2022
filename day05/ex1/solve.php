<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$inStackDefinition = true;
$stacks = [];
foreach ($input as $line) {
    if ($inStackDefinition) {
        $stackLevelDefinition = generateStackLevelDefinition($line);
        if (empty($stackLevelDefinition)) {
            $inStackDefinition = false;
            ksort($stacks);
            continue;
        }
        $stacks = buildStackLevel($stacks, $stackLevelDefinition);
        continue;
    }

    $moveDefinition = generateMoveDefinition($line);
    $stacks = modifyStack($stacks, $moveDefinition);
}

echo getTopCargos($stacks);

function buildStackLevel(array $stacks, array $stackLevelDefinition) : array
{
    foreach ($stackLevelDefinition as $stack => $cargo) {
        $stack = $stack + 1;
        if (!array_key_exists($stack, $stacks)) {
            $stacks[$stack] = [];
        }

        array_unshift($stacks[$stack], $cargo);
    }
    return $stacks;
}

function generateStackLevelDefinition(string $line) : array
{
    return array_filter(array_map(fn ($val) => trim($val, '[] 0123456789'), str_split($line, 4)), fn ($val) => !empty($val));
}

function generateMoveDefinition(string $line) : array
{
    static $regex = '/move (?<amount>\d+) from (?<from>\d+) to (?<to>\d+)/';
    preg_match($regex, $line, $matches);

    return array_intersect_key($matches, ['amount' => null, 'from' => null, 'to' => null]);
}

function modifyStack(array $stacks, array $moveDefinition) : array
{
    for ($step = 0; $step < $moveDefinition['amount']; $step++) {
        $cargo = array_pop($stacks[$moveDefinition['from']]);
        array_push($stacks[$moveDefinition['to']], $cargo);
    }
    return $stacks;
}

function getTopCargos(array $stacks) : string
{
    return array_reduce($stacks, function ($carry, $stack) : string {
        $cargo = array_pop($stack);
        return $carry . $cargo;
    }, '');
}