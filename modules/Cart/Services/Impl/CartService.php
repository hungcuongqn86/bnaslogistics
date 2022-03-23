<?php

namespace Modules\Cart\Services\Impl;

use Illuminate\Support\Facades\DB;
use Modules\Cart\Services\Intf\ICartService;
use Modules\Common\Entities\Cart;
use Modules\Common\Entities\CartItem;
use Modules\Common\Services\Impl\CommonService;

class CartService extends CommonService implements ICartService
{
    protected function getDefaultModel()
    {
        return Cart::getTableName();
    }

    protected function getDefaultClass()
    {
        return Cart::class;
    }

    public function search($userId)
    {
        return [];
    }

    public function getByUser($userid)
    {
        $query = Cart::with(['CartItems', 'Shop'])->where('user_id', '=', $userid)->where('status', '=', 1)->orderBy('id', 'desc');
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Cart::with(['CartItems', 'User', 'Shop'])->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $owner = new Cart($arrInput);
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
            $owner = Cart::find($id);
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
            Cart::where('id', '=', $id)->delete();
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


    // Cart Item
    public function itemCreate($arrInput)
    {
        $owner = new CartItem($arrInput);
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

    public function itemFindById($id)
    {
        $rResult = CartItem::where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function itemUpdate($arrInput)
    {
        $id = $arrInput['id'];
        try {
            $owner = CartItem::find($id);
            $owner->update($arrInput);
            return $owner;
        } catch (QueryException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function itemDelete($id)
    {
        DB::beginTransaction();
        try {
            CartItem::where('id', '=', $id)->delete();
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
