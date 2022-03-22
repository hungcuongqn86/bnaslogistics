<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class PackageController extends CommonController
{
    const arrVipData = [0, 2, 4, 6, 8, 10, 15];

    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            if ($user->type === 1) {
                $input['user_id'] = $user->id;
            }

            if ($user->hasRole('employees')) {
                $input['hander'] = $user->id;
            }

            return $this->sendResponse(OrderServiceFactory::mPackageService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function status()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'order_id' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'order_id.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $create = OrderServiceFactory::mPackageService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'order_id' => 'required',
                'package_code' => 'nullable|unique:package,package_code,' . $input['id']
            ];
            $arrMessages = [
                'order_id.required' => 'order_id.required',
                'package_code.unique' => 'Mã vận đơn ' . $input['package_code'] . ' đã tồn tại!'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $package = OrderServiceFactory::mPackageService()->findById($input['id']);
            if (empty($package)) {
                return $this->sendError('Error', ['Kiện hàng không tồn tại!']);
            }

            $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
            if (empty($order)) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }
            $user = $request->user();

            $history = array();
            $orderInput = array();

            if (!empty($input['contract_code'])) {
                if ($package['status'] < 2) {
                    $input['status'] = 2;
                }
                if ($order['status'] < 4) {
                    $orderInput['id'] = $order['id'];
                    $orderInput['status'] = 4;
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 4,
                        'content' => 'Mã hợp đồng ' . $input['contract_code']
                    ];
                }
            }

            if (!empty($input['package_code'])) {
                if ($package['status'] < 3) {
                    $input['status'] = 3;
                }
                if ($order['status'] < 4) {
                    $orderInput['id'] = $order['id'];
                    $orderInput['status'] = 4;
                }
            }

            // Tien can nang
            if (!empty($input['weight_qd'])) {
                $weight_qd = $input['weight_qd'];
                $gia_can_nang = 0;
                if (!empty($input['gia_can'])) {
                    $gia_can_nang = $input['gia_can'];
                } else {
                    if (!empty($order['user']['weight_price'])) {
                        $gia_can_nang = $order['user']['weight_price'];
                    } else {
                        $setting = CommonServiceFactory::mSettingService()->findByKey('weight_price');
                        $gia_can_nang = (int)$setting['setting']['value'];
                    }
                }
                $input['gia_can'] = $gia_can_nang;

                $vip = $order['vip'];
                $vipCn = self::arrVipData[$vip];
                $tiencan = $gia_can_nang * $weight_qd;
                $chietkhau = round($tiencan * $vipCn / 100, 2);
                $input['tien_can'] = $tiencan - $chietkhau;
                $input['vip_cn'] = $vipCn;
            }

            // Tien thanh ly
            $arrPk = $order['package'];
            $tienthanhly = 0;
            if ($arrPk[0]['id'] == $input['id']) {
                $tongTien = $order['tong'];
                if (!empty($order['phi_kiem_dem']) && $order['phi_kiem_dem'] > 0) {
                    $tongTien = $tongTien + $order['phi_kiem_dem'];
                }

                $tigia = $order['rate'];
                foreach ($arrPk as $pk) {
                    if ($pk['ship_khach'] && $pk['ship_khach'] > 0) {
                        $ndt = $pk['ship_khach'];
                        $vnd = $ndt * $tigia;
                        $tongTien = $tongTien + $vnd;
                    }
                }
                $thanh_toan = empty($order['thanh_toan']) ? 0 : $order['thanh_toan'];
                $tienthanhly = $tongTien - $thanh_toan;
            }
            $input['tien_thanh_ly'] = $tienthanhly;

            $update = OrderServiceFactory::mPackageService()->update($input);
            if (!empty($update)) {
                if ($input['status'] == 8) {
                    // Check huy
                    $check = OrderServiceFactory::mOrderService()->checkCancel($order['id']);
                    if ($check) {
                        $orderInput['id'] = $order['id'];
                        $orderInput['status'] = 6;
                        $tiencoc = $order['thanh_toan'];
                        if (!empty($tiencoc) && $tiencoc > 0) {
                            // Hoan tien
                            $orderInput['datcoc_content'] = "Hủy mã, hoàn tiền cọc.";
                            $orderInput['thanh_toan'] = 0;
                            $orderInput['count_product'] = 0;
                            $orderInput['tien_hang'] = 0;
                            $orderInput['phi_tam_tinh'] = 0;
                            $orderInput['tong'] = 0;

                            $userId = $order['user_id'];
                            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $userId]);

                            // Transaction
                            $transaction = [
                                'user_id' => $userId,
                                'type' => 5,
                                'code' => $order['id'] . '.P' . $update['id'],
                                'value' => $tiencoc,
                                'debt' => $debt + $tiencoc,
                                'content' => "Hủy mã, hoàn tiền cọc."
                            ];
                            CommonServiceFactory::mTransactionService()->create($transaction);

                            // update card
                            CartServiceFactory::mCartService()->cancelOrder($order['id']);
                        }

                        $history = [
                            'user_id' => $user['id'],
                            'order_id' => $order['id'],
                            'type' => 6,
                            'content' => "Hủy mã, hoàn tiền cọc."
                        ];
                    }
                }

                if (!empty($orderInput['id'])) {
                    OrderServiceFactory::mOrderService()->update($orderInput);
                }

                if (!empty($history['user_id'])) {
                    OrderServiceFactory::mHistoryService()->create($history);
                }
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
