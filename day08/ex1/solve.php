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

echo count($visibleTrees);