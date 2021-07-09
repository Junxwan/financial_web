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
     * @param string $name
     *
     * @return bool
     */
    public function insert(string $name)
    {
        return $this->repo->insert($name);
    }

    /**
     * @param int $id
     * @param string $name
     *
     * @return bool
     */
    public function update(int $id, string $name)
    {
        return $this->repo->update($id, $name);
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
