<?php

namespace App\Repositories;

use App\Models\News;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class NewsRepository extends Repository
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function list(array $data)
    {
        $query = news::query()->select('id', 'title', 'publish_time', 'url', 'remark');
        $queryTotal = DB::table('news');

        if (isset($data['search'])) {
            $search = $data['search'];
            if (isset($search['value']) && ! empty($search['value'])) {
                $query = $this->whereLike($query->getQuery(), $search);
                $queryTotal = $this->whereLike($queryTotal, $search);
            }

            $query = $this->whereDate($query->getQuery(), $search);
            $queryTotal = $this->whereDate($queryTotal, $search);
        }

        $query->offset($data['start'])
            ->limit($data['limit'])
            ->orderByDesc('publish_time')
            ->get();

        return [
            'data' => $query->offset($data['start'])
                ->limit($data['limit'])
                ->orderByDesc('publish_time')
                ->get(),
            'total' => $queryTotal->count(),
        ];
    }

    /**
     * @param int $id
     * @param string $remark
     *
     * @return bool
     */
    public function update(int $id, string $remark)
    {
        return (bool)News::query()->where('id', $id)->update([
            'remark' => $remark,
        ]);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return (bool)News::query()->where('id', $id)->delete();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function clear()
    {
        $f = function ($name) {
            return $this->transaction(function () use ($name) {
                DB::statement("SET SQL_SAFE_UPDATES = 0;");
                return news::query()->whereIn('id', function (Builder $query) use ($name) {
                    $query->fromSub(function (Builder $query2) use ($name) {
                        $query2->select('id')
                            ->from('news')
                            ->groupBy([$name])
                            ->having(DB::raw('count(*)'), '>', 1);
                    }, 'p');
                })->delete();
            });
        };

        return $f('url') + $f('title');
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereLike(Builder $query, array $data)
    {
        return $query->where('context', 'like', "%{$data['value']}%")
            ->where('title', 'like', "%{$data['value']}%");
    }

    /**
     * @param Builder $query
     * @param array $data
     *
     * @return Builder
     */
    private function whereDate(Builder $query, array $data)
    {
        if (! is_null($data['start_date']) && ! is_null($data['end_date'])) {
            return $query->whereBetween('publish_time', [$data['start_date'], $data['end_date']]);
        } elseif (! is_null($data['start_date'])) {
            return $query->where('publish_time', '<=', "{$data['start_date']} 23:59:59");
        } elseif (! is_null($data['end_date'])) {
            return $query->where('publish_time', '>=', "{$data['end_date']} 00:00:00");
        }

        return $query;
    }
}
