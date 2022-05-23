<?php

namespace Modules\Shop\Services\Impl;

use Modules\Common\Entities\Shop;
use Modules\Common\Services\Impl\CommonService;
use Modules\Shop\Services\Intf\IShopService;
use Illuminate\Support\Facades\DB;

class ShopService extends CommonService implements IShopService
{
    protected function getDefaultModel()
    {
        return Shop::getTableName();
    }

    protected function getDefaultClass()
    {
        return Shop::class;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function search($filter)
    {
        return [1];
    }

    public function getByIds($userid)
    {
        $query = Shop::with(array('CartItems' => function ($query) {
            $query->where('status', '=', 1)->where('is_deleted', '=', 0)->orderBy('id', 'desc');
        }))->where('user_id', '=', $userid);
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Shop::where('id', '=', $id)->first();
        return array('shop' => $rResult);
    }

    public function findByUrl($url, $user_id)
    {
        $rResult = Shop::where('url', '=', $url)->where('user_id', '=', $user_id)->first();
        if ($rResult) {
            return $rResult->toArray();
        }
        return null;
    }

    public function create($arrInput)
    {
        $owner = new Shop($arrInput);
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
            $owner = Shop::find($id);
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
