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
        return [
            'years' => years(),
            'month' => Carbon::now()->month - 1,
            'quarterly' => currentQuarterly(),
        ];
    }
}
