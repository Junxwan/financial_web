<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class Category
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $repo;

    /**
     * Industry constructor.
     *
     * @param CategoryRepository $repo
     */
    public function __construct(CategoryRepository $repo)
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
