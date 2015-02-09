<?php
/**
 * User: Menesty
 * Date: 1/30/15
 * Time: 09:31
 */

function getPreparedArtNumber($artNumber)
{
    $length = strlen($artNumber);
    $index = 8;
    $part = 3;

    if ($length > 8) {
        $index = $length;
        $part = 4;
    }
    return substr($artNumber, $length - $index, $part) . "." . substr($artNumber, $length - $index - 3, 3) .
    "." . substr($artNumber, $length - $index - 6, 2);
}