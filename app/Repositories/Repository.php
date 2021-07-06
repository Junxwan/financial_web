<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class Repository
{
    /**
     * @param \Closure $callback
     *
     * @return bool
     */
    protected function transaction(\Closure $callback)
    {
        try {
            DB::beginTransaction();
            if ($result = $callback()) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }
}
