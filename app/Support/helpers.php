<?php

if (! function_exists('rupiahIdr')) {
    function rupiahIdr($number): string
    {
        return 'IDR ' . number_format((float) $number, 0, ',', '.');
    }
}

if (! function_exists('rupiahRp')) {
    function rupiahRp($number): string
    {
        return 'Rp ' . number_format((float) $number, 0, ',', '.');
    }
}

if (! function_exists('rupiah')) {
    function rupiah($number): string
    {
        return rupiahIdr($number);
    }
}
