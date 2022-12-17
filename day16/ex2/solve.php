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
    $visitedNodes = [$startNode->name => []];

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
$startState->currentElephantNode = $startNode;

$bestState = travel($startState);

echo $bestState->absoluteFlow, PHP_EOL;

function travel(TravelState $state) : TravelState
{
    $state->tick();
    
    if ($state->remainingMinutes === 0) {
        return $state;
    }

    if (count($state->openFlowNodes) === 0) {
        return travel($state);
    }

    $elephantMoved = false;
    $meMoved = false;

    if ($state->elephantStepsFromDestination > 0) {
        $state->elephantStepsFromDestination--;
        $elephantMoved = true;
    }

    if ($state->stepsFromDestination > 0) {
        $state->stepsFromDestination--;
        $meMoved = true;
    }

    if (!$elephantMoved && $state->elephantStepsFromDestination === 0 && array_key_exists($state->currentElephantNode->name, $state->openFlowNodes)) {
        $state->currentFlowRate += $state->currentElephantNode->flowRate;
        unset($state->openFlowNodes[$state->currentElephantNode->name]);
        $elephantMoved = true;
    }

    if (!$meMoved && $state->stepsFromDestination === 0 && array_key_exists($state->currentNode->name, $state->openFlowNodes)) {
        $state->currentFlowRate += $state->currentNode->flowRate;
        unset($state->openFlowNodes[$state->currentNode->name]);
        $meMoved = true;
    }

    if ($meMoved && $elephantMoved) {
        return travel($state);
    }

    if (count($state->openFlowNodes) === 0) {
        return travel($state);
    }

    $nextStates = [];
    if (!$meMoved && $elephantMoved) {
        foreach ($state->openFlowNodes as $nodeName => $flow) {
            $distance = TravelState::$shortestPathes[$state->currentNode->name][$nodeName];
            $nextState = clone $state;
            $nextState->currentNode = TravelState::$allNodes[$nodeName];
            $nextState->stepsFromDestination = $distance - 1;
            $nextStates[] = travel($nextState);
        }
    }

    if ($meMoved && !$elephantMoved) {
        foreach ($state->openFlowNodes as $nodeName => $flow) {
            $distance = TravelState::$shortestPathes[$state->currentElephantNode->name][$nodeName];
            $nextState = clone $state;
            $nextState->currentElephantNode = TravelState::$allNodes[$nodeName];
            $nextState->elephantStepsFromDestination = $distance - 1;
            $nextStates[] = travel($nextState);
        }
    }

    if (!$meMoved && !$elephantMoved) {
        foreach ($state->openFlowNodes as $nodeName => $flow) {
            foreach ($state->openFlowNodes as $nodeNameElephant => $elephantFlow) {
                if (count($state->openFlowNodes) > 1 && $nodeName === $nodeNameElephant) {
                    continue;
                }
                $distance = TravelState::$shortestPathes[$state->currentNode->name][$nodeName];
                $distanceElephant = TravelState::$shortestPathes[$state->currentElephantNode->name][$nodeNameElephant];
                $nextState = clone $state;
                $nextState->currentNode = TravelState::$allNodes[$nodeName];
                $nextState->stepsFromDestination = $distance - 1;
                $nextState->currentElephantNode = TravelState::$allNodes[$nodeNameElephant];
                $nextState->elephantStepsFromDestination = $distanceElephant - 1;
                $nextStates[] = travel($nextState);
            }
            
        }
    }

    $bestState = null;
    $maxFlow = 0;

    foreach ($nextStates as $nextState) {
        if ($nextState->absoluteFlow > $maxFlow) {
            $maxFlow = $nextState->absoluteFlow;
            $bestState = $nextState;
        }
    }

    if ($bestState === null) {
        var_dump($meMoved, $elephantMoved, count($nextStates), count($state->openFlowNodes));
        exit();
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
    public int $remainingMinutes = 26;
    public Node $currentNode;
    public Node $currentElephantNode;
    public int $stepsFromDestination = 0;
    public int $elephantStepsFromDestination = 0;
    public array $ticks = [];
    public int $minute = 1;

    public static array $shortestPathes = [];
    public static array $allNodes = [];

    public function tick() : void
    {
        $this->absoluteFlow += $this->currentFlowRate;
        $this->ticks[$this->minute] = [
            'node' => $this->currentNode->name,
            'elephantNode' => $this->currentElephantNode->name,
            'distance' => $this->stepsFromDestination,
            'elephantDistance' => $this->elephantStepsFromDestination,
            'flowRate' => $this->currentFlowRate,
            'absFlow' => $this->absoluteFlow,
            'remaining' => $this->remainingMinutes,
        ];
        $this->remainingMinutes--;
        $this->minute++;
    }
}