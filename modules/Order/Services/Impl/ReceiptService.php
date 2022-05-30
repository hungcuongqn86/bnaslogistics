<?php

namespace Modules\Order\Services\Impl;

use Illuminate\Support\Facades\DB;
use Modules\Common\Entities\Receipt;
use Modules\Common\Entities\Package;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\IReceiptService;

class ReceiptService extends CommonService implements IReceiptService
{
    protected function getDefaultModel()
    {
        return Receipt::getTableName();
    }

    protected function getDefaultClass()
    {
        return Receipt::class;
    }

    public function search($filter)
    {

        return [];
    }

    public function findById($id)
    {
        $rResult = Receipt::with(array('Package' => function ($query) {
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
        $rResult = Receipt::whereYear('receipt_date', '=', $y)->whereMonth('receipt_date', '=', $m)->orderBy('code', 'desc')->first();
        if (!empty($rResult)) {
            return $rResult['code'];
        } else {
            return '';
        }
    }

    public function create($arrInput)
    {
        $receipt = new Receipt($arrInput);
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
            $receipt = Receipt::find($id);
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
