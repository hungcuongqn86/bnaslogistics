<?php

namespace Modules\Common\Services\Impl;

use Modules\Common\Entities\BankAccount;
use Modules\Common\Services\Intf\IBankAccountService;
use Illuminate\Support\Facades\DB;

class BankAccountService extends CommonService implements IBankAccountService
{
    protected function getDefaultModel()
    {
        return BankAccount::getTableName();
    }

    protected function getDefaultClass()
    {
        return BankAccount::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $type = isset($filter['type']) ? $filter['type'] : 1;
        if ($type == 1) {
            return BankAccount::get([
                'name',
                'account_number',
                'account_name',
                'bin'
            ])->toArray();
        }
        return BankAccount::get()->toArray();
    }

    public function findById($id)
    {
        $rResult = BankAccount::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('bank_account' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $owner = BankAccount::find($id);
            $owner->update($arrInput);
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
}
