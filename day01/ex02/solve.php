<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES);
$chunks = array_reduce($input, function (array $carry, string $line) : array {
    if ($line === '') {
        $carry['current']++;
        $carry['chunks'][$carry['current']] = ['sum' => 0, 'pos' => $carry['current']];

        return $carry;
    }

    $carry['chunks'][$carry['current']]['sum'] += (int) $line;

    return $carry;
}, ['chunks' => [1 => ['sum' => 0, 'pos' => 1]], 'current' => 1]);

$chunks = $chunks['chunks'];

usort($chunks, function (array $left, array $right) : int {
    return $right['sum'] <=> $left['sum'];
});

$chunks = array_slice($chunks, 0, 3);

echo array_reduce($chunks, function (int $carry, array $chunk) : int {
    return $carry + $chunk['sum'];
}, 0);