<?php

namespace Modules\Order\Services\Impl;

use Illuminate\Support\Facades\DB;
use Modules\Common\Entities\Bag;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IBagService;

class BagService extends CommonService implements IBagService
{
    protected function getDefaultModel()
    {
        return Bag::getTableName();
    }

    protected function getDefaultClass()
    {
        return Bag::class;
    }

    public function search($filter)
    {

        $query = Bag::with(['Package', 'User']);

        $sReceiptCode = isset($filter['code']) ? $filter['code'] : '';
        if (!empty($sReceiptCode)) {
            $query->where('code', '=', $sReceiptCode);
        }

        $sPackageCode = isset($filter['package_code']) ? $filter['package_code'] : '';
        if (!empty($sPackageCode)) {
            $query->whereHas('Package', function ($q) use ($sPackageCode) {
                $q->where('package_code', '=', $sPackageCode);
            });
        }

        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Bag::with(array('Package' => function ($query) {
            $query->orderBy('id');
        }))->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function findByTopCode($y, $m)
    {
        $rResult = Bag::whereYear('created_at', '=', $y)->whereMonth('created_at', '=', $m)->orderBy('code', 'desc')->first();
        if (!empty($rResult)) {
            return $rResult['code'];
        } else {
            return '';
        }
    }

    public function create($arrInput)
    {
        $receipt = new Bag($arrInput);
        DB::beginTransaction();
        try {
            $receipt->save();
            DB::commit();
            return $receipt;
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
            $receipt = Bag::find($id);
            $receipt->update($arrInput);
            DB::commit();
            return $receipt;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
