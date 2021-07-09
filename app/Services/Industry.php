<?php

namespace App\Services;

use App\Repositories\IndustryRepository;

class Industry
{
    /**
     * @var IndustryRepository
     */
    private IndustryRepository $repo;

    /**
     * Industry constructor.
     *
     * @param IndustryRepository $repo
     */
    public function __construct(IndustryRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        if (isset($data['search']) && isset($data['search']['name'])) {
            $code = $data['search']['name'];
        } else {
            $code = 'TSE';
        }

        return [
            'data' => $data = $this->repo->list($code, $data),
            'total' => $data->count(),
        ];
    }
}
