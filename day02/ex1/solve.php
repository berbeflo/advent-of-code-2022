<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_IGNORE_NEW_LINES);

$pointTableChosen = [
    'X' => 1,
    'Y' => 2,
    'Z' => 3,
];

$pointTableWinCondition = [
    'AX' => 3,
    'AY' => 6,
    'AZ' => 0,

    'BX' => 0,
    'BY' => 3,
    'BZ' => 6,

    'CX' => 6,
    'CY' => 0,
    'CZ' => 3
];

$sum = 0;

foreach ($input as $matchup) {
    [$enemy, $player] = explode(' ', $matchup);

    $sum += ($pointTableChosen[$player] + $pointTableWinCondition[$enemy . $player]);
}

echo $sum;