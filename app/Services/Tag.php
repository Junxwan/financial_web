<?php

namespace App\Services;

use App\Repositories\TagRepository;

class Tag
{
    /**
     * @var TagRepository
     */
    private TagRepository $repo;

    /**
     * Tag constructor.
     *
     * @param TagRepository $repo
     */
    public function __construct(TagRepository $repo)
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
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->repo->all();
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data)
    {
        return $this->repo->insert($data);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
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
}
