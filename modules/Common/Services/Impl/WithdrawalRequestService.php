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

        $iStatus = isset($filter['status']) ? $filter['status'] : '';
        if (!empty($iStatus)) {
            $query->where('status', '=', $iStatus);
        }

        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->where(function ($q) use ($sKeySearch) {
                $q->whereHas('User', function ($q) use ($sKeySearch) {
                    $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                    $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                    $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
                });
                $q->orWhere('content', 'LIKE', '%' . $sKeySearch . '%');
            });
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

    public function status()
    {
        $shipping = new WithdrawalRequest();
        return $shipping->status();
    }

    public function countByStatus()
    {
        $rResult = WithdrawalRequest::where('is_deleted', '=', 0)->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }
}
