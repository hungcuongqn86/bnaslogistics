<?php

namespace Modules\Common\Services\Impl;

use App\User;

// use Modules\Common\Entities\User;
use Modules\Common\Services\Intf\IUserService;
use Illuminate\Support\Facades\DB;

class UserService extends CommonService implements IUserService
{
    protected function getDefaultModel()
    {
        // return User::getTableName();
    }

    protected function getDefaultClass()
    {
        // return User::class;
    }

    public function usersGetAll($filter)
    {
        $query = User::with(['roles']);
        if (isset($filter['type'])) {
            $query->where('type', '=', $filter['type']);
        }
        $rResult = $query->get();
        return $rResult;
    }

    public function handleGetAll()
    {
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'employees']);
        });
        $rResult = $query->get(['id', 'name']);
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        $query = User::with(['Partner', 'roles'])->where('type', '=', 0);

        $iPartner = isset($filter['partner_id']) ? $filter['partner_id'] : 0;
        if ($iPartner > 0) {
            $query->Where('partner_id', '=', $iPartner);
        }

        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->Where(function ($q) use ($sKeySearch) {
                $q->Where('name', 'like', '%' . $sKeySearch . '%');
                $q->orWhere('phone_number', 'like', '%' . $sKeySearch . '%');
                $q->orWhere('email', 'like', '%' . $sKeySearch . '%');
            });
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function custumer($filter)
    {
        $query = User::with(['Handle'])->where('type', '=', 1);

        $iPartner = isset($filter['partner_id']) ? $filter['partner_id'] : 0;
        if ($iPartner > 0) {
            $query->Where('partner_id', '=', $iPartner);
        }

        $iHander = isset($filter['hander']) ? $filter['hander'] : 0;
        if ($iHander > 0) {
            $query->Where('hander', '=', $iHander);
        }

        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->Where('name', 'like', '%' . $sKeySearch . '%');
            $query->orWhere('phone_number', 'like', '%' . $sKeySearch . '%');
            $query->orWhere('email', 'like', '%' . $sKeySearch . '%');
        }
        $query->orderBy('id', 'desc');
        $limit = isset($filter['limit']) ? $filter['limit'] : config('const.LIMIT_PER_PAGE');
        $rResult = $query->paginate($limit)->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = User::with(['Partner', 'roles'])->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return array('user' => $rResult->toArray());
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $owner = new User($arrInput);
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

    public function findByIds($ids)
    {
        $rResult = User::wherein('id', $ids)->get()->toArray();
        return $rResult;
    }

    public function update($arrInput)
    {
        $id = $arrInput['id'];
        DB::beginTransaction();
        try {
            $owner = User::find($id);
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

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            User::find($id)->delete();
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
