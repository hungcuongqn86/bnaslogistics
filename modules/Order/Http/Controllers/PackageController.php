<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function update(Request $request, $id)
    {
        $input = $request->all();
        try {
            $package = OrderServiceFactory::mPackageService()->findById($id);
            if (empty($package)) {
                return $this->sendError('Error', ['Kiện hàng không tồn tại!']);
            }

            $order = OrderServiceFactory::mOrderService()->findById($package['order_id']);
            if (empty($order)) {
                return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
            }

            $user = $request->user();
            $orderInput = array();

            DB::beginTransaction();
            try {
                $dirty = $input['dirty'];
                $value = $input['value'];

                $content = 'Đơn ' . $package['order_id'] . ', kiện ' . $id . ', Thay đổi ';
                $colName = '';
                switch ($dirty) {
                    case 'ship_khach':
                        $colName = 'Ship nội địa';
                        $value = floatval($value);
                        break;
                    case 'ship_tt':
                        $colName = 'Ship thực tế';
                        $value = floatval($value);
                        break;
                    case 'thanh_toan':
                        $colName = 'Thanh toán shop';
                        $value = floatval($value);
                        break;
                    case 'contract_code':
                        $colName = 'Mã hợp đồng';
                        if ($package['status'] < 2) {
                            $package['status'] = 2;
                        }
                        if ($order['status'] < 4) {
                            $orderInput['id'] = $order['id'];
                            $orderInput['status'] = 4;
                        }
                        break;
                    case 'package_code':
                        $colName = 'Mã vận đơn';
                        if ($package['status'] < 3) {
                            $package['status'] = 3;
                        }
                        if ($order['status'] < 4) {
                            $orderInput['id'] = $order['id'];
                            $orderInput['status'] = 4;
                        }
                        break;
                    case 'status':
                        $colName = 'Trạng thái';
                        $value = (int)$value;
                        break;
                    case 'phi_van_phat_sinh':
                        $colName = 'Phí vận phát sinh';
                        $value = (int)$value;
                        break;
                    case 'note_tl':
                        $colName = 'Ghi chú thanh lý';
                        break;
                    case 'weight_qd':
                        $colName = 'Cân nặng qui đổi';
                        $value = floatval($value);
                        $weight_qd = $value;
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
                        break;
                }

                if ($package[$dirty] == $value) {
                    return $this->sendError('Error', ['Thông tin kiện hàng không thay đổi!']);
                }

                $content .= $colName . ': ' . $package[$dirty] . ' -> ' . $value;

                $package[$dirty] = $value;
                $update = OrderServiceFactory::mPackageService()->update($package);
                if (!empty($update)) {
                    if (!empty($orderInput['id'])) {
                        OrderServiceFactory::mOrderService()->update($orderInput);
                    }

                    // Add history
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 11,
                        'content' => $content
                    ];
                    OrderServiceFactory::mHistoryService()->create($history);
                }
                DB::commit();
                return $this->sendResponse($update, 'Successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->sendError('Error', $e->getMessage());
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


            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
