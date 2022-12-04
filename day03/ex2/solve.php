<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_IGNORE_NEW_LINES);
$lineCount = count($input);

$priorityList = generatePriorityList();
$prioritySum = 0;
for ($line = 0; $line < $lineCount; $line+=3) {
    $firstLine = $input[$line];
    $secondLine = $input[$line + 1];
    $thirdLine = $input[$line + 2];

    $commonItem = findCommonItem($firstLine, $secondLine, $thirdLine);
    $prioritySum += $priorityList[$commonItem];
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

function findCommonItem(string $firstSack, string $secondSack, string $thirdSack) : string
{
    $firstSack = str_split($firstSack);
    $secondSack = str_split($secondSack);
    $thirdSack = str_split($thirdSack);

    $commonItems = array_values(array_intersect($firstSack, $secondSack, $thirdSack));

    return $commonItems[0];
}