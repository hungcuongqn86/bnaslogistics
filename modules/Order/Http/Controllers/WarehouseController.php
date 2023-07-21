<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Entities\Package;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Order\Services\OrderServiceFactory;

class WarehouseController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function bags(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mBagService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function receipts(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mReceiptService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function tqreceipts(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mTqReceiptService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function wait(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mPackageService()->waitMoveOut($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function bills(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billStatus()
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billCreate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'user_id' => 'required',
            'pkcodelist' => 'required'
        ];
        $arrMessages = [
            'user_id.required' => 'Thiếu thông tin khách hàng!',
            'pkcodelist.required' => 'Thiếu thông tin kiện hàng!'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Tạo phiếu xuất không thành công!', $validator->errors()->all());
        }

        $customer = CommonServiceFactory::mUserService()->findById($input['user_id']);
        if (empty($customer)) {
            return $this->sendError('Tạo phiếu xuất không thành công!', ['Không có thông tin khách hàng!']);
        }

        //Bill input
        $user = $request->user();
        $billinput = array();
        $billinput['user_id'] = $input['user_id'];
        $billinput['code'] = self::genBillCode($customer['user']['id'], $customer['user']['code']);
        $billinput['employee_id'] = $user['id'];
        $billinput['status'] = 1;
        $billinput['so_ma'] = 0;

        DB::beginTransaction();
        try {
            //Lay danh sach kien hang
            $packages = OrderServiceFactory::mPackageService()->findByPkCodes($input['pkcodelist']);
            $soma = 0;
            foreach ($packages as $package) {
                $soma = $soma + 1;
                if (!empty($package['bill_id'])) {
                    return $this->sendError('Error', ['Mã vận đơn đã được tạo ở phiếu xuất khác!']);
                }
            }
            $billinput['so_ma'] = $soma;

            $create = OrderServiceFactory::mBillService()->create($billinput);
            if (!empty($create)) {
                foreach ($packages as $package) {
                    $packageInput = array(
                        'id' => $package['id'],
                        'bill_id' => $create['id']
                    );
                    OrderServiceFactory::mPackageService()->update($packageInput);
                }
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function bagCreate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'pkcodelist' => 'required'
        ];
        $arrMessages = [
            'pkcodelist.required' => 'Thiếu thông tin kiện hàng!'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Tạo bao hàng không thành công!', $validator->errors()->all());
        }

        //input
        $user = $request->user();
        $baginput = array();
        $baginput['code'] = self::genBagCode(date("Y"), date("m"));
        $baginput['status'] = $input['status'];
        $baginput['employee_id'] = $user['id'];
        $baginput['note_tq'] = $input['note_tq'];
        $baginput['dvvc'] = $input['dvvc'];

        DB::beginTransaction();
        try {
            //Lay danh sach kien hang
            $packages = OrderServiceFactory::mPackageService()->findByIds($input['pkcodelist']);
            foreach ($packages as $package) {
                if (!empty($package['bag_id'])) {
                    $mes = 'Mã vận đơn ' . $package['package_code'] . ' đã được tạo ở bao hàng khác!';
                    return $this->sendError('Error', [$mes]);
                }
            }

            $create = OrderServiceFactory::mBagService()->create($baginput);
            if (!empty($create)) {
                foreach ($packages as $package) {
                    $packageInput = array(
                        'id' => $package['id'],
                        'bag_id' => $create['id']
                    );
                    if ($create['status'] > 1) {
                        $status = 5;
                        if ($package['status'] > $status) {
                            $status = $package['status'];
                        }

                        $packageInput['status'] = $status;
                    }
                    OrderServiceFactory::mPackageService()->update($packageInput);

                    // Update order
                    $order = OrderServiceFactory::mOrderService()->findById($package['order_id']);
                    if (empty($order)) {
                        return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
                    }
                    $orderInput = array();
                    $orderInput['id'] = $order['id'];
                    $status = 4;
                    if ($order['status'] > $status) {
                        $status = $order['status'];
                    }
                    $orderInput['status'] = $status;
                    OrderServiceFactory::mOrderService()->update($orderInput);

                    // Add history
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 11,
                        'content' => 'Kiện hàng ' . $package['id'] . ' tạo Bao hàng, mã ' . $create['code']
                    ];
                    OrderServiceFactory::mHistoryService()->create($history);
                }
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function bagUpdate(Request $request, $id)
    {
        $input = $request->all();
        $bag = OrderServiceFactory::mBagService()->findById($id);
        if (empty($bag)) {
            return $this->sendError('Error', ['Bao hàng không tồn tại!']);
        }

        DB::beginTransaction();
        try {
            $dirty = $input['dirty'];
            $value = $input['value'];
            $update = $bag;
            if ($dirty != 'package') {
                if ($bag[$dirty] == $value) {
                    return $this->sendError('Error', ['Thông tin bao hàng không thay đổi!']);
                }
                $bagInput['id'] = $bag['id'];
                $bagInput[$dirty] = $value;
                $update = OrderServiceFactory::mBagService()->update($bagInput);
            } else {
                Package::Where('bag_id', '=', $bag['id'])->update(['bag_id' => null]);
                foreach ($value as $row) {
                    if (!empty($row['bag_id'])) {
                        $mes = 'Mã vận đơn ' . $row['package_code'] . ' đã được tạo ở bao hàng khác!';
                        return $this->sendError('Error', [$mes]);
                    }
                    Package::Where('id', '=', $row['id'])->update(['bag_id' => $bag['id']]);
                }
            }

            DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function storebillCreate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'pkcodelist' => 'required'
        ];
        $arrMessages = [
            'pkcodelist.required' => 'Thiếu thông tin kiện hàng!'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Tạo phiếu nhập không thành công!', $validator->errors()->all());
        }

        //input
        $user = $request->user();
        $billinput = array();
        $billinput['receipt_date'] = date('Y-m-d H:i:s');
        $billinput['code'] = self::genStoreBillCode(date("Y"), date("m"));
        $billinput['employee_id'] = $user['id'];
        $billinput['note'] = $input['note'];

        DB::beginTransaction();
        try {
            //Lay danh sach kien hang
            $packages = OrderServiceFactory::mPackageService()->findByIds($input['pkcodelist']);
            foreach ($packages as $package) {
                if (!empty($package['receipt_id'])) {
                    $mes = 'Mã vận đơn ' . $package['package_code'] . ' đã được tạo ở phiếu nhập khác!';
                    return $this->sendError('Error', [$mes]);
                }
            }

            $create = OrderServiceFactory::mReceiptService()->create($billinput);
            if (!empty($create)) {
                foreach ($packages as $package) {
                    $status = 6;
                    if ($package['status'] > $status) {
                        $status = $package['status'];
                    }

                    $packageInput = array(
                        'id' => $package['id'],
                        'receipt_id' => $create['id'],
                        'status' => $status
                    );
                    OrderServiceFactory::mPackageService()->update($packageInput);

                    // Update order
                    $order = OrderServiceFactory::mOrderService()->findById($package['order_id']);
                    if (empty($order)) {
                        return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
                    }
                    $orderInput = array();
                    $orderInput['id'] = $order['id'];
                    $status = 4;
                    if ($order['status'] > $status) {
                        $status = $order['status'];
                    }
                    $orderInput['status'] = $status;
                    OrderServiceFactory::mOrderService()->update($orderInput);

                    // Add history
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 11,
                        'content' => 'Kiện hàng ' . $package['id'] . ' nhập kho Việt, mã phiếu ' . $create['code']
                    ];
                    OrderServiceFactory::mHistoryService()->create($history);
                }
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function tqstorebillCreate(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'pkcodelist' => 'required'
        ];
        $arrMessages = [
            'pkcodelist.required' => 'Thiếu thông tin kiện hàng!'
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Tạo phiếu nhập không thành công!', $validator->errors()->all());
        }

        //input
        $user = $request->user();
        $billinput = array();
        $billinput['receipt_date'] = date('Y-m-d H:i:s');
        $billinput['code'] = self::genTqStoreBillCode(date("Y"), date("m"));
        $billinput['employee_id'] = $user['id'];
        $billinput['note'] = $input['note'];

        DB::beginTransaction();
        try {
            //Lay danh sach kien hang
            $packages = OrderServiceFactory::mPackageService()->findByIds($input['pkcodelist']);
            foreach ($packages as $package) {
                if (!empty($package['tq_receipt_id'])) {
                    $mes = 'Mã vận đơn ' . $package['package_code'] . ' đã được tạo ở phiếu nhập khác!';
                    return $this->sendError('Error', [$mes]);
                }
            }

            $create = OrderServiceFactory::mTqReceiptService()->create($billinput);
            if (!empty($create)) {
                foreach ($packages as $package) {
                    $status = 4;
                    if ($package['status'] > $status) {
                        $status = $package['status'];
                    }

                    $packageInput = array(
                        'id' => $package['id'],
                        'tq_receipt_id' => $create['id'],
                        'status' => $status
                    );
                    OrderServiceFactory::mPackageService()->update($packageInput);

                    // Update order
                    $order = OrderServiceFactory::mOrderService()->findById($package['order_id']);
                    if (empty($order)) {
                        return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
                    }
                    $orderInput = array();
                    $orderInput['id'] = $order['id'];
                    $status = 4;
                    if ($order['status'] > $status) {
                        $status = $order['status'];
                    }
                    $orderInput['status'] = $status;
                    OrderServiceFactory::mOrderService()->update($orderInput);

                    // Add history
                    $history = [
                        'user_id' => $user['id'],
                        'order_id' => $order['id'],
                        'type' => 11,
                        'content' => 'Kiện hàng ' . $package['id'] . ' nhập kho Trung Quốc, mã phiếu ' . $create['code']
                    ];
                    OrderServiceFactory::mHistoryService()->create($history);
                }
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function genBagCode($y, $m)
    {
        try {
            $top = OrderServiceFactory::mBagService()->findByTopCode($y, $m);
            if (!empty($top)) {
                $topOrderExp = explode('.', $top);
                $code = 'BAG.' . (string)((int)end($topOrderExp) + 1);
            } else {
                $code = 'BAG.' . $y . $m . '0001';
            }
            return $code;
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function genTqStoreBillCode($y, $m)
    {
        try {
            $top = OrderServiceFactory::mTqReceiptService()->findByTopCode($y, $m);
            if (!empty($top)) {
                $topOrderExp = explode('.', $top);
                $code = 'TQR.' . (string)((int)end($topOrderExp) + 1);
            } else {
                $code = 'TQR.' . $y . $m . '0001';
            }
            return $code;
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function genStoreBillCode($y, $m)
    {
        try {
            $top = OrderServiceFactory::mReceiptService()->findByTopCode($y, $m);
            if (!empty($top)) {
                $topOrderExp = explode('.', $top);
                $code = 'R.' . (string)((int)end($topOrderExp) + 1);
            } else {
                $code = 'R.' . $y . $m . '0001';
            }
            return $code;
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function genBillCode($uId, $uCode)
    {
        try {
            $topOrder = OrderServiceFactory::mBillService()->findByTopCode($uId);
            if (!empty($topOrder)) {
                $topOrderExp = explode('.', $topOrder);
                $code = 'B.' . (string)((int)end($topOrderExp) + 1);
            } else {
                $code = 'B.' . $uCode . '0001';
            }
            return $code;
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function deletetqreceipt(Request $request, $id)
    {
        $input = $request->all();
        $bill = OrderServiceFactory::mBillService()->findById($input['id']);
        if (empty($bill)) {
            return $this->sendError('Error', ['Không tồn tại phiếu xuất!']);
        }
        if ($bill['bill']['status'] == 2) {
            return $this->sendError('Error', ['Không thể xóa phiếu xuất đã xuất kho!']);
        }
        DB::beginTransaction();
        try {
            // Package
            $packages = $bill['bill']['package'];
            foreach ($packages as $package) {
                $packageInput = array(
                    'id' => $package['id'],
                    'bill_id' => null
                );
                OrderServiceFactory::mPackageService()->update($packageInput);
            }
            $billInput = array(
                'id' => $input['id'],
                'is_deleted' => 1
            );
            OrderServiceFactory::mBillService()->update($billInput);
            DB::commit();
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billDelete(Request $request)
    {
        $input = $request->all();
        $bill = OrderServiceFactory::mBillService()->findById($input['id']);
        if (empty($bill)) {
            return $this->sendError('Error', ['Không tồn tại phiếu xuất!']);
        }
        if ($bill['bill']['status'] == 2) {
            return $this->sendError('Error', ['Không thể xóa phiếu xuất đã xuất kho!']);
        }
        DB::beginTransaction();
        try {
            // Package
            $packages = $bill['bill']['package'];
            foreach ($packages as $package) {
                $packageInput = array(
                    'id' => $package['id'],
                    'bill_id' => null
                );
                OrderServiceFactory::mPackageService()->update($packageInput);
            }
            $billInput = array(
                'id' => $input['id'],
                'is_deleted' => 1
            );
            OrderServiceFactory::mBillService()->update($billInput);
            DB::commit();
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billDetail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mBillService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function bagDetail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mBagService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function billConfirm(Request $request)
    {
        $input = $request->all();
        $user = $request->user();
        $arrRules = [
            'id' => 'required',
        ];
        $arrMessages = [
            'id.required' => 'Không xác định được phiếu xuất!',
        ];

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Xuất kho không thành công!', $validator->errors()->all());
        }

        DB::beginTransaction();
        try {
            //Bill
            $bill = OrderServiceFactory::mBillService()->findById($input['id']);
            if (empty($bill)) {
                return $this->sendError('Xuất kho không thành công!', ['Phiếu xuất không tồn tại!']);
            }

            $billinput = array();
            $billinput['id'] = $input['id'];
            $billinput['status'] = 2;
            $billinput['tien_can'] = 0;
            $billinput['tien_dong_go'] = 0;
            $billinput['tien_chong_soc'] = 0;
            $billinput['cuoc_van_phat_sinh'] = 0;
            $billinput['tien_thanh_ly'] = 0;

            $packages = $bill['bill']['package'];
            foreach ($packages as $package) {
                $billinput['tien_can'] = $billinput['tien_can'] + $package['tien_can_tt'];
                $billinput['tien_dong_go'] = $billinput['tien_dong_go'] + $package['tien_dong_go'];
                $billinput['tien_chong_soc'] = $billinput['tien_chong_soc'] + $package['tien_chong_soc_tt'];
                $billinput['cuoc_van_phat_sinh'] = $billinput['cuoc_van_phat_sinh'] + $package['phi_van_phat_sinh'];
                $billinput['tien_thanh_ly'] = $billinput['tien_thanh_ly'] + $package['tien_thanh_ly'];
            }

            $tongThanhLy = $billinput['tien_can'] + $billinput['tien_dong_go'] + $billinput['tien_chong_soc'] + $billinput['cuoc_van_phat_sinh'] + $billinput['tien_thanh_ly'];
            if ($tongThanhLy > $bill['bill']['user']['debt']) {
                return $this->sendError('Xuất kho không thành công!', ['Dư nợ không đủ để thực hiện thanh lý!']);
            }

            $update = OrderServiceFactory::mBillService()->update($billinput);
            if (!empty($update)) {
                // Total_transaction
                $total_transaction = 0;

                // Thanh ly package
                foreach ($packages as $package) {
                    $total_transaction += (int)$package['ship_khach_tt'] + (int)$package['tien_can_tt'] + (int)$package['tien_dong_go'] + (int)$package['tien_chong_soc_tt'] + (int)$package['phi_van_phat_sinh'];
                    $packageInput = array(
                        'id' => $package['id'],
                        'status' => 7
                    );
                    $pkupdate = OrderServiceFactory::mPackageService()->update($packageInput);
                    if (!empty($pkupdate) && ($pkupdate['is_main'] == 1)) {
                        //Thanh ly order
                        $order = OrderServiceFactory::mOrderService()->findById($pkupdate['order_id']);
                        $total_transaction += (int)$order['tien_hang'] + (int)$order['phi_dat_hang_tt'] + (int)$order['phi_kiem_dem_tt'];

                        $orderInput = array();
                        $orderInput['id'] = $pkupdate['order_id'];
                        $orderInput['status'] = 5;
                        OrderServiceFactory::mOrderService()->update($orderInput);

                        // add history
                        $history = [
                            'user_id' => $user['id'],
                            'order_id' => $pkupdate['order_id'],
                            'type' => 9,
                            'content' => 'Xuất kho thanh lý, mã phiếu ' . $update['id']
                        ];
                        OrderServiceFactory::mHistoryService()->create($history);
                    }
                }

                // Transaction
                $transaction = [
                    'user_id' => $update['user_id'],
                    'type' => 6,
                    'code' => 'XKTL.' . $update['id'],
                    'value' => $tongThanhLy,
                    'debt' => $bill['bill']['user']['debt'] - $tongThanhLy,
                    'content' => 'Xuất kho thanh lý, mã phiếu ' . $update['id']
                ];

                CommonServiceFactory::mTransactionService()->create($transaction);

                // Check vip
                if ($total_transaction > 0) {
                    $old_total = $bill['bill']['user']['total_transaction'];
                    $new_total = $old_total + ($total_transaction / 1000000);
                    $vipId = $bill['bill']['user']['vip'];
                    $vips = CommonServiceFactory::mVipService()->getAll();
                    foreach ($vips as $vip) {
                        if ($vip->min_tot_tran <= $new_total) {
                            $vipId = $vip->id;
                            break;
                        }
                    }

                    $userInput = [
                        'id' => $update['user_id'],
                        'total_transaction' => $new_total,
                        'vip' => $vipId
                    ];

                    CommonServiceFactory::mUserService()->update($userInput);
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
