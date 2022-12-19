<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\CratingFee;
use Modules\Common\Services\Intf\ICratingFeeService;
use Illuminate\Support\Facades\DB;

class CratingFeeService extends CommonService implements ICratingFeeService
{
    protected function getDefaultModel()
    {
        return CratingFee::getTableName();
    }

    protected function getDefaultClass()
    {
        return CratingFee::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $query = CratingFee::where('id', '>', 0);

        $sorder_type = isset($filter['order_type']) ? $filter['order_type'] : 'min_count';
        $sdir = isset($filter['sdir']) ? $filter['sdir'] : 'asc';

        if ($sorder_type) {
            $query->orderBy($sorder_type, $sdir);
        }

        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function getAll()
    {
        return CratingFee::where('id', '>', 0)->orderBy('min_count', 'DESC')->get();
    }

    public function findById($id)
    {
        $rResult = CratingFee::where('id', '=', $id)->first();
        return $rResult;
    }

    public function create($arrInput)
    {
        $owner = new CratingFee($arrInput);
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
            $version = CratingFee::find($id);
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
            CratingFee::where('id', '=', $id)->delete();
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
