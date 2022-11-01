<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\BankMess;
use Modules\Common\Services\Intf\IBankMessService;
use Illuminate\Support\Facades\DB;

class BankMessService extends CommonService implements IBankMessService
{
    protected function getDefaultModel()
    {
        return BankMess::getTableName();
    }

    protected function getDefaultClass()
    {
        return BankMess::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = BankMess::where('is_deleted', '=', 0);

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'created_at';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'desc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = BankMess::where('id', '=', $id)->first();
        return array('sim' => $rResult);
    }

    public function create($arrInput)
    {
        $version = new BankMess($arrInput);
        DB::beginTransaction();
        try {
            $version->save();
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

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $version = BankMess::find($id);
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
