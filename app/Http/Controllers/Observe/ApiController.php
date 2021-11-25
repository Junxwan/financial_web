<?php

namespace App\Http\Controllers\Observe;

use App\Services\Observe;
use Illuminate\Http\Request;

class ApiController
{
    /**
     * @var Observe
     */
    private Observe $observe;

    /**
     * ApiController constructor.
     *
     * @param Observe $observe
     */
    public function __construct(Observe $observe)
    {
        $this->observe = $observe;
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cbPriceVolume(string $code)
    {
        return response()->json($this->observe->cbPriceVolumes($code));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cbPriceVolumeByDate(Request $request)
    {
        return response()->json($this->observe->cbPriceVolumeByDate($request->query('date', date('Y-m-d'))));
    }
}
