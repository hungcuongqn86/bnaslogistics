<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

                    $carrier['count_product'] = $count_product;
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

    public function update(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'package_count' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'package_count.required' => 'package_count.required',
            'content.required' => 'content.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $update = OrderServiceFactory::mCarrierService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
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
            $input['approve_id'] = $user['id'];
            $input['approve_at'] = date('Y-m-d H:i:s');
            $update = OrderServiceFactory::mCarrierService()->update($input);
            if ((!empty($update)) && ($input['status'] == '2')) {
                // Tao don hang
                $orderInput = Array(
                    'user_id' => $update->user_id,
                    'shop_id' => 1,
                    'status' => 4,
                    'rate' => 1,
                    'count_product' => 0,
                    'count_link' => 0,
                    'tien_hang' => 0,
                    'phi_tam_tinh' => 0,
                    'tong' => 0,
                    'thanh_toan' => 0,
                    'con_thieu' => 0,
                    'shipping' => 1,
                );
                $order = OrderServiceFactory::mOrderService()->create($orderInput);
                if (!empty($order)) {
                    // History
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 10
                    ];
                    OrderServiceFactory::mHistoryService()->create($history);
                    //Package
                    $package = [
                        'order_id' => $order['id']
                    ];
                    OrderServiceFactory::mPackageService()->create($package);
                }

                $update->order_id = $order['id'];
                $update->save();
            }
            DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
