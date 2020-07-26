<?php

namespace Modules\Common\Services\Impl;

use Illuminate\Database\QueryException;
use Modules\Common\Entities\WithdrawalRequest;
use Modules\Common\Services\Intf\IWithdrawalRequestService;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestService extends CommonService implements IWithdrawalRequestService
{
    protected function getDefaultModel()
    {
        return WithdrawalRequest::getTableName();
    }

    protected function getDefaultClass()
    {
        return WithdrawalRequest::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $query = WithdrawalRequest::with(['User'])->where('is_deleted', '=', 0);

        $iUser = isset($filter['user_id']) ? $filter['user_id'] : 0;
        if ($iUser > 0) {
            $query->Where('user_id', '=', $iUser);
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function create($arrInput)
    {
        $transaction = new WithdrawalRequest($arrInput);
        DB::beginTransaction();
        try {
            $transaction->save();
            DB::commit();
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findByIds($ids)
    {
        $rResult = WithdrawalRequest::wherein('id', $ids)->get()->toArray();
        return $rResult;
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $transaction = WithdrawalRequest::find($id);
            $transaction->update($arrInput);
            DB::commit();
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($ids)
    {
        DB::beginTransaction();
        try {
            WithdrawalRequest::wherein('id', $ids)->update(['is_deleted' => 1]);
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
