<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$field = array_map('str_split', $input);

define('HEIGHT', count($field));
define('WIDTH', count($field[0]));
define('FIELD', $field);

$startIndex = findStartIndex($field);

$steps = 0;
$currentIteration = [$startIndex];
$nextIteration = [];
$visited = [$startIndex => true];

while (true) {
    while (($currentIndex = array_shift($currentIteration)) !== null) {
        if (isDestination($currentIndex, $field)) {
            echo $steps;
            exit;
        }
        $currentHeight = getHeight($currentIndex, $field);
        foreach (getNeighboursFromIndex($currentIndex) as $neighbourIndex) {
            if (array_key_exists($neighbourIndex, $visited)) {
                continue;
            }

            $neighbourHeight = getHeight($neighbourIndex, $field);

            if ($currentHeight > ($neighbourHeight + 1)) {
                continue;
            }

            $nextIteration[] = $neighbourIndex;
            $visited[$neighbourIndex] = true;
        }
    }

    $currentIteration = $nextIteration;
    $nextIteration = [];
    $steps++;
}

function isDestination(int $index, array &$field) : bool
{
    [$yPos, $xPos] = getPositionFromIndex($index);
    
    return $field[$yPos][$xPos] === 'a' || $field[$yPos][$xPos] === 'S';
}

function getHeight(int $index, array &$field) : int
{
    [$yPos, $xPos] = getPositionFromIndex($index);
    $height = $field[$yPos][$xPos];

    if ($height === 'S') {
        $height = 'a';
    }

    if ($height === 'E') {
        $height = 'z';
    }

    return ord($height);
}

function findStartIndex(array &$field) : int
{
    for ($yPos = 0; $yPos < HEIGHT; $yPos++) {
        for ($xPos = 0; $xPos < WIDTH; $xPos++) {
            $value = $field[$yPos][$xPos];
            if ($value === 'E') {
                return $yPos * WIDTH + $xPos;
            }
        }
    }

    throw new DomainException('There must be a starting point!');
}

function getNeighboursFromIndex(int $index) : array
{
    $position = getPositionFromIndex($index);
    $neighbours = [
        getIndexFromPosition([$position[0]-1,$position[1]]),
        getIndexFromPosition([$position[0], $position[1]+1]),
        getIndexFromPosition([$position[0]+1, $position[1]]),
        getIndexFromPosition([$position[0], $position[1]-1]),
    ];

    return array_filter($neighbours, fn ($val) => $val !== null);
}

function getPositionFromIndex(int $index) : array
{
    return [intdiv($index, WIDTH), $index % WIDTH];
}

function getIndexFromPosition(array $position) : ?int
{
    if ($position[0] < 0 || $position[1] < 0) {
        return null;
    }

    if ($position[0] >= HEIGHT || $position[1] >= WIDTH) {
        return null;
    }

    return WIDTH * $position[0] + $position[1];
}