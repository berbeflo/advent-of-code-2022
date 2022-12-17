<?php

$directions = trim(file_get_contents(__DIR__ . '/input.txt'));
$modulo = strlen($directions);
$rockQueue = [HorizontalBlank::create(...), Cross::create(...), Corner::create(...), VerticalBlank::create(...), Square::create(...)];

$grid = new Grid();

for ($rocks = 0, $step = 0; $rocks < 2022; $rocks++) {
    $rockCreator = array_shift($rockQueue);
    array_push($rockQueue, $rockCreator);
    $rock = $rockCreator($grid);

    while (!$rock->isOnGround()) {
        $nextMovementIndex = $step++ % $modulo;
        $direction = $directions[$nextMovementIndex];
        switch ($direction) {
            case '<':
                $rock->pushLeft();
                break;
            case '>':
                $rock->pushRight();
                break;
        }
        $rock->down();
    }
}

echo $grid->getHighestYPosition() + 1;

class Grid
{
    private array $grid = [];
    private int $highestYPos = -1;

    public function add(Coordinate $coordinate) : void
    {
        $this->createRowIfNotExists($coordinate);
        $this->setCoordinate($coordinate, '@');   
    }

    private function createRowIfNotExists(Coordinate $coordinate) : void
    {
        if (array_key_exists($coordinate->yPos, $this->grid)) {
            return;
        }

        $this->grid[$coordinate->yPos] = [];
    }

    public function move(Coordinate $coordinateFrom, Coordinate $coordinateTo) : void
    {
        $this->createRowIfNotExists($coordinateTo);
        $this->unsetCoordinate($coordinateFrom);
        $this->setCoordinate($coordinateTo, '@');
    }

    public function makeSolid(Coordinate $coordinate) : void
    {
        $this->unsetCoordinate($coordinate);
        $this->setCoordinate($coordinate, '#');
        if ($coordinate->yPos > $this->highestYPos) {
            $this->highestYPos = $coordinate->yPos;
        }
    }

    public function isFree(Coordinate $coordinate) : bool
    {
        if ($coordinate->yPos < 0 || $coordinate->xPos < 0 || $coordinate->xPos > 6) {
            return false;
        }
        $notFree = isset($this->grid[$coordinate->yPos][$coordinate->xPos]);

        return !$notFree;
    }

    public function getNextEntryRow() : int
    {
        return $this->highestYPos + 4;
    }

    public function getHighestYPosition() : int
    {
        return $this->highestYPos;
    }

    private function setCoordinate(Coordinate $coordinate, ?string $char) : void
    {
        $this->grid[$coordinate->yPos][$coordinate->xPos] = $char;
    }

    private function unsetCoordinate(Coordinate $coordinate) : void
    {
        $this->setCoordinate($coordinate, null);
    }

    public function draw() : void
    {
        for ($yPos = $this->highestYPos + 4; $yPos >= 0; $yPos--) {
            for ($xPos = 0; $xPos < 7; $xPos++) {
                if (!array_key_exists($yPos, $this->grid)) {
                    echo '.';
                    continue;
                }

                if (!isset($this->grid[$yPos][$xPos])) {
                    echo '.';
                    continue;
                }

                echo $this->grid[$yPos][$xPos];
            }
            echo PHP_EOL;
        }

        echo PHP_EOL;
        echo PHP_EOL;
    }
}

class Coordinate
{
    public function __construct(public readonly int $xPos, public readonly int $yPos)
    {
        
    }
}

abstract class Rock
{
    protected array $stones;
    protected bool $grounded = false;

    public static function create(Grid $grid) : static
    {
        return new static($grid);
    }

    protected function __construct(protected readonly Grid $grid)
    {
        $this->initStones();
        $this->putStonesInGrid();
    }

    protected function putStonesInGrid() : void
    {
        foreach ($this->stones as $stone) {
            $this->grid->add($stone);
        }
    }

    abstract protected function initStones() : void;

    abstract protected function indexesToCheck(string $direction) : array;

    public function isOnGround() : bool
    {
        return $this->grounded;
    }

    public function down() : void
    {
        $indexesToCheck = $this->indexesToCheck('down');

        if (!$this->checkIndexesForModification($indexesToCheck, 0, -1)) {
            foreach ($this->stones as $stone) {
                $this->grid->makeSolid($stone);
            }
            $this->grounded = true;
            return;
        }

        $this->moveStones(0, -1);
    }

    public function pushLeft() : void
    {
        $indexesToCheck = $this->indexesToCheck('left');

        if (!$this->checkIndexesForModification($indexesToCheck, -1, 0)) {
            return;
        }

        $this->moveStones(-1, 0);
    }

    public function pushRight() : void
    {
        $indexesToCheck = $this->indexesToCheck('right');

        if (!$this->checkIndexesForModification($indexesToCheck, 1, 0)) {
            return;
        }

        $this->moveStones(1, 0);
    }

    protected function moveStones(int $xPosModifier, int $yPosModifier) : void
    {
        foreach ($this->stones as $index => $stone) {
            $newCoordinate = $this->modify($stone, $xPosModifier, $yPosModifier);
            $this->grid->move($stone, $newCoordinate);
            $this->stones[$index] = $newCoordinate;
        }
    }

    protected function checkIndexesForModification(array $indexes, int $xPosModifier, int $yPosModifier) : bool
    {
        foreach ($indexes as $index) {
            $coordinate = $this->stones[$index];
            $newCoordinate = $this->modify($coordinate, $xPosModifier, $yPosModifier);

            if (!$this->grid->isFree($newCoordinate)) {
                return false;
            }
        }

        return true;
    }

    protected function modify(Coordinate $coordinate, int $xPosModifier, int $yPosModifier) : Coordinate
    {
        return new Coordinate($coordinate->xPos + $xPosModifier, $coordinate->yPos + $yPosModifier);
    }
}

class HorizontalBlank extends Rock
{
    protected function initStones() : void
    {
        $xLeft = 2;
        $yBottom = $this->grid->getNextEntryRow();

        $this->stones = [
            new Coordinate($xLeft, $yBottom),
            new Coordinate($xLeft + 1, $yBottom),
            new Coordinate($xLeft + 2, $yBottom),
            new Coordinate($xLeft + 3, $yBottom),
        ];
    }

    protected function indexesToCheck(string $direction) : array
    {
        return match ($direction) {
            'down' => [0, 1, 2, 3],
            'left' => [0],
            'right' => [3],
        };
    }
}

class VerticalBlank extends Rock
{
    protected function initStones(): void
    {
        $xLeft = 2;
        $yBottom = $this->grid->getNextEntryRow();

        $this->stones = [
            new Coordinate($xLeft, $yBottom),
            new Coordinate($xLeft, $yBottom + 1),
            new Coordinate($xLeft, $yBottom + 2),
            new Coordinate($xLeft, $yBottom + 3),
        ];
    }

    protected function indexesToCheck(string $direction): array
    {
        return match($direction) {
            'down' => [0],
            'left' => [0, 1, 2, 3],
            'right' => [0, 1, 2, 3],
        };
    }
}

class Square extends Rock
{
    protected function initStones(): void
    {
        $xLeft = 2;
        $yBottom = $this->grid->getNextEntryRow();

        $this->stones = [
            new Coordinate($xLeft, $yBottom),
            new Coordinate($xLeft + 1, $yBottom),
            new Coordinate($xLeft, $yBottom + 1),
            new Coordinate($xLeft + 1, $yBottom + 1),
        ];
    }

    protected function indexesToCheck(string $direction): array
    {
        return match($direction) {
            'down' => [0, 1],
            'left' => [0, 2],
            'right' => [1, 3],
        };
    }
}

class Cross extends Rock
{
    protected function initStones(): void
    {
        $xLeft = 2;
        $yBottom = $this->grid->getNextEntryRow();

        $this->stones = [
            new Coordinate($xLeft, $yBottom + 1),
            new Coordinate($xLeft + 1, $yBottom + 2),
            new Coordinate($xLeft + 2, $yBottom + 1),
            new Coordinate($xLeft + 1, $yBottom),
            new Coordinate($xLeft + 1, $yBottom + 1),
        ];
    }

    protected function indexesToCheck(string $direction): array
    {
        return match($direction) {
            'down' => [0, 2, 3],
            'left' => [0, 1, 3],
            'right' => [1, 2, 3],
        };
    }
}

class Corner extends Rock
{
    protected function initStones(): void
    {
        $xLeft = 2;
        $yBottom = $this->grid->getNextEntryRow();

        $this->stones = [
            new Coordinate($xLeft, $yBottom),
            new Coordinate($xLeft + 1, $yBottom),
            new Coordinate($xLeft + 2, $yBottom),
            new Coordinate($xLeft + 2, $yBottom + 1),
            new Coordinate($xLeft + 2, $yBottom + 2),
        ];
    }

    protected function indexesToCheck(string $direction): array
    {
        return match($direction) {
            'down' => [0, 1, 2],
            'left' => [0, 3, 4],
            'right' => [2, 3, 4],
        };
    }
}