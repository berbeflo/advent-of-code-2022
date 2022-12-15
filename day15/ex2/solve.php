<?php

use Coordinate as GlobalCoordinate;

$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$sensors = [];
$beacons = [];
$minX = PHP_INT_MAX;
$maxX = PHP_INT_MIN;

foreach ($input as $line) {
    preg_match_all('/\-?\d+/', $line, $matches);
    [$xPosSensor, $yPosSensor, $xPosBeacon, $yPosBeacon] = $matches[0];

    $sensor = new Sensor($xPosSensor, $yPosSensor);
    $beacon = new Coordinate($xPosBeacon, $yPosBeacon);

    $sensor->setCoveredDistance(calcManhattanDistance($sensor, $beacon));
    $minX = min($sensor->xPos - $sensor->coveredDistance, $minX);
    $maxX = max($sensor->xPos + $sensor->coveredDistance, $maxX);

    $sensors[] = $sensor;
    $beacons[] = $beacon;
}

$minPos = 0;
$maxPos = 4000000;
foreach ($sensors as $sensor) {
    foreach (getSurroundingCoordinates($sensor) as $coordinate) {
        if (
            $coordinate->xPos < $minPos ||
            $coordinate->xPos > $maxPos ||
            $coordinate->yPos < $minPos ||
            $coordinate->yPos > $maxPos
        ) {
            continue;
        }
        foreach ($sensors as $testSensor) {
            if (calcManhattanDistance($coordinate, $testSensor) <= $testSensor->coveredDistance) {
                continue 2;
            }
        }

        echo $coordinate->xPos * 4000000 + $coordinate->yPos;
        exit;
    }
}

function calcManhattanDistance(Coordinate $first, Coordinate $second) : int
{
    $distX = abs($first->xPos - $second->xPos);
    $distY = abs($first->yPos - $second->yPos);

    return $distX + $distY;
}

function getSurroundingCoordinates(Sensor $sensor) : Generator
{
    $coordinates = [];

    $distance = $sensor->coveredDistance + 1;
    for ($addX = 0; $addX <= $distance; $addX++) {
        $addY = $distance - $addX;
        yield new Coordinate($sensor->xPos + $addX, $sensor->yPos + $addY);
        yield new Coordinate($sensor->xPos + $addX, $sensor->yPos - $addY);
        yield new Coordinate($sensor->xPos - $addX, $sensor->yPos + $addY);
        yield new Coordinate($sensor->xPos - $addX, $sensor->yPos - $addY);
    }
}

class Coordinate
{
    public function __construct(public readonly int $xPos, public readonly int $yPos)
    {
    }
}

class Sensor extends Coordinate
{
    public readonly int $coveredDistance;

    public function setCoveredDistance(int $coveredDistance) : void
    {
        $this->coveredDistance = $coveredDistance;
    }
}