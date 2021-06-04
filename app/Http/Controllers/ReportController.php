<?php

namespace App\Http\Controllers;

class ReportController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('page.report');
    }

    public function list()
    {
//        return response()->json([
//            'draw' => $request->get('draw'),
//            'recordsTotal' => $total,
//            'recordsFiltered' => $total,
//            'data' => $query->offset($request->get('start'))
//                ->limit($request->get('limit'))
//                ->orderByDesc('publish_time')
//                ->get(),
//        ]);
    }
}
