<?php
$lines = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$grid = [];

foreach ($lines as $cubePosition) {
    $grid[$cubePosition] = 0;
}

foreach ($grid as $cubePosition => $num) {
    $newNum = 0;
    foreach (getAdjacentFields($cubePosition) as $adjacentCube) {
        if (!array_key_exists($adjacentCube, $grid)) {
            $newNum += 1;
        }
    }
    $grid[$cubePosition] = $newNum;
}

echo array_sum($grid);

function getAdjacentFields(string $coordinate) : array
{
    [$xPos, $yPos, $zPos] = array_map(intval(...), explode(',', $coordinate));
    $xPlusOne = $xPos + 1;
    $xMinusOne = $xPos - 1;
    $yPlusOne = $yPos + 1;
    $yMinusOne = $yPos - 1;
    $zPlusOne = $zPos + 1;
    $zMinusOne = $zPos - 1;

    return [
        "{$xPos},{$yPos},{$zPlusOne}",
        "{$xPos},{$yPos},{$zMinusOne}",
        "{$xPos},{$yPlusOne},{$zPos}",
        "{$xPos},{$yMinusOne},{$zPos}",
        "{$xPlusOne},{$yPos},{$zPos}",
        "{$xMinusOne},{$yPos},{$zPos}",
    ];
}