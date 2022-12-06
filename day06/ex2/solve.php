<?php
$file = fopen(__DIR__ . '/input.txt', 'r');
$buffer = str_repeat('.', 14);
$charsWithoutDoubling = 0;
$charPos = 0;
while (++$charPos) {
    $nextChar = fgetc($file);
    $buffer = substr($buffer, 1) . $nextChar;
    
    if ($charPos > 13 && strlen(count_chars($buffer, 3)) === 14) {
        break;
    }
}

echo $charPos;