<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_IGNORE_NEW_LINES);

$priorityList = generatePriorityList();
$prioritySum = 0;
foreach ($input as $line) {
    $wrongItem = findWrongItem($line);
    $prioritySum += $priorityList[$wrongItem];
}

echo $prioritySum;


function generatePriorityList() : array
{
    $priorityList = [];
    for ($lowerCase = 'a', $upperCase = 'A', $priority = 1; $priority < 27; $priority++, $lowerCase++, $upperCase++) {
        $priorityList[$lowerCase] = $priority;
        $priorityList[$upperCase] = $priority + 26;
    }

    return $priorityList;
}

function findWrongItem(string $items) : string
{
    $middlePos = strlen($items) / 2;
    $compartment1 = str_split(substr($items, 0, $middlePos));
    $compartment2 = str_split(substr($items, $middlePos));

    $intersection = array_values(array_intersect($compartment1, $compartment2));
    
    return $intersection[0];
}