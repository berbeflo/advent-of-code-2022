<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_IGNORE_NEW_LINES);

$overlappingRealms = 0;
foreach ($input as $line) {
    $boundaries = getPairBoundaries($line);
    if (realmsOverlap($boundaries)) {
        $overlappingRealms++;
    }
}

echo $overlappingRealms;

function getPairBoundaries(string $line) : array
{
    [$firstElve, $secondElve] = explode(',', $line);
    [$minFirstElve, $maxFirstElve] = explode('-', $firstElve);
    [$minSecondElve, $maxSecondElve] = explode('-', $secondElve);

    return array_map('intval', [$minFirstElve, $maxFirstElve, $minSecondElve, $maxSecondElve]);
}

function realmsOverlap(array $boundaries) : bool
{
    [$minFirstElve, $maxFirstElve, $minSecondElve, $maxSecondElve] = $boundaries;

    return ($minFirstElve >= $minSecondElve && $minFirstElve <= $maxSecondElve)
        || ($minSecondElve >= $minFirstElve && $minSecondElve <= $maxFirstElve)
        || ($maxFirstElve >= $minSecondElve && $maxFirstElve <= $maxSecondElve)
        || ($maxSecondElve >= $minFirstElve && $maxSecondElve <= $maxFirstElve);
}