<?php

namespace Modules\Order\Services\Impl;

use Illuminate\Support\Facades\DB;
use Modules\Common\Entities\Carrier;
use Modules\Common\Entities\CarrierPackage;
use Modules\Common\Services\Impl\CommonService;
use Modules\Order\Services\Intf\ICarrierService;

class CarrierService extends CommonService implements ICarrierService
{
    protected function getDefaultModel()
    {
        return Carrier::getTableName();
    }

    protected function getDefaultClass()
    {
        return Carrier::class;
    }

    public function search($filter)
    {
        $query = Carrier::with(['User', 'Order', 'CarrierPackage']);
        $iUser = isset($filter['user_id']) ? $filter['user_id'] : '';
        if (!empty($iUser)) {
            $query->where('user_id', '=', $iUser);
        }

        $iCode = isset($filter['code']) ? $filter['code'] : '';
        if (!empty($iCode)) {
            $query->where('id', '=', $iCode);
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

    public function status()
    {
        $shipping = new Carrier();
        return $shipping->status();
    }

    public function countByStatus()
    {
        $rResult = Carrier::where('id', '>', 0)->groupBy('status')->selectRaw('status, count(*) as total')->get();
        if (!empty($rResult)) {
            return $rResult;
        } else {
            return null;
        }
    }

    public function getByOrder($filter)
    {
        $query = Carrier::with(['CarrierPackage']);
        $iorder = isset($filter['order_id']) ? $filter['order_id'] : 0;
        if ($iorder > 0) {
            $query->where('order_id', '=', $iorder);
        }
        $query->orderBy('id', 'desc');
        $rResult = $query->get()->toArray();
        return $rResult;
    }

    public function findById($id)
    {
        $rResult = Carrier::with(['CarrierPackage', 'User'])->where('id', '=', $id)->first();
        if (!empty($rResult)) {
            return $rResult->toArray();
        } else {
            return null;
        }
    }

    public function create($arrInput)
    {
        $shipping = new Carrier($arrInput);
        DB::beginTransaction();
        try {
            $shipping->save();
            if (!empty($arrInput['carrier_package'])) {
                $carrier_package = [];
                foreach ($arrInput['carrier_package'] as $pk) {
                    $carrier_package[] = new CarrierPackage($pk);
                }
                $shipping->CarrierPackage()->saveMany($carrier_package);
            }

            DB::commit();
            return $shipping;
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
            $shipping = Carrier::find($id);
            $shipping->update($arrInput);
            if (isset($arrInput['carrier_package']) && !empty($arrInput['carrier_package'])) {
                $previousCarrierPackageIds = CarrierPackage::Where('carrier_id', '=', $id)->pluck('id');
                foreach ($arrInput['carrier_package'] as $row) {
                    if (!isset($row['id']) || $row['id'] == 0) {
                        $pkItem = new CarrierPackage(array_merge([
                            'carrier_id' => $id
                        ], $row));
                        $pkItem->save();
                    } else {
                        if (is_numeric($index = $previousCarrierPackageIds->search($row['id']))) {
                            $previousCarrierPackageIds->forget($index);
                        }
                        $pkItem = CarrierPackage::find($row['id']);
                        $pkItem->update($row);
                    }
                }
                CarrierPackage::whereIn('id', $previousCarrierPackageIds)->delete();
            }

            DB::commit();
            return $shipping;
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
            CarrierPackage::Where('carrier_id', '=', $id)->delete();
            Carrier::find($id)->delete();
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
