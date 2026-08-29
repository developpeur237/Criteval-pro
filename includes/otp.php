<?php
declare(strict_types=1);

function generate_otp(int $length = 6): string
{
    $min = 10 ** ($length - 1);
    $max = (10 ** $length) - 1;
    return (string) random_int($min, $max);
}
