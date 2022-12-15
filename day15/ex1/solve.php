<?php
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

$rowToCheck = 2000000;
$coveredFields = 0;
for ($xPos = $minX; $xPos <= $maxX; $xPos++) {
    $coordinate = new Coordinate($xPos, $rowToCheck);
    foreach ($beacons as $beacon) {
        if (calcManhattanDistance($coordinate, $beacon) === 0) {
            continue 2;
        }
    }
    foreach ($sensors as $sensor) {
        if (calcManhattanDistance($coordinate, $sensor) <= $sensor->coveredDistance) {
            $coveredFields++;
            continue 2;
        }
    }
}

echo PHP_EOL, $coveredFields;

function calcManhattanDistance(Coordinate $first, Coordinate $second) : int
{
    $distX = abs($first->xPos - $second->xPos);
    $distY = abs($first->yPos - $second->yPos);

    return $distX + $distY;
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