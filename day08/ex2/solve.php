<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$trees = array_map(fn ($treeRow) => array_map(intval(...), str_split($treeRow)), $input);

$visibleTrees = [];

$forestWidth = count($trees[0]);
$forestLength = count($trees);

for ($yPos = 0; $yPos < $forestLength; $yPos++) {
    $visibleHeight = -1;
    for ($xPos = 0; $xPos < $forestWidth; $xPos++) {
        $treeHeight = $trees[$yPos][$xPos];
        if ($treeHeight > $visibleHeight) {
            $visibleTrees[$xPos . '_' . $yPos] = $treeHeight;
            $visibleHeight = $treeHeight;
        }

        if ($visibleHeight === 9) {
            continue 2;
        }
    }
}

for ($xPos = 0; $xPos < $forestWidth; $xPos++) {
    $visibleHeight = -1;
    for ($yPos = 0; $yPos < $forestLength; $yPos++) {
        $treeHeight = $trees[$yPos][$xPos];
        if ($treeHeight > $visibleHeight) {
            $visibleTrees[$xPos . '_' . $yPos] = $treeHeight;
            $visibleHeight = $treeHeight;
        }

        if ($visibleHeight === 9) {
            continue 2;
        }
    }
}

for ($yPos = 0; $yPos < $forestLength; $yPos++) {
    $visibleHeight = -1;
    for ($xPos = $forestWidth-1; $xPos >= 0; $xPos--) {
        $treeHeight = $trees[$yPos][$xPos];
        if ($treeHeight > $visibleHeight) {
            $visibleTrees[$xPos . '_' . $yPos] = $treeHeight;
            $visibleHeight = $treeHeight;
        }

        if ($visibleHeight === 9) {
            continue 2;
        }
    }
}

for ($xPos = 0; $xPos < $forestWidth; $xPos++) {
    $visibleHeight = -1;
    for ($yPos = $forestLength-1; $yPos >= 0; $yPos--) {
        $treeHeight = $trees[$yPos][$xPos];
        if ($treeHeight > $visibleHeight) {
            $visibleTrees[$xPos . '_' . $yPos] = $treeHeight;
            $visibleHeight = $treeHeight;
        }

        if ($visibleHeight === 9) {
            continue 2;
        }
    }
}

foreach ($visibleTrees as $position => $height) {
    [$xPos, $yPos] = explode('_', $position);
    $visibleTrees[$position] = calculateScenicScore((int) $xPos, (int) $yPos, $height, $trees);
}

echo max($visibleTrees);

function calculateScenicScore(int $xPosTree, int $yPosTree, int $height, array &$trees) : int
{
    static $forestWidth = null;
    static $forestLength = null;

    if ($forestWidth === null) {
        $forestWidth = count($trees[0]);
        $forestLength = count($trees);
    }
    
    $score = 1;

    $topMultiplier = 0;
    for ($yPos = $yPosTree-1; $yPos >= 0; $yPos--) {
        $topMultiplier++;
        $treeHeight = $trees[$yPos][$xPosTree];

        if ($treeHeight >= $height) {
            break;
        }
    }

    $bottomMultiplier = 0;
    for ($yPos = $yPosTree+1; $yPos < $forestLength; $yPos++) {
        $bottomMultiplier++;
        $treeHeight = $trees[$yPos][$xPosTree];

        if ($treeHeight >= $height) {
            break;
        }
    }

    $leftMultiplier = 0;
    for ($xPos = $xPosTree-1; $xPos >= 0; $xPos--) {
        $leftMultiplier++;
        $treeHeight = $trees[$yPosTree][$xPos];

        if ($treeHeight >= $height) {
            break;
        }
    }

    $rightMultiplier = 0;
    for ($xPos = $xPosTree+1; $xPos < $forestWidth; $xPos++) {
        $rightMultiplier++;
        $treeHeight = $trees[$yPosTree][$xPos];

        if ($treeHeight >= $height) {
            break;
        }
    }

    return $score * $topMultiplier * $bottomMultiplier * $leftMultiplier * $rightMultiplier;
}