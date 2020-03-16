<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Simulator;
use App\SimulatorLoop;

class SimulatorController extends Controller
{
    // 多行輸入
    public function paths(Request $request)
    {
        // 原始指令多行字串
        $lines = $request->lines ?? '';

        $simulator = new Simulator($lines);

        // 存在循環指令
        if ($simulator->loop) {
            $simulator = new SimulatorLoop($lines);
        }

        return json_encode($simulator);
    }

    public function index()
    {
        $data = [];

        return view('simulator.index', $data);
    }

    public function v2()
    {
        return view('simulator.v2');
    }

    public function apiAnalytics(Request $request)
    {
        $raw = $request->raw ?? '';
        $gcode = new Gcode($raw);

        return json_encode($gcode->parameters);
    }

    // 多行輸入
    public function apiPoints(Request $request)
    {
        $multirow = $request->multirow ?? '';
        $simulator = new Simulator($multirow);

        $points = [];
        $i = 0;

        $points['loop'] = $simulator->loop;
        $points['loopu'] = $simulator->loopu;
        $points['loopr'] = $simulator->loopr;

        // 指令行數
        foreach ($simulator->blocks as $key => $block) {
            $x1 = $points['points'][$i - 1]['x2'] ?? 0;
            $y1 = $points['points'][$i - 1]['y2'] ?? 0;
            $points['points'][$i] = $simulator->run($key, $x1, $y1);
            $i++;
        }

        return json_encode($points);
    }
}
