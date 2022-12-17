<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$nodes = [];
$pathes = [];
$nodesOfInterest = [];
foreach ($input as $line) {
    preg_match('/Valve (?<valve>[A-Z]{2}) has flow rate=(?<flow>\d+); tunnels? leads? to valves? (?<dests>([A-Z]{2}(, )?)+)/', $line, $matches);
    ['valve' => $name, 'flow' => $flow, 'dests' => $neighbours] = $matches;
    $nodes[$name] = new Node($name, (int) $flow);
    $pathes[$name] = explode(', ', $neighbours);
    if ($nodes[$name]->flowRate > 0) {
        $nodesOfInterest[$name] = $nodes[$name]->flowRate;
    }
}

uasort($nodesOfInterest, function (int $left, int $right) {
    return $right <=> $left;
});

$startNode = $nodes['AA'];

foreach ($pathes as $name => $neighbours) {
    foreach ($neighbours as $neighbour) {
        $nodes[$name]->addNeighbour($nodes[$neighbour]);
    }
}

$pathes = [];
foreach ($nodes as $node) {
    $pathes[$node->name] = findShortesPathes($node);
}

function findShortesPathes(Node $startNode) : array
{
    $step = 0;
    $nextIteration = [$startNode];
    $currentIteration = [];
    $visitedNodes = [$startNode->name => 0];

    while (!empty($nextIteration)) {
        $step++;
        $currentIteration = $nextIteration;
        $nextIteration = [];

        foreach ($currentIteration as $node) {
            foreach ($node->getNeighbours() as $neighbour) {
                if (array_key_exists($neighbour->name, $visitedNodes)) {
                    continue;
                }

                $visitedNodes[$neighbour->name] = $step;
                $nextIteration[] = $neighbour;
            }
        }
    }

    unset($visitedNodes[$startNode->name]);

    return $visitedNodes;
}

TravelState::$shortestPathes = $pathes;
TravelState::$allNodes = $nodes;

$startState = new TravelState();
$startState->openFlowNodes = $nodesOfInterest;
$startState->currentNode = $startNode;

$bestState = travel($startState);

echo $bestState->absoluteFlow;

function travel(TravelState $state) : TravelState
{
    if ($state->remainingMinutes === 0) {
        return $state;
    }
    
    if (array_key_exists($state->currentNode->name, $state->openFlowNodes)) {
        $state->tick();
        $state->currentFlowRate += $state->currentNode->flowRate;
        unset($state->openFlowNodes[$state->currentNode->name]);

        return travel($state);
    }

    if (empty($state->openFlowNodes)) {
        while ($state->remainingMinutes > 0) {
            $state->tick();
        }

        return $state;
    }

    $nextStates = [];
    foreach ($state->openFlowNodes as $nodeName => $nodeRate) {
        $nextState = clone $state;
        for ($step = 0; $step < $state::$shortestPathes[$state->currentNode->name][$nodeName]; $step++) {
            if ($nextState->remainingMinutes === 0) {
                break;
            }
            $nextState->tick();
        }
        $nextState->currentNode = $state::$allNodes[$nodeName];
        $nextStates[] = travel($nextState);
    }

    $maxFlow = 0;
    $bestState = null;
    foreach ($nextStates as $state) {
        if ($state->absoluteFlow > $maxFlow) {
            $maxFlow = $state->absoluteFlow;
            $bestState = $state;
        }
    }

    return $bestState;
}

class Node
{
    private array $neighbours = [];
    public function __construct(public readonly string $name, public readonly int $flowRate)
    {
        
    }

    public function addNeighbour(Node $node) : void
    {
        $this->neighbours[] = $node;
    }

    public function getNeighbours() : array
    {
        return $this->neighbours;
    }
}

class TravelState
{
    public int $currentFlowRate = 0;
    public array $openFlowNodes = [];
    public int $absoluteFlow = 0;
    public int $remainingMinutes = 30;
    public Node $currentNode;
    public array $ticks = [];
    public int $minute = 1;

    public static array $shortestPathes = [];
    public static array $allNodes = [];

    public function tick() : void
    {
        $this->absoluteFlow += $this->currentFlowRate;
        $this->ticks[$this->minute] = [
            'node' => $this->currentNode->name,
            'flowRate' => $this->currentFlowRate,
            'absFlow' => $this->absoluteFlow,
            'remaining' => $this->remainingMinutes,
        ];
        $this->remainingMinutes--;
        $this->minute++;
    }
}