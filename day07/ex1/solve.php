<?php
namespace AdventOfCode;

$input = file(__DIR__ . '/input.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

$root = new Directory('/', null);
$currentDirectory = $root;

foreach ($input as $line) {
    $paramters = explode(' ', $line);
    switch ($paramters[0]) {
        case '$':
            switch ($paramters[1]) {
                case 'ls':
                    break;
                case 'cd':
                    if ($paramters[2] === '/') {
                        $currentDirectory = $root;
                        break;
                    }
                    if ($paramters[2] === '..') {
                        $currentDirectory = $currentDirectory->getParent();
                        break;
                    }
                    $currentDirectory = $currentDirectory->getChild($paramters[2]);
                    break;
            }
            break;
        case 'dir':
            $currentDirectory->addChild(new Directory($paramters[1], $currentDirectory));
            break;
        default:
            $currentDirectory->addChild(new File($paramters[1], (int) $paramters[0]));
    }
}

$root->getSize();

$smallDirectories = [];
findSmallDirectories($root, $smallDirectories);
echo array_reduce($smallDirectories, function (int $carry, Directory $directory) : int {
    return $carry + $directory->getSize();
}, 0);

function findBigDirectories($node, &$bigDirectories, $minSize) {
    if ($node->isFile()) {
        return;
    }

    if ($node->getSize() >= $minSize) {
        $bigDirectories[] = $node;
    }

    foreach ($node->getChildren() as $childNode) {
        findBigDirectories($childNode, $bigDirectories, $minSize);
    }
}

function findSmallDirectories($node, &$smallDirectories) {
    if ($node->isFile()) {
        return;
    }

    if ($node->getSize() <= 100000) {
        $smallDirectories[] = $node;
    }

    foreach ($node->getChildren() as $childNode) {
        findSmallDirectories($childNode, $smallDirectories);
    }
}

abstract class FSObject
{
    private $name;
    protected $size = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function getSize() : int;
    abstract public function isFile() : bool;

    public function getName() : string
    {
        return $this->name;
    }
}

class File extends FSObject
{
    public function __construct(string $name, int $size)
    {
        parent::__construct($name);
        $this->size = $size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isFile(): bool
    {
        return true;
    }
}

class Directory extends FSObject
{
    private $parentNode;
    private $childNodes = [];

    public function __construct(string $name, ?Directory $parent)
    {
        parent::__construct($name);
        $this->parentNode = $parent;
    }

    public function addChild(FSObject $child) : void
    {
        $this->childNodes[$child->getName()] = $child;
    }

    public function getSize(): int
    {
        if ($this->size === null) {
            $this->calcSize();
        }

        return $this->size;
    }

    public function getParent() : ?Directory
    {
        return $this->parentNode;
    }

    public function getChild(string $name) : Directory
    {
        return $this->childNodes[$name];
    }

    public function getChildren() : array
    {
        return $this->childNodes;
    }

    private function calcSize() : void
    {
        $size = array_reduce($this->childNodes, function (int $carry, FSObject $node) {
            return $carry + $node->getSize();
        }, 0);
        $this->size = $size;
    }

    public function isFile(): bool
    {
        return false;
    }
}

