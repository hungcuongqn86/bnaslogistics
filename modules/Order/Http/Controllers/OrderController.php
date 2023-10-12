<?php

namespace Modules\Order\Http\Controllers;

use App\Exports\OrderExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Order\Services\OrderServiceFactory;

class OrderController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            $currentUser = Auth::user();
            if ($currentUser->hasRole('employees')) {
                $input['hander'] = $currentUser['id'];
            }
            return $this->sendResponse(OrderServiceFactory::mOrderService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $input = $request->all();
            $fileName = time() . '.orders.xlsx';
            $file = Excel::store(new OrderExport($input), $fileName);
            return $this->sendResponse($fileName, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function download(Request $request, $filename)
    {
        try {
            return response()->download(storage_path("app/{$filename}"));
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function export1(Request $request)
    {
        $input = $request->all();
        try {
            $data = OrderServiceFactory::mOrderService()->export($input);
            $orders = [];
            foreach ($data as $order) {
                foreach ($order['cart'] as $key => $cart) {
                    if (!$key) {
                        $orders[] = array(
                            'id' => $order['id'],
                            'link' => $cart['pro_link']
                        );
                    } else {
                        $orders[] = array(
                            'id' => '',
                            'link' => $cart['pro_link']
                        );
                    }
                }
            }

            $fileName = time() . '.orders';
            $res = Excel::create($fileName, function ($excel) use ($orders) {
                $excel->sheet('Orders-link', function ($sheet) use ($orders) {
                    $sheet->fromArray($orders);
                    $sheet->setCellValue('A1', 'Đơn hàng');
                    $sheet->setCellValue('B1', 'Link sp');
                });
            })->store('xlsx', public_path('exports'), true);

            return $this->sendResponse($res, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function countByStatus(Request $request)
    {
        try {
            $input = $request->all();
            $currentUser = Auth::user();
            if ($currentUser->hasRole('employees')) {
                $input['hander'] = $currentUser['id'];
            }
            return $this->sendResponse(OrderServiceFactory::mOrderService()->countByStatus($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function comments(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            $input['type'] = $user->type;
            $input['admin'] = false;
            $currentUser = Auth::user();
            if ($currentUser->hasRole('admin')) {
                $input['admin'] = true;
            }
            return $this->sendResponse(OrderServiceFactory::mOrderService()->comments($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function allcomments(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            $input['type'] = $user->type;
            $input['admin'] = false;
            $currentUser = Auth::user();
            if ($currentUser->hasRole('admin')) {
                $input['admin'] = true;
            }
            return $this->sendResponse(OrderServiceFactory::mOrderService()->allcomments($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function myOrder(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $input['user_id'] = $user->id;
            return $this->sendResponse(OrderServiceFactory::mOrderService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function myCountByStatus(Request $request)
    {
        try {
            $user = $request->user();
            $retn = array();

            $arrCountOrder = OrderServiceFactory::mOrderService()->myCountByStatus($user->id);
            foreach ($arrCountOrder as $item) {
                $item['type'] = 'od';
                $retn[] = $item;
            }

            $arrCountPk = OrderServiceFactory::mPackageService()->myOrderCountByStatus($user->id);
            foreach ($arrCountPk as $item) {
                $item['type'] = 'pk';
                $retn[] = $item;
            }

            return $this->sendResponse($retn, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id, Request $request)
    {
        try {
            $user = $request->user();
            $order = OrderServiceFactory::mOrderService()->findById($id);
            if ($order && ($user['type'] == 1) && $order['user_id'] != $user['id']) {
                return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
            }
            return $this->sendResponse($order, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function status()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mOrderService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function historyTypes()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mHistoryService()->types(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function genOrderCode($uId, $uCode)
    {
        try {
            // 7.	Mã đơn hàng, mã số khách hàng+ số đơn đã mua, như: mã KH 224655+0001, 224655+0002…..
            $code = '';
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

    private function convertPrice($priceStr)
    {
        $price = str_replace(' ', '', $priceStr);
        $price = explode('-', $price)[0];
        $price = str_replace(',', '.', $price);
        return $price;
    }

    private function reUpdate($id)
    {
        try {
            $order = OrderServiceFactory::mOrderService()->findById($id);
            if ($order) {
                $orderItems = $order['order_items'];
                if (sizeof($orderItems) > 0) {
                    // Lay ti gia
                    $rate = (int)$order['ti_gia'];

                    // Lay vip
                    $ck_dv = $order['ck_dv'];

                    $tra_shop = 0;
                    $tien_hang = 0;
                    $count_product = 0;
                    foreach ($orderItems as $orderItem) {
                        $price = self::convertPrice($orderItem['price']);
                        $amount = $orderItem['amount'];
                        $tra_shop = $tra_shop + ($price * $amount);
                        $tien_hang = $tien_hang + round($price * $rate * $amount, 0);
                        $count_product = $count_product + $orderItem['amount'];
                    }

                    // Tinh phi dich vu
                    $phi_dat_hang_cs = $order['phi_dat_hang_cs'];
                    $phi_dat_hang = round(($phi_dat_hang_cs * $tien_hang) / 100);
                    $ck_dv_tt = round(($phi_dat_hang * $ck_dv) / 100);
                    $phi_dat_hang_tt = $phi_dat_hang - $ck_dv_tt;

                    // Kiem dem
                    // Lay bang gia kiem dem
                    $inspectionFee = CommonServiceFactory::mInspectionFeeService()->getAll();
                    $phi_kiem_dem_cs = 0;
                    if ($order['kiem_hang'] == 1) {
                        if ($order['phi_kiem_dem_cs'] == 0) {
                            foreach ($inspectionFee as $feeItem) {
                                if ($feeItem->min_count <= $count_product) {
                                    $phi_kiem_dem_cs = $feeItem->val;
                                    break;
                                }
                            }
                        } else {
                            $phi_kiem_dem_cs = $order['phi_kiem_dem_cs'];
                        }
                    }

                    $phi_kiem_dem_tt = 0;
                    if ($phi_kiem_dem_cs != 0) {
                        $phi_kiem_dem_tt = $count_product * $phi_kiem_dem_cs;
                    }

                    // Phi du kien
                    $gia_can = 0;
                    $tiencan_tt = 0;
                    $chietkhau_vc = 0;

                    $dg_1_price = 0;
                    $dg_2_price = 0;
                    $tien_dong_go = $order['tien_dong_go_dk'];

                    $chong_soc_1_price = 0;
                    $chong_soc_2_price = 0;
                    $tien_chong_soc = $order['tien_chong_soc_dk'];

                    if (isset($order['cal_option'])) {
                        $cal_option = $order['cal_option'];
                        $ck_vc = $order['ck_vc'];
                        $cn_value = 0;
                        if (($cal_option == 1) && isset($order['can_nang_dk'])) {
                            $cn_value = $order['can_nang_dk'];
                        }
                        if (($cal_option == 2) && isset($order['kich_thuoc_dk'])) {
                            $cn_value = $order['kich_thuoc_dk'];
                        }

                        if ($cn_value > 0) {
                            if ($order['gia_can_dk'] == 0) {
                                $transportFees = CommonServiceFactory::mTransportFeeService()->getByType($cal_option);
                                foreach ($transportFees as $feeItem) {
                                    if ($feeItem->min_r <= $cn_value) {
                                        $gia_can = $feeItem->val;
                                        break;
                                    }
                                }
                            } else {
                                $gia_can = $order['gia_can_dk'];
                            }

                            $tiencan = $gia_can * $cn_value;
                            $chietkhau_vc = round($tiencan * $ck_vc / 100, 2);
                            $tiencan_tt = $tiencan - $chietkhau_vc;
                        }

                        if ($cal_option == 1) {
                            if (($order['dong_go'] == 1) && isset($order['can_nang_dk'])) {
                                $kg_val = $order['can_nang_dk'];
                                if ($order['dg_1_price'] == 0) {
                                    $setting = CommonServiceFactory::mSettingService()->findByKey('dg_1_price');
                                    $dg_1_price = (int)$setting['setting']['value'];
                                } else {
                                    $dg_1_price = $order['dg_1_price'];
                                }

                                if ($order['dg_2_price'] == 0) {
                                    $setting = CommonServiceFactory::mSettingService()->findByKey('dg_2_price');
                                    $dg_2_price = (int)$setting['setting']['value'];
                                } else {
                                    $dg_2_price = $order['dg_2_price'];
                                }

                                $kg1 = 0;
                                $kg2 = 0;
                                if ($kg_val >= 1) {
                                    $kg1 = 1;
                                    $kg2 = $kg_val - 1;
                                } else {
                                    $kg1 = $kg_val;
                                    $kg2 = 0;
                                }

                                $tien_dong_go = ($kg1 * $dg_1_price) + ($kg2 * $dg_2_price);
                            }

                            if (($order['bao_hiem'] == 1) && isset($order['can_nang_dk'])) {
                                $kg_val = $order['can_nang_dk'];
                                if ($order['chong_soc_1_price'] == 0) {
                                    $setting = CommonServiceFactory::mSettingService()->findByKey('chong_soc_1_price');
                                    $chong_soc_1_price = (int)$setting['setting']['value'];
                                } else {
                                    $chong_soc_1_price = $order['chong_soc_1_price'];
                                }

                                if ($order['chong_soc_2_price'] == 0) {
                                    $setting = CommonServiceFactory::mSettingService()->findByKey('chong_soc_2_price');
                                    $chong_soc_2_price = (int)$setting['setting']['value'];
                                } else {
                                    $chong_soc_2_price = $order['chong_soc_2_price'];
                                }

                                $kg1 = 0;
                                $kg2 = 0;
                                if ($kg_val >= 1) {
                                    $kg1 = 1;
                                    $kg2 = $kg_val - 1;
                                } else {
                                    $kg1 = $kg_val;
                                    $kg2 = 0;
                                }

                                $tien_chong_soc = ($kg1 * $chong_soc_1_price) + ($kg2 * $chong_soc_2_price);
                            }
                        }

                        if ($cal_option == 2) {

                        }
                    }

                    $order['count_product'] = $count_product;
                    $order['tra_shop'] = $tra_shop;
                    $order['tien_hang'] = $tien_hang;
                    $order['ck_dv_tt'] = $ck_dv_tt;
                    $order['phi_dat_hang_cs'] = $phi_dat_hang_cs;
                    $order['phi_dat_hang'] = $phi_dat_hang;
                    $order['phi_dat_hang_tt'] = $phi_dat_hang_tt;
                    $order['phi_kiem_dem_cs'] = $phi_kiem_dem_cs;
                    $order['phi_kiem_dem_tt'] = $phi_kiem_dem_tt;

                    $order['gia_can_dk'] = $gia_can;
                    $order['ck_vc_dk'] = $chietkhau_vc;
                    $order['tien_can_dk'] = $tiencan_tt;

                    $order['dg_1_price'] = $dg_1_price;
                    $order['dg_2_price'] = $dg_2_price;
                    $order['tien_dong_go_dk'] = $tien_dong_go;

                    $order['chong_soc_1_price'] = $chong_soc_1_price;
                    $order['chong_soc_2_price'] = $chong_soc_2_price;
                    $order['tien_chong_soc_dk'] = $tien_chong_soc;

                    $order['ti_gia'] = $rate;
                    OrderServiceFactory::mOrderService()->update($order);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $input = $request->all();
            $user = $request->user();

            $cartId = $input['id'];
            $cart = CartServiceFactory::mCartService()->findById($cartId);
            if (empty($cart)) {
                return $this->sendError('Error', ['Không tồn tại giỏ hàng!']);
            }

            if ($cart['user_id'] != $user->id) {
                return $this->sendError('Error', ['Không có quyền sửa giỏ hàng!']);
            }

            // Lay vip
            $ck_dv = 0;
            $ck_vc = 0;
            $deposit = 0;
            $vip = CommonServiceFactory::mVipService()->findById($user->vip);
            if (!empty($vip)) {
                $ck_dv = $vip['ck_dv'];
                $ck_vc = $vip['ck_vc'];
                $deposit = $vip['deposit'];
            }

            // Lay bang gia dv
            $serviceFee = CommonServiceFactory::mServiceFeeService()->getAll();

            // Lay bang gia kiem dem
            $inspectionFee = CommonServiceFactory::mInspectionFeeService()->getAll();

            // Tinh phi dich vu
            $tien_hang = $cart['tien_hang'];
            $phi_dat_hang_cs = 0;
            foreach ($serviceFee as $feeItem) {
                if ($feeItem->min_tot_tran * 1000000 <= $tien_hang) {
                    $phi_dat_hang_cs = $feeItem->val;
                    break;
                }
            }

            // Kiem dem
            $count_product = $cart['count_product'];
            $phi_kiem_dem_cs = 0;
            if ($cart['kiem_hang'] == 1) {
                foreach ($inspectionFee as $feeItem) {
                    if ($feeItem->min_count <= $count_product) {
                        $phi_kiem_dem_cs = $feeItem->val;
                        break;
                    }
                }
            }

            // Add Order
            $orderInput = array(
                'user_id' => (int)$user['id'],
                'shop_id' => $cart['shop_id'],
                'cart_id' => $cartId,
                'code' => self::genOrderCode($user->id, $user->code),
                'shipping' => 0,
                'ti_gia' => $cart['ti_gia'],
                'count_product' => $count_product,
                'kiem_hang' => $cart['kiem_hang'],
                'dong_go' => $cart['dong_go'],
                'bao_hiem' => $cart['bao_hiem'],
                'tien_hang' => $tien_hang,
                'vip_id' => $vip['id'],
                'ck_dv' => $ck_dv,
                'ck_vc' => $ck_vc,
                'deposit' => $deposit,
                'phi_dat_hang_cs' => $phi_dat_hang_cs,
                'phi_kiem_dem_cs' => $phi_kiem_dem_cs,
                'status' => 1,
            );

            if (isset($user['hander'])) {
                $orderInput['hander'] = $user['hander'];
            }

            $create = OrderServiceFactory::mOrderService()->create($orderInput);
            if (!empty($create)) {
                // Add Order Items
                foreach ($cart['cart_items'] as $cart_item) {
                    $orderItemInput = [];
                    $orderItemInput['order_id'] = $create['id'];
                    $orderItemInput['amount'] = $cart_item['amount'];
                    $orderItemInput['begin_amount'] = $cart_item['begin_amount'];
                    $orderItemInput['color'] = $cart_item['color'];
                    $orderItemInput['colortxt'] = $cart_item['colortxt'];
                    $orderItemInput['count'] = $cart_item['count'];
                    $orderItemInput['domain'] = $cart_item['domain'];
                    $orderItemInput['image'] = $cart_item['image'];
                    $orderItemInput['method'] = $cart_item['method'];
                    $orderItemInput['name'] = $cart_item['name'];
                    $orderItemInput['note'] = $cart_item['note'];
                    $orderItemInput['price'] = $cart_item['price'];
                    $orderItemInput['price_arr'] = $cart_item['price_arr'];
                    $orderItemInput['pro_link'] = $cart_item['pro_link'];
                    $orderItemInput['pro_properties'] = $cart_item['pro_properties'];
                    $orderItemInput['rate'] = $cart_item['rate'];
                    $orderItemInput['site'] = $cart_item['site'];
                    $orderItemInput['size'] = $cart_item['size'];
                    $orderItemInput['sizetxt'] = $cart_item['sizetxt'];
                    OrderServiceFactory::mOrderService()->itemCreate($orderItemInput);
                }

                // Update cart
                $cartInput = [];
                $cartInput['id'] = $cartId;
                $cartInput['status'] = 2;
                CartServiceFactory::mCartService()->update($cartInput);

                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $create['id'],
                    'type' => 1
                ];
                OrderServiceFactory::mHistoryService()->create($history);

                // Package
                $package = [
                    'order_id' => $create['id'],
                    'is_main' => 1
                ];
                OrderServiceFactory::mPackageService()->create($package);

                // Re update
                self::reUpdate($create['id']);
            }

            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function itemUpdate($id, Request $request)
    {
        $input = $request->all();
        $user = $request->user();

        if ($user['type'] == 1) {
            return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
        }

        $orderItem = OrderServiceFactory::mOrderService()->itemFindById($id);
        if (empty($orderItem)) {
            return $this->sendError('Error', ['Không tồn tại đơn hàng!']);
        }

        $order = OrderServiceFactory::mOrderService()->findById($orderItem['order_id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
        }

        $dirty = $input['dirty'];
        $value = $input['value'];

        if (($order['status'] > 3) && ($dirty != "nv_note")) {
            return $this->sendError('Error', ['Đơn đã mua hoặc hủy, không thể thay đổi!']);
        }

        if ($orderItem[$dirty] == $value) {
            return $this->sendError('Error', ['Thông tin đơn hàng không thay đổi!']);
        }

        DB::beginTransaction();
        try {
            $content = 'Mã ' . $id . ', Thay đổi ';
            $colName = '';
            switch ($dirty) {
                case 'amount':
                    $colName = 'số lượng';
                    break;
                case 'price':
                    $colName = 'giá';
                    break;
                case 'nv_note':
                    $colName = 'Ghi chú';
                    break;
            }

            $content .= $colName . ': ' . $orderItem[$dirty] . ' -> ' . $value;

            $orderItem[$dirty] = $value;
            $update = OrderServiceFactory::mOrderService()->itemUpdate($orderItem);
            if (!empty($update)) {
                // Re update
                self::reUpdate($orderItem['order_id']);
                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $orderItem['order_id'],
                    'type' => 8,
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
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $user = $request->user();

        if ($user['type'] == 1) {
            return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
        }

        $order = OrderServiceFactory::mOrderService()->findById($id);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        if ($order['status'] > 4) {
            return $this->sendError('Error', ['Đơn đã mua hoặc hủy, không thể thay đổi!']);
        }

        DB::beginTransaction();
        try {
            $dirty = $input['dirty'];
            $value = $input['value'];

            $content = 'Mã ' . $id . ', Thay đổi ';
            $colName = '';
            switch ($dirty) {
                case 'kiem_hang':
                    $colName = 'kiểm hàng';
                    $value = (int)$value;
                    break;
                case 'dong_go':
                    $colName = 'đóng gỗ';
                    $value = (int)$value;
                    break;
                case 'bao_hiem':
                    $colName = 'chống sốc';
                    $value = (int)$value;
                    break;
                case 'chinh_ngach':
                    $colName = 'chính ngạch';
                    $value = (int)$value;
                    break;
                case 'vat':
                    $colName = 'VAT';
                    $value = (int)$value;
                    break;
                case 'phi_dat_hang_cs':
                    $colName = 'Phí đặt hàng';
                    break;
                case 'phi_kiem_dem_cs':
                    $colName = 'Phí kiểm đếm';
                    break;
                case 'can_nang_dk':
                    $colName = 'Cân nặng dự kiến';
                    break;
                case 'gia_can_dk':
                    $colName = 'Giá cân nặng dự kiến';
                    break;
                case 'dg_1_price':
                    $colName = 'Giá đóng gỗ 1 dự kiến';
                    break;
                case 'dg_2_price':
                    $colName = 'Giá đóng gỗ 2 dự kiến';
                    break;
                case 'tien_dong_go_dk':
                    $colName = 'Tiền đóng gỗ dự kiến';
                    $value = (int)$value;
                    break;
                case 'tien_chong_soc_dk':
                    $colName = 'Tiền chống sốc dự kiến';
                    $value = (int)$value;
                    break;
                case 'kich_thuoc_dk':
                    $colName = 'Kích thước dự kiến';
                    break;
                case 'cal_option':
                    $colName = 'Cách tính chi phí dự kiến';
                    break;
            }

            if ($order[$dirty] == $value) {
                return $this->sendError('Error', ['Thông tin đơn hàng không thay đổi!']);
            }

            $content .= $colName . ': ' . $order[$dirty] . ' -> ' . $value;

            $order[$dirty] = $value;
            $update = OrderServiceFactory::mOrderService()->update($order);
            if (!empty($update)) {
                // Re update
                self::reUpdate($id);

                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $id,
                    'type' => 8,
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
    }

    public function reorder(Request $request, $id)
    {
        $user = $request->user();
        $order = OrderServiceFactory::mOrderService()->findById($id);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        DB::beginTransaction();
        try {
            // Lay ti gia tu setting
            $settingRate = CommonServiceFactory::mSettingService()->findByKey('rate');
            $rate = (int)$settingRate['setting']['value'];

            // Lay vip
            $ck_dv = 0;
            $ck_vc = 0;
            $deposit = 0;
            $vip = CommonServiceFactory::mVipService()->findById($user->vip);
            if (!empty($vip)) {
                $ck_dv = $vip['ck_dv'];
                $ck_vc = $vip['ck_vc'];
                $deposit = $vip['deposit'];
            }

            // Lay bang gia dv
            $serviceFee = CommonServiceFactory::mServiceFeeService()->getAll();

            // Lay bang gia kiem dem
            $inspectionFee = CommonServiceFactory::mInspectionFeeService()->getAll();

            $tien_hang = 0;
            $count_product = 0;
            $orderItems = $order['order_items'];
            foreach ($orderItems as $orderItem) {
                $price = $orderItem['price'];
                $amount = $orderItem['amount'];
                $tien_hang = $tien_hang + round($price * $rate * $amount, 0);
                $count_product = $count_product + $orderItem['amount'];
            }

            // Tinh phi dich vu
            $phi_dat_hang_cs = 0;
            foreach ($serviceFee as $feeItem) {
                if ($feeItem->min_tot_tran * 1000000 <= $tien_hang) {
                    $phi_dat_hang_cs = $feeItem->val;
                    break;
                }
            }

            // Kiem dem
            $phi_kiem_dem_cs = 0;
            if ($order['kiem_hang'] == 1) {
                foreach ($inspectionFee as $feeItem) {
                    if ($feeItem->min_count <= $count_product) {
                        $phi_kiem_dem_cs = $feeItem->val;
                        break;
                    }
                }
            }

            // Add Order
            $orderInput = array(
                'user_id' => (int)$user['id'],
                'shop_id' => $order['shop_id'],
                'cart_id' => $order['cart_id'],
                'code' => self::genOrderCode($user->id, $user->code),
                'shipping' => 0,
                'ti_gia' => $rate,
                'count_product' => $count_product,
                'kiem_hang' => $order['kiem_hang'],
                'dong_go' => $order['dong_go'],
                'bao_hiem' => $order['bao_hiem'],
                'tien_hang' => $tien_hang,
                'vip_id' => $vip['id'],
                'ck_dv' => $ck_dv,
                'ck_vc' => $ck_vc,
                'deposit' => $deposit,
                'phi_dat_hang_cs' => $phi_dat_hang_cs,
                'phi_kiem_dem_cs' => $phi_kiem_dem_cs,
                'status' => 2,
            );

            if (isset($user['hander'])) {
                $orderInput['hander'] = $user['hander'];
            }

            $create = OrderServiceFactory::mOrderService()->create($orderInput);
            if (!empty($create)) {
                // Add Order Items
                foreach ($orderItems as $orderItem) {
                    $orderItemInput = [];
                    $orderItemInput['order_id'] = $create['id'];
                    $orderItemInput['amount'] = $orderItem['amount'];
                    $orderItemInput['begin_amount'] = $orderItem['begin_amount'];
                    $orderItemInput['color'] = $orderItem['color'];
                    $orderItemInput['colortxt'] = $orderItem['colortxt'];
                    $orderItemInput['count'] = $orderItem['count'];
                    $orderItemInput['domain'] = $orderItem['domain'];
                    $orderItemInput['image'] = $orderItem['image'];
                    $orderItemInput['method'] = $orderItem['method'];
                    $orderItemInput['name'] = $orderItem['name'];
                    $orderItemInput['note'] = $orderItem['note'];
                    $orderItemInput['price'] = $orderItem['price'];
                    $orderItemInput['price_arr'] = $orderItem['price_arr'];
                    $orderItemInput['pro_link'] = $orderItem['pro_link'];
                    $orderItemInput['pro_properties'] = $orderItem['pro_properties'];
                    $orderItemInput['rate'] = $rate;
                    $orderItemInput['site'] = $orderItem['site'];
                    $orderItemInput['size'] = $orderItem['size'];
                    $orderItemInput['sizetxt'] = $orderItem['sizetxt'];
                    OrderServiceFactory::mOrderService()->itemCreate($orderItemInput);
                }

                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $create['id'],
                    'type' => 1
                ];
                OrderServiceFactory::mHistoryService()->create($history);

                // Package
                $package = [
                    'order_id' => $create['id'],
                    'is_main' => 1
                ];
                OrderServiceFactory::mPackageService()->create($package);
                // Re update
                self::reUpdate($create['id']);
            }

            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function phancong(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required',
            'hander' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required',
            'hander.required' => 'hander.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        try {
            $update = OrderServiceFactory::mOrderService()->update($input);
            if (!empty($update)) {
                // History
                $user = $request->user();
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $input['id'],
                    'type' => 2,
                    'content' => $input['content_pc']
                ];
                OrderServiceFactory::mHistoryService()->create($history);
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function datcoc(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required',
            'dc_value' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required',
            'dc_value.required' => 'dc_value.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $user = $request->user();
        $order = OrderServiceFactory::mOrderService()->findById($input['id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        // Lay vip
        $vip = CommonServiceFactory::mVipService()->findById($user['vip']);
        if (empty($vip)) {
            return $this->sendError('Error', ['Lỗi dữ liệu, hãy thực hiện lại!']);
        }

        if ($vip['deposit'] != $input['dc_percent_value']) {
            return $this->sendError('Error', ['Lỗi dữ liệu, hãy thực hiện lại!']);
        }

        if ($order['user_id'] != $user['id']) {
            return $this->sendError('Error', ['Không có quyền!'], 403);
        }

        if ($order['status'] > 2) {
            return $this->sendError('Error', ['Đơn đã đặt cọc!']);
        }

        DB::beginTransaction();
        try {
            // Transaction
            $datcoc = $order['tien_hang'] + $order['phi_kiem_dem_tt'] + $order['phi_dat_hang_tt'];
            $datcoc = round($datcoc * $vip['deposit'] / 100);
            /*if ($datcoc != $input['dc_value']) {
                return $this->sendError('Error', ['Lỗi dữ liệu, hãy thực hiện lại!']);
            }*/

            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $user['id']]);
            if ($debt < $datcoc) {
                return $this->sendError('Dư nợ không đủ để thực hiện đặt cọc!');
            }

            $input['status'] = 3;
            $input['datcoc_content'] = $input['content'];
            $input['dat_coc'] = $datcoc;
            unset($input['tien_hang']);

            $update = OrderServiceFactory::mOrderService()->update($input);
            if (!empty($update)) {
                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $input['id'],
                    'type' => 3,
                    'content' => $input['content']
                ];
                $historyRs = OrderServiceFactory::mHistoryService()->create($history);

                // Transaction
                $transaction = [
                    'user_id' => $user['id'],
                    'type' => 4,
                    'code' => $input['id'] . '.H' . $historyRs['id'],
                    'value' => $input['dc_value'],
                    'debt' => $debt - $input['dc_value'],
                    'content' => $input['content']
                ];
                CommonServiceFactory::mTransactionService()->create($transaction);
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
        $input = $request->all();
        $user = $request->user();

        $order = OrderServiceFactory::mOrderService()->findById($id);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        if ($order['user_id'] != $user['id']) {
            return $this->sendError('Error', ['Không có quyền thực hiện với đơn này!']);
        }

        if ($order['status'] > 4) {
            return $this->sendError('Error', ['Đơn đã mua hoặc hủy, không thể xóa!']);
        }

        DB::beginTransaction();
        try {
            OrderServiceFactory::mOrderService()->delete($id);
            DB::commit();
            return $this->sendResponse([1], 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
