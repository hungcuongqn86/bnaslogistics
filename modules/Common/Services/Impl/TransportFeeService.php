<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\TransportFee;
use Modules\Common\Services\Intf\ITransportFeeService;
use Illuminate\Support\Facades\DB;

class TransportFeeService extends CommonService implements ITransportFeeService
{
    protected function getDefaultModel()
    {
        return TransportFee::getTableName();
    }

    protected function getDefaultClass()
    {
        return TransportFee::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = TransportFee::where('id', '>', 0);

        $type = isset($filter['type']) ? $filter['type'] : 0;
        if ($type > 0) {
            $query->where('type', '=', $type);
        }

        $warehouse_id = isset($filter['warehouse_id']) ? $filter['warehouse_id'] : 0;
        if ($warehouse_id > 0) {
            $query->where('warehouse_id', '=', $warehouse_id);
        }

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'min_r';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'asc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function getByType($type)
    {
        return TransportFee::where('type', '=', $type)->orderBy('min_r', 'DESC')->get();
    }

    public function findById($id)
    {
        $rResult = TransportFee::where('id', '=', $id)->first();
        return $rResult;
    }

    public function create($arrInput)
    {
        $owner = new TransportFee($arrInput);
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
            $version = TransportFee::find($id);
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
            TransportFee::where('id', '=', $id)->delete();
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
