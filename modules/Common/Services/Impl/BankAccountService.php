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
