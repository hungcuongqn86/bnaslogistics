<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Warehouse;
use Modules\Common\Services\Intf\IWarehouseService;
use Illuminate\Support\Facades\DB;

class WarehouseService extends CommonService implements IWarehouseService
{
    protected function getDefaultModel()
    {
        return Warehouse::getTableName();
    }

    protected function getDefaultClass()
    {
        return Warehouse::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = Warehouse::where('id', '>', 0);

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'id';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'asc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Warehouse::where('id', '=', $id)->first();
        return $rResult;
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $version = Warehouse::find($id);
            $version->update($arrInput);
            DB::commit();
            return $version;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
