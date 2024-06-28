<?php

if (!function_exists('generateRandomString')) {
  function generateRandomString($length) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
}

if (!function_exists('generateFormattedString')) {
  function generateFormattedString() {
    $part1 = generateRandomString(5);
    $part2 = generateRandomString(3);
    $part3 = generateRandomString(5);
    return $part1 . '-' . $part2 . '-' . $part3;
  }
}