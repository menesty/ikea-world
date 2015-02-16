<?php
/**
 * User: Menesty
 * Date: 1/30/15
 * Time: 09:31
 */

function getPreparedArtNumber($artNumber)
{
    $length = strlen($artNumber);
   return substr($artNumber, 0, $length - 5).".".substr($artNumber, $length - 5, 3).".".substr($artNumber, $length - 2);
}