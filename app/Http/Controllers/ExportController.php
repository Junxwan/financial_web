<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExportController extends Controller
{
    /**
     * @var string
     */
    protected $view = 'page.export';

    /**
     * @param Request $request
     *
     * @return array[]
     */
    protected function initView(Request $request)
    {
        $now = Carbon::now();
        $years = [];

        for ($i = 0; $i < ($now->year - 2015); $i++) {
            $years[] = $now->year - $i;
        }

        $quarterly = 4;
        if ($now->month >= 11) {
            $quarterly = 3;
        } elseif ($now->month >= 8) {
            $quarterly = 2;
        } elseif ($now->month >= 5) {
            $quarterly = 1;
        }

        return [
            'years' => $years,
            'month' => $now->month - 1,
            'quarterly' => $quarterly,
        ];
    }
}
