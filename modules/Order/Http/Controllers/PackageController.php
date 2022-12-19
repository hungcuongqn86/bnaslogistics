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

    public function bycode($code)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->findByPkCode($code), 'Successfully.');
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
            $input['is_main'] = 0;
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
                    $package['ship_khach_tt'] = $value * $order['ti_gia'];
                    break;
                case 'ship_tt':
                    $colName = 'Ship thực tế';
                    $value = floatval($value);
                    $package['ship_tt_tt'] = $value * $order['ti_gia'];
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
                case 'vi_tri_kho_viet':
                    $colName = 'Vị trí trong kho Việt';
                    $hide = 1;
                    break;
                case 'ghi_chu_kho_viet':
                    $colName = 'Ghi chú kho Việt';
                    $hide = 1;
                    break;
                case 'vi_tri_kho_trung':
                    $colName = 'Vị trí trong kho Trung';
                    $hide = 1;
                    break;
                case 'ghi_chu_kho_trung':
                    $colName = 'Ghi chú kho Trung';
                    $hide = 1;
                    break;
                case 'weight':
                    $colName = 'Cân nặng';
                    $value = floatval($value);
                    if ($package['cal_option'] == 0) {
                        if ($value > 0) {
                            if ($value < 0.5) {
                                $value = 0.5;
                            }

                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(1);
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
                            $package['weight_qd'] = $value;
                            $package['gia_can'] = $gia_can;
                            $package['tien_can'] = $tiencan;
                            $package['ck_vc_tt'] = $chietkhau;
                            $package['tien_can_tt'] = $tiencan_tt;
                        } else {
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    }

                    if ($order['dong_go'] == 1) {
                        if ($package['dg_cal_option'] == 0) {
                            if ($value > 0) {
                                if ($value < 0.5) {
                                    $value = 0.5;
                                }
                                $setting = CommonServiceFactory::mSettingService()->findByKey('dg_1_price');
                                $dg_1_price = (int)$setting['setting']['value'];

                                $setting = CommonServiceFactory::mSettingService()->findByKey('dg_2_price');
                                $dg_2_price = (int)$setting['setting']['value'];
                                $kg1 = 0;
                                $kg2 = 0;
                                if ($value >= 1) {
                                    $kg1 = 1;
                                    $kg2 = $value - 1;
                                } else {
                                    $kg1 = $value;
                                    $kg2 = 0;
                                }

                                $tien_dong_go = ($kg1 * $dg_1_price) + ($kg2 * $dg_2_price);
                                $package['dg_first_unit'] = 0;
                                $package['dg_1_price'] = $dg_1_price;
                                $package['dg_2_price'] = $dg_2_price;
                                $package['tien_dong_go'] = $tien_dong_go;
                            } else {
                                $package['dg_first_unit'] = 0;
                                $package['dg_1_price'] = 0;
                                $package['dg_2_price'] = 0;
                                $package['tien_dong_go'] = 0;
                            }
                        }
                    }

                    if ($order['bao_hiem'] == 1) {
                        if ($value > 0) {
                            $setting = CommonServiceFactory::mSettingService()->findByKey('chong_soc_1_price');
                            $chong_soc_1_price = (int)$setting['setting']['value'];

                            $setting = CommonServiceFactory::mSettingService()->findByKey('chong_soc_2_price');
                            $chong_soc_2_price = (int)$setting['setting']['value'];
                            $kg1 = 0;
                            $kg2 = 0;
                            if ($value >= 1) {
                                $kg1 = 1;
                                $kg2 = $value - 1;
                            } else {
                                $kg1 = $value;
                                $kg2 = 0;
                            }

                            $tien_chong_soc = ($kg1 * $chong_soc_1_price) + ($kg2 * $chong_soc_2_price);
                            $package['chong_soc_1_price'] = $chong_soc_1_price;
                            $package['chong_soc_2_price'] = $chong_soc_2_price;
                            $package['tien_chong_soc'] = $tien_chong_soc;
                            $package['tien_chong_soc_tt'] = $tien_chong_soc * $order['ti_gia'];
                        } else {
                            // chong soc
                            $package['chong_soc_1_price'] = 0;
                            $package['chong_soc_2_price'] = 0;
                            $package['tien_chong_soc'] = 0;
                            $package['tien_chong_soc_tt'] = 0;
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
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    }

                    if ($order['dong_go'] == 1) {
                        if ($package['dg_cal_option'] == 1) {
                            if ($value > 0) {
                                $cratingFees = CommonServiceFactory::mCratingFeeService()->getAll();

                                $dg_1_price = 0;
                                $dg_2_price = 0;
                                $first_count = 1;
                                foreach ($cratingFees as $feeItem) {
                                    $min_count = floatval($feeItem->min_count);
                                    if ($min_count <= $value) {
                                        $dg_1_price = (int)$feeItem->first_val;
                                        $dg_2_price = (int)$feeItem->val;
                                        $first_count = floatval($feeItem->first_count);
                                        break;
                                    }
                                }

                                $kt1 = 0;
                                $kt2 = 0;
                                if ($first_count == 0) {
                                    $kt1 = 0;
                                    $kt2 = $value;
                                } else {
                                    $kt1 = 1;
                                    $kt2 = ceil(($value - $first_count)/$first_count);
                                }
                                $tien_dong_go = ($kt1 * $dg_1_price) + ($kt2 * $dg_2_price);
                                $package['dg_first_unit'] = $first_count;
                                $package['dg_1_price'] = $dg_1_price;
                                $package['dg_2_price'] = $dg_2_price;
                                $package['tien_dong_go'] = $tien_dong_go;
                            } else {
                                $package['dg_first_unit'] = 0;
                                $package['dg_1_price'] = 0;
                                $package['dg_2_price'] = 0;
                                $package['tien_dong_go'] = 0;
                            }
                        }
                    }
                    break;
                case 'cal_option':
                    $colName = 'Áp giá VC theo';
                    if ($value == 0) {
                        $weight = $package['weight'];
                        if ($weight > 0) {
                            if ($weight < 0.5) {
                                $weight = 0.5;
                            }

                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(1);
                            $gia_can = 0;
                            foreach ($transportFees as $feeItem) {
                                if ($feeItem->min_r <= $weight) {
                                    $gia_can = $feeItem->val;
                                    break;
                                }
                            }

                            $tiencan = $gia_can * $weight;
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
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    } elseif ($value == 1) {
                        $size = $package['size'];
                        if ($size > 0) {
                            // Lay vip
                            $ck_vc = $order['ck_vc'];
                            $transportFees = CommonServiceFactory::mTransportFeeService()->getByType(2);
                            $gia_can = 0;
                            foreach ($transportFees as $feeItem) {
                                if ($feeItem->min_r < $size) {
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
                            $package['gia_can'] = 0;
                            $package['tien_can'] = 0;
                            $package['ck_vc_tt'] = 0;
                            $package['tien_can_tt'] = 0;
                        }
                    }
                    break;
                case 'dg_cal_option':
                    $colName = 'Áp giá ĐG theo';
                    if ($order['dong_go'] == 1) {
                        if ($value == 0) {
                            $weight = $package['weight'];
                            if ($weight > 0) {
                                if ($weight < 0.5) {
                                    $weight = 0.5;
                                }

                                $setting = CommonServiceFactory::mSettingService()->findByKey('dg_1_price');
                                $dg_1_price = (int)$setting['setting']['value'];

                                $setting = CommonServiceFactory::mSettingService()->findByKey('dg_2_price');
                                $dg_2_price = (int)$setting['setting']['value'];
                                $kg1 = 0;
                                $kg2 = 0;
                                if ($weight >= 1) {
                                    $kg1 = 1;
                                    $kg2 = $weight - 1;
                                } else {
                                    $kg1 = $weight;
                                    $kg2 = 0;
                                }

                                $tien_dong_go = ($kg1 * $dg_1_price) + ($kg2 * $dg_2_price);
                                $package['dg_first_unit'] = 0;
                                $package['dg_1_price'] = $dg_1_price;
                                $package['dg_2_price'] = $dg_2_price;
                                $package['tien_dong_go'] = $tien_dong_go;
                            } else {
                                $package['dg_first_unit'] = 0;
                                $package['dg_1_price'] = 0;
                                $package['dg_2_price'] = 0;
                                $package['tien_dong_go'] = 0;
                            }
                        } elseif ($value == 1) {
                            $size = $package['size'];
                            if ($size > 0) {
                                $cratingFees = CommonServiceFactory::mCratingFeeService()->getAll();

                                $dg_1_price = 0;
                                $dg_2_price = 0;
                                $first_count = 1;
                                foreach ($cratingFees as $feeItem) {
                                    $min_count = floatval($feeItem->min_count);
                                    if ($min_count < $size) {
                                        $dg_1_price = (int)$feeItem->first_val;
                                        $dg_2_price = (int)$feeItem->val;
                                        $first_count = floatval($feeItem->first_count);
                                        break;
                                    }
                                }

                                $kt1 = 0;
                                $kt2 = 0;
                                if ($first_count == 0) {
                                    $kt1 = 0;
                                    $kt2 = $size;
                                } else {
                                    $kt1 = 1;
                                    $kt2 = ceil(($size - $first_count)/$first_count);
                                }
                                $tien_dong_go = ($kt1 * $dg_1_price) + ($kt2 * $dg_2_price);
                                $package['dg_first_unit'] = $first_count;
                                $package['dg_1_price'] = $dg_1_price;
                                $package['dg_2_price'] = $dg_2_price;
                                $package['tien_dong_go'] = $tien_dong_go;
                            } else {
                                $package['dg_first_unit'] = 0;
                                $package['dg_1_price'] = 0;
                                $package['dg_2_price'] = 0;
                                $package['tien_dong_go'] = 0;
                            }
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
                // Tien thanh ly
                self::calThanhLy($package['order_id']);

                // Order
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

    private function calThanhLy($order_id)
    {
        DB::beginTransaction();
        try {
            $mainpkId = 0;
            $order = OrderServiceFactory::mOrderService()->findById($order_id);
            $arrPk = $order['package'];
            $tien_ship = 0;
            $tien_ship_tt = 0;

            foreach ($arrPk as $pk) {
                if ($pk['is_main'] == 1) {
                    $mainpkId = $pk['id'];
                }
                if (isset($pk['ship_khach_tt']) && $pk['ship_khach_tt'] > 0) {
                    $tien_ship = $tien_ship + $pk['ship_khach_tt'];
                }
                if (isset($pk['ship_tt_tt']) && $pk['ship_tt_tt'] > 0) {
                    $tien_ship_tt = $tien_ship_tt + $pk['ship_tt_tt'];
                }
            }

            if ($mainpkId > 0) {
                $tienthanhly = $order['tien_hang'] + $order['phi_dat_hang_tt'] + $tien_ship;
                if (isset($order['phi_kiem_dem_tt']) && $order['phi_kiem_dem_tt'] > 0) {
                    $tienthanhly = $tienthanhly + $order['phi_kiem_dem_tt'];
                }
                if (isset($order['dat_coc']) && $order['dat_coc'] > 0) {
                    $tienthanhly = $tienthanhly - $order['dat_coc'];
                }

                $package = OrderServiceFactory::mPackageService()->findById($mainpkId);
                $package['tien_thanh_ly'] = $tienthanhly;
                OrderServiceFactory::mPackageService()->update($package);
            }

            $orderInput = [];
            $orderInput['id'] = $order['id'];
            $orderInput['ship_khach_tt'] = $tien_ship;
            $orderInput['ship_tt_tt'] = $tien_ship_tt;
            OrderServiceFactory::mOrderService()->update($orderInput);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();
        $package = OrderServiceFactory::mPackageService()->findById($id);
        if (empty($package)) {
            return $this->sendError('Error', ['Kiện hàng không tồn tại!']);
        }

        if ($package['is_main'] == 1) {
            return $this->sendError('Error', ['Không thể xóa kiện hàng chính!']);
        }

        if ($package['status'] > 2) {
            return $this->sendError('Error', ['Không thể xóa kiện hàng!']);
        }

        DB::beginTransaction();
        try {
            OrderServiceFactory::mPackageService()->delete($id);
            $history = [
                'user_id' => $user['id'],
                'order_id' => $package['order_id'],
                'type' => 11,
                'content' => "Xóa kiện hàng " . $id,
                'hide' => 1
            ];
            OrderServiceFactory::mHistoryService()->create($history);
            DB::commit();
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
