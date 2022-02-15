<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\ChinaWarehouse;
use Modules\Common\Services\Intf\IChinaWarehouseService;
use Illuminate\Support\Facades\DB;

class ChinaWarehouseService extends CommonService implements IChinaWarehouseService
{
    protected function getDefaultModel()
    {
        return ChinaWarehouse::getTableName();
    }

    protected function getDefaultClass()
    {
        return ChinaWarehouse::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = ChinaWarehouse::where('id', '>', 0);

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
        $rResult = ChinaWarehouse::where('id', '=', $id)->first();
        return $rResult;
    }

    public function create($arrInput)
    {
        $owner = new ChinaWarehouse($arrInput);
        DB::beginTransaction();
        try {
            $owner->save();
            DB::commit();
            return $owner;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $version = ChinaWarehouse::find($id);
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

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            ChinaWarehouse::where('id', '=', $id)->delete();
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
