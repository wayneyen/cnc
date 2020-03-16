<?php

namespace App;

class Block
{
    public $block = '';
    public $words = [];
    public $serial = null;

    function __construct($block = '')
    {
        $this->block = $block;

        // 正規切割指令
        $block = strtoupper(str_replace(' ', '', $block));
        preg_match_all(
            '/[A-Z]+-?[0-9]+\.?[0-9]?+/',
            $block,
            $words
        );

        foreach ($words[0] as $key => $word) {
            $this->words[] = new Word($word);
        }
    }
}
