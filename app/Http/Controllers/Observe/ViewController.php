<?php

namespace App\Http\Controllers\Observe;

class ViewController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.observe.index');
    }
}
