<?php

namespace App\Services;

use App\Models\Stock;
use App\Repositories\ReportRepository;
use Illuminate\Http\Request;

class Report
{
    /**
     * @var ReportRepository
     */
    private ReportRepository $repo;

    /**
     * Report constructor.
     *
     * @param ReportRepository $repo
     */
    public function __construct(ReportRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(int $id)
    {
        return $this->repo->get($id);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        return $this->repo->list($data);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function insert(Request $request)
    {
        return $this->repo->insert($this->getData($request));
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return bool
     */
    public function update(int $id, Request $request)
    {
        return $this->repo->update($id, $this->getData($request));
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    private function getData(Request $request)
    {
        $data = $request->all();
        return array_merge([
            'stock_id' => Stock::query()->where('code', $data['code'])->first()->id,
            'title' => $data['title'],
            'date' => $data['date'],
            'price_f' => $data['price_f'],
            'quarterly' => $data['quarterly'],
            'month' => $data['month'],
            'action' => $data['action'],
            'value' => $data['value'],
            'market_eps_f' => $data['market_eps_f'],
            'pe' => $data['pe'],
            'evaluate' => $data['evaluate'],
            'desc' => $data['desc'],
            'desc_total' => $data['desc_total'],
            'desc_revenue' => $data['desc_revenue'],
            'desc_gross' => $data['desc_gross'],
            'desc_fee' => $data['desc_fee'],
            'desc_outside' => $data['desc_outside'],
            'desc_other' => $data['desc_other'],
            'desc_tax' => $data['desc_tax'],
            'desc_non' => $data['desc_non'],
        ],
            $data['revenue'],
            $data['revenue_month'],
            $data['eps'],
            $data['gross'],
            $data['fee'],
            $data['outside'],
            $data['other'],
            $data['tax'],
            $data['profit'],
            $data['profit_pre'],
            $data['profit_after'],
            $data['profit_non'],
            $data['profit_main'],
        );
    }
}
