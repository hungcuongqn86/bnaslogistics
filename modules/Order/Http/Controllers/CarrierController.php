<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Entities\CarrierPackage;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Order\Services\OrderServiceFactory;

class CarrierController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function myshipping(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function status()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function countByStatus(Request $request)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->countByStatus(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function getByOrder(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->getByOrder($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function reUpdate($id)
    {
        try {
            $carrier = OrderServiceFactory::mCarrierService()->findById($id);
            if (!empty($carrier)) {
                $carrierPk = $carrier['carrier_package'];
                if (sizeof($carrierPk) > 0) {
                    // Lay ti gia tu setting
                    $settingRate = CommonServiceFactory::mSettingService()->findByKey('rate');
                    $rate = (int)$settingRate['setting']['value'];

                    // Lay vip
                    $ck_vc = 0;
                    $vip = CommonServiceFactory::mVipService()->findById($carrier['user']['vip']);
                    if (!empty($vip)) {
                        $ck_vc = $vip['ck_vc'];
                    }

                    // Lay bang gia kiem dem
                    $inspectionFee = CommonServiceFactory::mInspectionFeeService()->getAll();

                    $count_product = 0;
                    foreach ($carrierPk as $pkItem) {
                        $count_product = $count_product + $pkItem['product_count'];
                    }

                    // Kiem dem
                    $phi_kiem_dem_cs = 0;
                    if ($carrier['kiem_hang'] == 1) {
                        foreach ($inspectionFee as $feeItem) {
                            if ($feeItem->min_count <= $count_product) {
                                $phi_kiem_dem_cs = $feeItem->val;
                                break;
                            }
                        }
                    }

                    $phi_kiem_dem_tt = 0;
                    if ($phi_kiem_dem_cs != 0) {
                        $phi_kiem_dem_tt = $count_product * $phi_kiem_dem_cs;
                    }

                    $carrier['product_count'] = $count_product;
                    $carrier['vip_id'] = $vip['id'];
                    $carrier['ck_vc'] = $ck_vc;
                    $carrier['phi_kiem_dem_cs'] = $phi_kiem_dem_cs;
                    $carrier['phi_kiem_dem_tt'] = $phi_kiem_dem_tt;
                    $carrier['ti_gia'] = $rate;
                    OrderServiceFactory::mCarrierService()->update($carrier);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'china_warehouses_id' => 'required',
            'china_warehouses_address' => 'required'
        ];
        $arrMessages = [
            'china_warehouses_id.required' => 'Phải chọn kho Trung Quốc nhận hàng',
            'china_warehouses_address.required' => 'Phải chọn kho Trung Quốc nhận hàng'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        if (!isset($input['carrier_package']) || empty($input['carrier_package'])) {
            return $this->sendError('Error', ['Phải nhập vận đơn!']);
        }

        $carrier_package = $input['carrier_package'];
        foreach ($carrier_package as $package) {
            if (empty($package['package_code'])) {
                return $this->sendError('Error', ['Dữ liệu vận đơn không đủ! Phải nhập mã vận đơn!']);
            }
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $input['user_id'] = $user['id'];
            $input['kiem_hang'] = (int)$input['kiem_hang'];
            $input['dong_go'] = (int)$input['dong_go'];
            $input['bao_hiem'] = (int)$input['bao_hiem'];
            $input['status'] = 1;
            // return $this->sendResponse($input, 'Successfully.');
            $create = OrderServiceFactory::mCarrierService()->create($input);
            if (!empty($create)) {
                self::reUpdate($create['id']);
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'china_warehouses_id' => 'required',
            'china_warehouses_address' => 'required'
        ];
        $arrMessages = [
            'china_warehouses_id.required' => 'Phải chọn kho Trung Quốc nhận hàng',
            'china_warehouses_address.required' => 'Phải chọn kho Trung Quốc nhận hàng'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        if (!isset($input['carrier_package']) || empty($input['carrier_package'])) {
            return $this->sendError('Error', ['Phải nhập vận đơn!']);
        }

        $carrier_package = $input['carrier_package'];
        foreach ($carrier_package as $package) {
            if (empty($package['package_code'])) {
                return $this->sendError('Error', ['Dữ liệu vận đơn không đủ! Phải nhập mã vận đơn!']);
            }
        }

        $carrier = OrderServiceFactory::mCarrierService()->findById($id);
        if (empty($carrier)) {
            return $this->sendError('Error', ['Không tồn tại yêu cầu ký gửi!']);
        }

        DB::beginTransaction();
        try {
            $input['kiem_hang'] = (int)$input['kiem_hang'];
            $input['dong_go'] = (int)$input['dong_go'];
            $input['bao_hiem'] = (int)$input['bao_hiem'];
            $update = OrderServiceFactory::mCarrierService()->update($input);
            if (!empty($update)) {
                self::reUpdate($update['id']);
            }
            DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        $carrier = OrderServiceFactory::mCarrierService()->findById($id);
        if (empty($carrier)) {
            return $this->sendError('Error', ['Không tồn tại yêu cầu ký gửi!']);
        }

        if ($carrier['status'] > 1) {
            return $this->sendError('Error', ['Không xóa được yêu cầu ký gửi đã duyệt!']);
        }

        try {
            return $this->sendResponse(OrderServiceFactory::mCarrierService()->delete($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function genOrderCode($uId, $uCode)
    {
        try {
            // 7.	Mã đơn hàng, mã số khách hàng+ số đơn đã mua, như: mã KH 224655+0001, 224655+0002…..
            $code = 'KG';
            $topOrder = OrderServiceFactory::mOrderService()->findByTopCode($uId);
            if (!empty($topOrder)) {
                $code = (string)((int)$topOrder + 1);
            } else {
                $code = $uCode . '0001';
            }
            return $code;
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function approve(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required',
            'status' => 'required',
        ];
        $arrMessages = [
            'id.required' => 'id.required',
            'status.required' => 'status.required',
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $user = $request->user();
        if ($user['type'] == 1) {
            return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
        }

        $shipping = OrderServiceFactory::mCarrierService()->findById($input['id']);
        if (empty($shipping)) {
            return $this->sendError('Error', ['Không tồn tại yêu cầu ký gửi!']);
        }

        try {
            DB::beginTransaction();
            $carrierInput['approve_id'] = $user['id'];
            $carrierInput['approve_at'] = date('Y-m-d H:i:s');
            $carrierInput['id'] = $input['id'];
            $carrierInput['status'] = $input['status'];
            $update = OrderServiceFactory::mCarrierService()->update($carrierInput);
            if ((!empty($update)) && ($input['status'] == '2')) {
                // Tao don hang
                $orderInput = Array(
                    'user_id' => $update->user_id,
                    'carrier_id' => $update->id,
                    'shipping' => 1,
                    'ti_gia' => $update->ti_gia,
                    'count_product' => $update->product_count,
                    'kiem_hang' => $update->kiem_hang,
                    'dong_go' => $update->dong_go,
                    'bao_hiem' => $update->bao_hiem,
                    'chinh_ngach' => $update->chinh_ngach,
                    'vat' => $update->vat,
                    'tien_hang' => 0,
                    'tra_shop' => 0,
                    'vip_id' => $update->vip_id,
                    'ck_vc' => $update->ck_vc,
                    'phi_kiem_dem_cs' => $update->phi_kiem_dem_cs,
                    'phi_kiem_dem_tt' => $update->phi_kiem_dem_tt,
                    'code' => self::genOrderCode($shipping['user']['id'], $shipping['user']['code']),
                    'hander' => 1,
                    'china_warehouses_id' => $update->china_warehouses_id,
                    'china_warehouses_address' => $update->china_warehouses_address,
                    'status' => 4,
                    'dat_coc' => 0
                );

                if (isset($shipping['user']['hander'])) {
                    $orderInput['hander'] = $shipping['user']['hander'];
                }

                $order = OrderServiceFactory::mOrderService()->create($orderInput);
                if (!empty($order)) {
                    // History
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 10
                    ];
                    OrderServiceFactory::mHistoryService()->create($history);

                    // Package
                    if (!empty($shipping['carrier_package'])) {
                        foreach ($shipping['carrier_package'] as $pk) {
                            $package = [
                                'order_id' => $order['id'],
                                'package_code' => $pk['package_code'],
                                'status' => 3,
                                'is_main' => $pk['is_main'],
                                'product_name' => $pk['product_name'],
                                'product_count' => $pk['product_count'],
                                'carrier_brand' => $pk['carrier_brand'],
                                'description' => $pk['description'],
                                'note' => $pk['note'],
                            ];
                            OrderServiceFactory::mPackageService()->create($package);
                        }
                    }
                }
            }
            DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
