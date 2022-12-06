<?php
$file = fopen(__DIR__ . '/input.txt', 'r');
$buffer = '....';
$charsWithoutDoubling = 0;
$charPos = 0;
while (++$charPos) {
    $nextChar = fgetc($file);
    $buffer = substr($buffer, 1) . $nextChar;
    
    if ($charPos > 3 && strlen(count_chars($buffer, 3)) === 4) {
        break;
    }
}

echo $charPos;