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
$maxSum = array_reduce($chunks['chunks'], function (array $carry, array $chunk) : array {
    return $chunk['sum'] > $carry['sum'] ? $chunk : $carry;
}, ['sum' => 0, 'pos' => 0]);

echo $maxSum['sum'];