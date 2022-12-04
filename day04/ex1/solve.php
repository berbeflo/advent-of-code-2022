<?php
$input = file(__DIR__ . '/input.txt', FILE_IGNORE_NEW_LINES | FILE_IGNORE_NEW_LINES);

$overlappingRealms = 0;
foreach ($input as $line) {
    $boundaries = getPairBoundaries($line);
    if (realmsFullyOverlap($boundaries)) {
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

function realmsFullyOverlap(array $boundaries) : bool
{
    [$minFirstElve, $maxFirstElve, $minSecondElve, $maxSecondElve] = $boundaries;

    return ($minFirstElve >= $minSecondElve && $maxFirstElve <= $maxSecondElve)
        || ($minSecondElve >= $minFirstElve && $maxSecondElve <= $maxFirstElve);
}