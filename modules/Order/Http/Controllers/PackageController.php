<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Order\Services\OrderServiceFactory;

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
        $hide = 0;

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
                    $hide = 1;
                    break;
                case 'thanh_toan':
                    $colName = 'Thanh toán shop';
                    $value = floatval($value);
                    $hide = 1;
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
                case 'weight':
                    $colName = 'Cân nặng';
                    $value = floatval($value);
                    if ($package['cal_option'] == 0) {
                        if ($value > 0) {
                            $weight_qd = $value;
                            if ($weight_qd < 0.5) {
                                $weight_qd = 0.5;
                            }

                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(1);
                            $gia_can = 0;
                            foreach ($transportFees as $feeItem) {
                                if ($feeItem->min_r <= $weight_qd) {
                                    $gia_can = $feeItem->val;
                                    break;
                                }
                            }

                            $tiencan = $gia_can * $weight_qd;
                            $chietkhau = round($tiencan * $ck_vc / 100, 2);
                            $tiencan_tt = $tiencan - $chietkhau;

                            if ($package['status'] < 4) {
                                $package['status'] = 4;
                            }
                            $package['weight_qd'] = $weight_qd;
                            $package['gia_can'] = $gia_can;
                            $package['tien_can'] = $tiencan;
                            $package['ck_vc_tt'] = $chietkhau;
                            $package['tien_can_tt'] = $tiencan_tt;
                        } else {
                            $package['weight_qd'] = 0;
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    }
                    break;
                case 'size':
                    $colName = 'Kích thước';
                    $value = floatval($value);
                    if ($package['cal_option'] == 1) {
                        if ($value > 0) {
                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(2);
                            $gia_can = 0;
                            foreach ($transportFees as $feeItem) {
                                if ($feeItem->min_r <= $value) {
                                    $gia_can = $feeItem->val;
                                    break;
                                }
                            }

                            $tiencan = $gia_can * $value;
                            $chietkhau = round($tiencan * $ck_vc / 100, 2);
                            $tiencan_tt = $tiencan - $chietkhau;

                            if ($package['status'] < 4) {
                                $package['status'] = 4;
                            }

                            $package['gia_can'] = $gia_can;
                            $package['tien_can'] = $tiencan;
                            $package['ck_vc_tt'] = $chietkhau;
                            $package['tien_can_tt'] = $tiencan_tt;
                        } else {
                            $package['weight_qd'] = 0;
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    }
                    break;
                case 'cal_option':
                    $colName = 'Áp giá theo';
                    if ($value == 0) {
                        $weight = $package['weight'];
                        if ($weight > 0) {
                            $weight_qd = $weight;
                            if ($weight_qd < 0.5) {
                                $weight_qd = 0.5;
                            }

                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(1);
                            $gia_can = 0;
                            foreach ($transportFees as $feeItem) {
                                if ($feeItem->min_r <= $weight_qd) {
                                    $gia_can = $feeItem->val;
                                    break;
                                }
                            }

                            $tiencan = $gia_can * $weight_qd;
                            $chietkhau = round($tiencan * $ck_vc / 100, 2);
                            $tiencan_tt = $tiencan - $chietkhau;

                            if ($package['status'] < 4) {
                                $package['status'] = 4;
                            }
                            $package['weight_qd'] = $weight_qd;
                            $package['gia_can'] = $gia_can;
                            $package['tien_can'] = $tiencan;
                            $package['ck_vc_tt'] = $chietkhau;
                            $package['tien_can_tt'] = $tiencan_tt;
                        } else {
                            $package['weight_qd'] = 0;
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    } else {
                        $size = $package['size'];
                        if ($size > 0) {
                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(2);
                            $gia_can = 0;
                            foreach ($transportFees as $feeItem) {
                                if ($feeItem->min_r <= $size) {
                                    $gia_can = $feeItem->val;
                                    break;
                                }
                            }

                            $tiencan = $gia_can * $size;
                            $chietkhau = round($tiencan * $ck_vc / 100, 2);
                            $tiencan_tt = $tiencan - $chietkhau;

                            if ($package['status'] < 4) {
                                $package['status'] = 4;
                            }
                            $package['gia_can'] = $gia_can;
                            $package['tien_can'] = $tiencan;
                            $package['ck_vc_tt'] = $chietkhau;
                            $package['tien_can_tt'] = $tiencan_tt;
                        } else {
                            $package['weight_qd'] = 0;
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    }
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
                    'content' => $content,
                    'hide' => $hide
                ];
                OrderServiceFactory::mHistoryService()->create($history);
            }
            DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        $package = OrderServiceFactory::mPackageService()->findById($id);
        if (empty($package)) {
            return $this->sendError('Error', ['Kiện hàng không tồn tại!']);
        }

        if ($package['status'] > 2) {
            return $this->sendError('Error', ['Không thể xóa kiện hàng!']);
        }
        try {
            OrderServiceFactory::mPackageService()->delete($id);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
