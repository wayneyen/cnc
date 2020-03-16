<?php

namespace App;

class Word
{
    public $word = '';
    public $key = '';
    public $value = '';

    function __construct($word = '')
    {
        $this->word = $word;

        preg_match_all(
            '/([A-Z]+)(-?\d+.?[0-9]?+)/',
            $word,
            $detail
        );

        $this->key = $detail[1][0];
        $this->value = $detail[2][0];
    }
}
