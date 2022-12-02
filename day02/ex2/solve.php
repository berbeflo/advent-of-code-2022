<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_IGNORE_NEW_LINES);

$pointTable = [
    'AX' => 3,
    'BX' => 1,
    'CX' => 2,

    'AY' => 4,
    'BY' => 5,
    'CY' => 6,

    'AZ' => 8,
    'BZ' => 9,
    'CZ' => 7,
];

$sum = 0;

foreach ($input as $matchup) {
    [$enemy, $player] = explode(' ', $matchup);

    $sum += $pointTable[$enemy . $player];
}

echo $sum;