<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\Vip;
use Modules\Common\Services\Intf\IVipService;
use Illuminate\Support\Facades\DB;

class VipService extends CommonService implements IVipService
{
    protected function getDefaultModel()
    {
        return Vip::getTableName();
    }

    protected function getDefaultClass()
    {
        return Vip::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = Vip::where('id', '>', 0);

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'min_tot_tran';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'asc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Vip::where('id', '=', $id)->first();
        return $rResult;
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $version = Vip::find($id);
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
