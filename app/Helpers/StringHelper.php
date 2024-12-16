<?php

if (!function_exists('convertToNonUnicode')) {
    function convertToNonUnicode($str)
    {
        $trans = transliterator_create('Any-Latin; Latin-ASCII; Lower');
        return transliterator_transliterate($trans, $str);
    }
}
