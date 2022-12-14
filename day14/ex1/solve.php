<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$grid = ['500_0' => '+'];

foreach ($input as $line) {
    $grid = array_merge($grid, parseLine($line));
}

$borders = getBorders($grid);

while (true) {
    $sandPosition = [500, 0];
    while (true) {
        $nextPositions = calculateNextPositions($sandPosition);
        foreach ($nextPositions as $nextPosition) {
            if (!isFree($nextPosition, $grid)) {
                continue;
            }

            if (isOut($nextPosition, $borders)) {
                break 3;
            }

            $sandPosition = $nextPosition;
            continue 2;
        }
        [$xPos, $yPos] = $sandPosition;
        $grid["{$xPos}_{$yPos}"] = 'o';

        break;
    }
}

echo count(array_filter($grid, fn ($val) => $val === 'o'));

function isOut(array $nextPosition, array $borders) : bool
{
    [$xPos, $yPos] = $nextPosition;
    ['left' => $left, 'right' => $right, 'bottom' => $bottom] = $borders;

    if ($xPos < $left) {
        return true;
    }

    if ($xPos > $right) {
        return true;
    }

    if ($yPos > $bottom) {
        return true;
    }

    return false;
}

function isFree(array $nextPosition, array &$grid) : bool
{
    [$xPos, $yPos] = $nextPosition;
    $position = "{$xPos}_{$yPos}";

    return !array_key_exists($position, $grid);
}

function calculateNextPositions(array $sandPosition) : array
{
    [$xPos, $yPos] = $sandPosition;

    return [
        [$xPos, $yPos+1],
        [$xPos-1, $yPos+1],
        [$xPos+1, $yPos+1],
    ];
}

function getBorders(array $grid) : array
{
    $positions = array_keys($grid);
    $positions = array_map(fn ($val) => explode('_', $val), $positions);
    $xPositions = array_map(intval(...), array_column($positions, 0));
    $yPositions = array_map(intval(...), array_column($positions, 1));

    return [
        'left' => min($xPositions),
        'right' => max($xPositions),
        'bottom' => max($yPositions),
    ];
}

function parseLine(string $line) : array
{
    preg_match_all('/(\d+,\d+)/', $line, $matches);

    $points = [];
    foreach ($matches[0] as $point) {
        $points[] = explode(',', $point);
    }

    $rocks = [];
    
    for ($firstPoint = 0, $secondPoint = 1; $secondPoint < count($points); $firstPoint++, $secondPoint++) {
        $lowerX = min($points[$firstPoint][0], $points[$secondPoint][0]);
        $upperX = max($points[$firstPoint][0], $points[$secondPoint][0]);
        $lowerY = min($points[$firstPoint][1], $points[$secondPoint][1]);
        $upperY = max($points[$firstPoint][1], $points[$secondPoint][1]);

        for ($xPos = $lowerX; $xPos <= $upperX; $xPos++) {
            for ($yPos = $lowerY; $yPos <= $upperY; $yPos++) {
                $rocks["{$xPos}_{$yPos}"] = '#';
            }
        }
    }
    
    return $rocks;
}