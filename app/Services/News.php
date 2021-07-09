<?php

namespace App\Services;

use App\Repositories\NewsRepository;

class News
{
    /**
     * @var NewsRepository
     */
    private NewsRepository $repo;

    /**
     * News constructor.
     *
     * @param NewsRepository $repo
     */
    public function __construct(NewsRepository $repo)
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
        return $this->repo->list($data);
    }

    /**
     * @param int $id
     * @param string $remark
     *
     * @return bool
     */
    public function update(int $id, string $remark)
    {
        return $this->repo->update($id, $remark);
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
     * @return bool
     * @throws \Exception
     */
    public function clear()
    {
        return $this->repo->clear();
    }
}
