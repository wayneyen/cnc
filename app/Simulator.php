<?php

namespace App;

class Simulator
{
    public $simulator = ''; // 原始指令
    public $blocks = []; // 單節
    public $loop = null; // 循環指令

    // 預設最標設置於機械原點
    public $x = 150;
    public $z = 50;

    public $strokeDasharray = 0; // 移動模式 G00:5:快速模式 G01:0:線性模式
    public $type = 'line'; // line：直線 arc：圓弧
    public $direct = 0; // 弧線方向

    public $zoom = 4;

    function __construct($simulator = '')
    {
        $this->simulator = $simulator;

        $blocks = explode("\n", $simulator);

        foreach ($blocks as $key => $block) {
            $n = str_pad($key, 4, "0", STR_PAD_LEFT);
            $this->blocks['N' . $n] = new Block($block);
            $this->blocks['N' . $n]->serial = $key;
        }

        // 檢查 G71 在第幾行
        foreach ($this->blocks as $key => $block) {
            foreach ($block->words as $word) {
                if (!$this->loop && $word->word == 'G71') {
                    $this->loop = new Loop;
                    $this->loop->key = $key;
                    $this->loop->prevKey = 'N' . str_pad($block->serial - 1, 4, "0", STR_PAD_LEFT);;
                }
            }
        }

        // 發現 G71 指令
        if ($this->loop) {
            // 取出 U R
            foreach ($this->blocks[$this->loop->key]->words as $word) {
                if ($word->key == 'U') {
                    $this->loop->u = (float) $word->value;
                } else if ($word->key == 'R') {
                    $this->loop->r = (float) $word->value;
                }
            }
        }

        // 產生基本路徑
        $this->paths = $this->generatePaths();
    }

    // 產生基本路徑
    private function generatePaths()
    {
        $paths = [];

        foreach ($this->blocks as $key => $block) {
            $paths[$key] = $this->_run($key);
        }

        return $paths;
    }

    // 返回一個點
    private function _run($key)
    {
        $path = [];
        $x2 = $this->x;
        $z2 = $this->z;

        $i = 0;  // 弧線圓心
        $k = 0;  // 弧線圓心
        $r = 0;  // 弧線半徑

        foreach ($this->blocks[$key]->words as $word) {
            // 先不處裡循環指令，要先渲染出基本路徑
            if ($word->word == 'G71') {
                return null;
            }

            switch ($word->key) {
            case 'G': // 處理模式碼，不回傳
                $this->_gProcess($word->value);
                break;

            case 'M': // 輔助模式碼
                $this->_mProcess($word->value);
                break;

            case 'N': // 順序碼
                $this->_nProcess($word->value, $key);
                break;

            case 'S': // 速度碼
                $this->speed = $this->_sProcess($word->value);
                break;

            case 'T': // 刀具碼
                $this->knift = $this->_tProcess($word->value);
                break;

            case 'X': // 處理 X 座標
                $x2 = $this->_xProcess($word->value);
                break;

            case 'U': // 處理 X 座標 (增量)
                $x2 += $this->_xProcess($word->value);
                break;

            case 'Z': // 處理 Z 座標
                $z2 = $this->_zProcess($word->value);
                break;

            case 'W': // 處理 Z 座標 (增量)
                $z2 += $this->_zProcess($word->value);
                break;

            case 'I': // 圓心 x
                $i = $this->_defaultProcess($word->value);
                break;

            case 'K': // 圓心 z
                $k = $this->_defaultProcess($word->value);
                break;

            case 'R': // 圓心 z
                $r = $this->_defaultProcess($word->value);
                break;
            }

            // 最後的機械原點指令
            if ($word->word == 'G28' && $key != 'N0000') {
                $x2 = 150;
                $z2 = 50;
                $this->strokeDasharray = 5;
            }
        }

        $x1 = $this->x;
        $z1 = $this->z;

        if ($this->type == 'line') {
            // 畫直線
            $path['d'] = "M{$this->_z2x($z1)} {$this->_x2y($x1)}";
            $path['d'] .= " L{$this->_z2x($z2)} {$this->_x2y($x2)}";
        } elseif ($this->type == 'arc') {
            // 畫圓弧
            $path['d'] = "M{$this->_z2x($z1)} {$this->_x2y($x1)}";

            if ($r > 0) {
                $r = abs($r) * 2 * $this->zoom;
            } else {
                $i = $z1 - $i;
                $k = $x1 + $k;
                $border1 = pow(abs($i - $z1), 2);
                $border2 = pow(abs($k - $x1), 2);
                $r = sqrt($border1 + $border2) * $this->zoom;
            }
            $path['d'] .= " A$r $r 0 0 {$this->direct} {$this->_z2x($z2)} {$this->_x2y($x2)}";
        }

        $path['strokeDasharray'] = $this->strokeDasharray;
        $path['block'] = $this->blocks[$key]->block;

        // 移動之後重新定位目前座標
        $this->x = $x2;
        $this->z = $z2;

        return $path;
    }

    private function _z2x($z)
    {
        $x = $z * $this->zoom + 500;
        return $x;
    }

    private function _x2y($x)
    {
        $y = -($x * $this->zoom) + 500;
        return $y;
    }

    // G 碼規則
    private function _gProcess($value)
    {
        switch ($value) {
        // 移動模式：直線
        case '00': // 快速移動：虛線
            $this->strokeDasharray = 5;
            $this->type = 'line';
            break;

        case '01': // 線性移動：實線
            $this->strokeDasharray = 0;
            $this->type = 'line';
            break;

        // 移動模式：圓弧移動：實線
        case '02': // 圓弧移動：實線
            $this->strokeDasharray = 0;
            $this->type = 'arc';
            $this->direct = 1;
            break;

        case '03': // 圓弧移動：實線
            $this->strokeDasharray = 0;
            $this->type = 'arc';
            $this->direct = 0;
            break;

        // 座標模式
        case '90': // 座標：絕對
            $this->positionmode = 'G90';
            return 'G90';
            break;

        case '91': // 座標：相對
            $this->positionmode = 'G91';
            return 'G91';
            break;

        // 速度模式
        case '96': // 速度：周數固定
            $this->speedmode = 'G96';
            return 'G96';
            break;

        case '97': // 速度：轉數固定
            $this->speedmode = 'G97';
            return 'G97';
            break;

        default:
            return false;
        }
    }

    // M 碼規則
    private function _mProcess($value)
    {
        // 不處理
        return true;
    }

    // N 碼規則
    private function _nProcess($value, $key)
    {
        if ($this->loop && is_null($this->loop->start)) {
            $this->loop->start = $key;
            $this->z = 0;
        } elseif ($this->loop && !is_null($this->loop->start)) {
            $this->loop->end = $key;
        }

        // 獲取循環起點
        foreach ($this->blocks[$this->loop->prevKey]->words as $word) {
            if ($word->key == 'X') {
                $this->loop->xstart = (float) $word->value;
            } else if ($word->key == 'Z') {
                $this->loop->zstart = (float) $word->value;
            }
        }

        // 獲取循環終點
        $this->loop->xend = $this->loop->xstart;
        $this->loop->zend = $this->loop->zstart;

        foreach ($this->blocks[$this->loop->start]->words as $word) {
            if ($word->key == 'X') {
                $this->loop->xend = (float) $word->value;
            } else if ($word->key == 'Z') {
                $this->loop->zend = (float) $word->value;
            }
        }

        $x1 = $this->_z2x($this->loop->zstart);
        $x2 = $this->_z2x($this->loop->zend);

        $y1 = $this->_x2y($this->loop->xstart);
        $y2 = $this->_x2y($this->loop->xend);

        $this->loop->base = "M$x1 $y1 L$x2 $y2";
    }

    // S 碼規則
    private function _sProcess($value)
    {
        return $value;
    }

    // T 碼規則
    private function _tProcess($value)
    {
        return $value;
    }

    // X 碼規則
    private function _xProcess($value)
    {
        return (float) $value;
    }

    // Y 碼規則
    private function _yProcess($value)
    {
        return (float) $value;
    }

    // Z 碼規則
    private function _zProcess($value)
    {
        return (float) $value;
    }

    private function _defaultProcess($value)
    {
        return (float) $value;
    }
}
