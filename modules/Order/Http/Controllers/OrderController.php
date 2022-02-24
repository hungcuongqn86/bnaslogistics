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
            $fileName = time() . '.orders.xlsx';
            $file = Excel::store(new OrderExport, $fileName);
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
            if ($order && ($user['type'] == 1) && $order['order']['user_id'] != $user['id']) {
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

    private function genOrderCode()
    {
        try {
            // 7.	Mã đơn hàng, mã số khách hàng+ số đơn đã mua, như: mã KH 224655+0001, 224655+0002…..
            return "";
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function reUpdate($id)
    {
        try {
            $cart = CartServiceFactory::mCartService()->findById($id);
            if ($cart) {
                $cartItems = $cart['cart_items'];
                if (sizeof($cartItems) > 0) {
                    // Lay ti gia tu setting
                    $settingRate = CommonServiceFactory::mSettingService()->findByKey('rate');
                    $rate = (int)$settingRate['setting']['value'];

                    // Lay vip
                    $ck_dv = 0;
                    $vip = CommonServiceFactory::mVipService()->findById($cart['user']['vip']);
                    if (!empty($vip)) {
                        $ck_dv = $vip['ck_dv'];
                    }

                    // Lay bang gia dv
                    $serviceFee = CommonServiceFactory::mServiceFeeService()->getAll();

                    // Lay bang gia kiem dem
                    $inspectionFee = CommonServiceFactory::mInspectionFeeService()->getAll();

                    $tien_hang = 0;
                    $count_product = 0;
                    foreach ($cartItems as $cartItem) {
                        $price = self::convertPrice($cartItem['price']);
                        $amount = $cartItem['amount'];
                        $tien_hang = $tien_hang + round($price * $rate * $amount, 0);
                        $count_product = $count_product + $cartItem['amount'];
                    }

                    // Tinh phi dich vu
                    $phi_dat_hang_cs = 0;
                    foreach ($serviceFee as $feeItem) {
                        if ($feeItem->min_tot_tran * 1000000 <= $tien_hang) {
                            $phi_dat_hang_cs = $feeItem->val;
                            break;
                        }
                    }

                    $phi_dat_hang = round(($phi_dat_hang_cs * $tien_hang) / 100);
                    $ck_dv_tt = round(($phi_dat_hang * $ck_dv) / 100);
                    $phi_dat_hang_tt = $phi_dat_hang - $ck_dv_tt;

                    // Bao hiem
                    $phi_bao_hiem_cs = 0;
                    if ($cart['bao_hiem'] == 1) {
                        $settingBh = CommonServiceFactory::mSettingService()->findByKey('bh_price');
                        $phi_bao_hiem_cs = (int)$settingBh['setting']['value'];
                    }

                    $phi_bao_hiem_tt = ($phi_bao_hiem_cs * $tien_hang) / 100;

                    // Kiem dem
                    $phi_kiem_dem_cs = 0;
                    if ($cart['kiem_hang'] == 1) {
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

                    $cart['count_product'] = $count_product;
                    $cart['tien_hang'] = $tien_hang;
                    $cart['vip_id'] = $vip['id'];
                    $cart['ck_dv'] = $ck_dv;
                    $cart['ck_dv_tt'] = $ck_dv_tt;
                    $cart['phi_dat_hang_cs'] = $phi_dat_hang_cs;
                    $cart['phi_dat_hang'] = $phi_dat_hang;
                    $cart['phi_dat_hang_tt'] = $phi_dat_hang_tt;
                    $cart['phi_bao_hiem_cs'] = $phi_bao_hiem_cs;
                    $cart['phi_bao_hiem_tt'] = $phi_bao_hiem_tt;
                    $cart['phi_kiem_dem_cs'] = $phi_kiem_dem_cs;
                    $cart['phi_kiem_dem_tt'] = $phi_kiem_dem_tt;
                    $cart['ti_gia'] = $rate;
                    CartServiceFactory::mCartService()->update($cart);
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

            // Add Order
            $orderInput = array(
                'user_id'  => (int)$user['id'],
                'cart_id'  => $cartId,
                'code'  => self::genOrderCode(),
                'shipping'  => 0,
                'ti_gia'  => $cart['ti_gia'],
                'count_product'  => $cart['count_product'],
                'kiem_hang'  => $cart['kiem_hang'],
                'dong_go'  => $cart['dong_go'],
                'bao_hiem'  => $cart['bao_hiem'],
                'tien_hang'  => $cart['tien_hang'],
                'vip_id'  => $cart['vip_id'],
                'ck_dv'  => $cart['ck_dv'],
                'ck_dv_tt'  => $cart['ck_dv_tt'],
                'phi_dat_hang_cs'  => $cart['phi_dat_hang_cs'],
                'phi_dat_hang'  => $cart['phi_dat_hang'],
                'phi_dat_hang_tt'  => $cart['phi_dat_hang_tt'],
                'phi_bao_hiem_cs'  => $cart['phi_bao_hiem_cs'],
                'phi_bao_hiem_tt'  => $cart['phi_bao_hiem_tt'],
                'phi_kiem_dem_cs'  => $cart['phi_kiem_dem_cs'],
                'phi_kiem_dem_tt'  => $cart['phi_kiem_dem_tt'],
                'status'  => 2,
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

                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $create['id'],
                    'type' => 1
                ];
                OrderServiceFactory::mHistoryService()->create($history);

                // Package
                $package = [
                    'order_id' => $create['id']
                ];
                OrderServiceFactory::mPackageService()->create($package);
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
        // dd(1);
        $input = $request->all();
        $arrRules = [
            'id' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $order = OrderServiceFactory::mOrderService()->findById($input['id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        $user = $request->user();

        if (!empty($input['phi_dich_vu'])) {
            $input['phi_tam_tinh'] = round($input['tien_hang'] * $input['phi_dich_vu'] / 100, 2);
        }

        try {
            $update = OrderServiceFactory::mOrderService()->update($input);
            if (!empty($update)) {
                // History
                $content = 'Trước khi sửa, phí dịch vụ: ' . $order['order']['phi_dich_vu'] . '%, Phí kiểm đếm: ' . $order['order']['phi_kiem_dem'] . 'vnđ';
                $content .= ' -> Sau khi sửa, phí dịch vụ: ' . $update['phi_dich_vu'] . '%, Phí kiểm đếm: ' . $update['phi_kiem_dem'] . 'vnđ';
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $update['id'],
                    'type' => 8,
                    'content' => $content
                ];
                OrderServiceFactory::mHistoryService()->create($history);
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function optionUpdate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'id' => 'required'
        ];
        $arrMessages = [
            'id.required' => 'id.required'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $order = OrderServiceFactory::mOrderService()->findById($input['id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn không tồn tại!']);
        }

        $user = $request->user();
        try {
            $update = OrderServiceFactory::mOrderService()->update($input);
            if (!empty($update)) {
                // History
                $content = 'Tùy chọn:';
                if (!empty($input['is_kiemdem']) && $input['is_kiemdem']) {
                    $content .= ' Kiểm đếm,';
                }
                if (!empty($input['is_donggo']) && $input['is_donggo']) {
                    $content .= ' Đóng gỗ,';
                }
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $update['id'],
                    'type' => 8,
                    'content' => $content
                ];
                OrderServiceFactory::mHistoryService()->create($history);
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
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

        $order = OrderServiceFactory::mOrderService()->findById($input['id']);
        if (!empty($order) && ($order['order']['status'] > 2)) {
            return $this->sendError('Error', ['Đơn đã đặt cọc!']);
        }

        try {
            $user = $request->user();
            // Transaction
            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $user['id']]);
            if ($debt < $input['dc_value']) {
                return $this->sendError('Dư nợ không đủ để thực hiện đặt cọc!');
            }

            $input['status'] = 3;
            $input['datcoc_content'] = $input['content'];
            $input['thanh_toan'] = $input['dc_value'];
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
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
