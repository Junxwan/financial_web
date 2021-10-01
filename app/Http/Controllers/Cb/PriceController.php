<?php

namespace App\Http\Controllers\Cb;

use App\Models\Cb\Cb;
use App\Models\Cb\Price;
use App\Repositories\Cb\PriceRepository;

class PriceController
{
    /**
     * @var string
     */
    protected $view = 'page.cb.price';

    /**
     * @var PriceRepository
     */
    private PriceRepository $repo;

    /**
     * IndexController constructor.
     *
     * @param PriceRepository $repo
     */
    public function __construct(PriceRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view($this->view);
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(string $code)
    {
        $price = $this->repo->get($code);
        $data = [
            'price' => [],
            'volume' => [],
            'name' => Cb::query()->select('name')->where('code', $code)->first()->name,
        ];

        foreach ($price as $value) {
            $p = $value->only(['open', 'close', 'increase', 'high', 'low']);
            $p['x'] = strtotime($value->date) * 1000;

            $data['price'][] = $p;
            $data['volume'][] = [
                'x' => $p['x'],
                'y' => $value->volume,
            ];
        }

        return response()->json($data);
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function month(string $code)
    {
        return response()->json($this->repo->month($code));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function date()
    {
        return response()->json([
            'date' => Price::query()
                ->select('date')
                ->orderByDesc('date')
                ->limit(1)
                ->first()->date,
        ]);
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function premium(string $code)
    {
        return response()->json($this->repo->premium($code));
    }

    /**
     * @param string $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function conversion(string $code)
    {
        return response()->json($this->repo->conversion($code));
    }
}
