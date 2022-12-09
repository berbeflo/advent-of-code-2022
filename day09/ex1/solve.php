<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$headPos = new Position();
$tailPos = new Position();

$visitedCoords = [$tailPos->coords() => true];

foreach ($input as $line) {
    [$direction, $steps] = explode(' ', $line);
    $steps = (int) $steps;
    
    $methodToCall = match ($direction) {
        'U' => $headPos->moveUp(...),
        'R' => $headPos->moveRight(...),
        'D' => $headPos->moveDown(...),
        'L' => $headPos->moveLeft(...),
    };

    for ($step = 0; $step < $steps; $step++) {
        $methodToCall();
        adjustTailPosition($tailPos, $headPos);
        $visitedCoords[$tailPos->coords()] = true;
    }
}

echo count($visitedCoords);

function adjustTailPosition(Position $tailPos, Position $headPos) : void
{
    $distanceX = $headPos->getXCoordinate() - $tailPos->getXCoordinate();
    $distanceY = $headPos->getYCoordinate() - $tailPos->getYCoordinate();

    if (abs($distanceX) <= 1 && abs($distanceY) <= 1) {
        return;
    }

    $moveOnce = $distanceX === 0 || $distanceY === 0;

    if ($distanceX === 2) {
        $tailPos->moveRight();
    }
    if ($distanceX === -2) {
        $tailPos->moveLeft();
    }
    if ($distanceY === 2) {
        $tailPos->moveUp();
    }
    if ($distanceY === -2) {
        $tailPos->moveDown();
    }

    if ($moveOnce) {
        return;
    }

    if ($distanceX === 1) {
        $tailPos->moveRight();
    }
    if ($distanceX === -1) {
        $tailPos->moveLeft();
    }
    if ($distanceY === 1) {
        $tailPos->moveUp();
    }
    if ($distanceY === -1) {
        $tailPos->moveDown();
    }
}

class Position
{
    public function __construct(private int $xPos = 0, private int $yPos = 0)
    {
    }

    public function moveUp() : void
    {
        $this->yPos++;
    }

    public function moveDown() : void
    {
        $this->yPos--;
    }

    public function moveLeft() : void
    {
        $this->xPos--;
    }

    public function moveRight() : void
    {
        $this->xPos++;
    }

    public function coords() : string
    {
        return $this->xPos . ':' . $this->yPos;
    }

    public function getXCoordinate() : int
    {
        return $this->xPos;
    }

    public function getYCoordinate() : int
    {
        return $this->yPos;
    }
}