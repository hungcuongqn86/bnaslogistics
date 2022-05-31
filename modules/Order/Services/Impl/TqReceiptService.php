<?php

namespace Modules\Order\Services\Impl;

use Illuminate\Support\Facades\DB;
use Modules\Common\Entities\TqReceipt;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\ITqReceiptService;

class TqReceiptService extends CommonService implements ITqReceiptService
{
    protected function getDefaultModel()
    {
        return TqReceipt::getTableName();
    }

    protected function getDefaultClass()
    {
        return TqReceipt::class;
    }

    public function search($filter)
    {

        $query = TqReceipt::with(['Package', 'User']);

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

        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->where('note', 'LIKE', '%' . $sKeySearch . '%');
        }

        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = TqReceipt::with(array('Package' => function ($query) {
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
        $rResult = TqReceipt::whereYear('receipt_date', '=', $y)->whereMonth('receipt_date', '=', $m)->orderBy('code', 'desc')->first();
        if (!empty($rResult)) {
            return $rResult['code'];
        } else {
            return '';
        }
    }

    public function create($arrInput)
    {
        $receipt = new TqReceipt($arrInput);
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
            $receipt = TqReceipt::find($id);
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
