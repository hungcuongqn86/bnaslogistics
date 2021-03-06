<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
use App\Exports\OrderExport;
use Maatwebsite\Excel\Facades\Excel;

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

    public function create(Request $request)
    {
        $input = $request->all();
        $arrRules = [
            'shop_id' => 'required',
            'cart_ids' => 'required'
        ];
        $arrMessages = [
            'shop_id.required' => 'Không xác định được shop!',
            'cart_ids.required' => 'Không có sản phẩm!'
        ];

        $user = $request->user();
        $input['user_id'] = $user['id'];
        if (isset($user['hander'])) {
            $input['hander'] = $user['hander'];
        }

        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Kết đơn không thành công!', $validator->errors()->all());
        }

        $arrCartId = explode(',', $input['cart_ids']);
        $carts = CartServiceFactory::mCartService()->findByIds($arrCartId);
        foreach ($carts as $cart) {
            if (!empty($cart['order_id'])) {
                return $this->sendError('Kết đơn không thành công!', ['Xin vui lòng thực hiện lại!']);
            }
        }

        try {
            $input['status'] = 2;
            $create = OrderServiceFactory::mOrderService()->create($input);
            if (!empty($create)) {
                foreach ($arrCartId as $id) {
                    $cartInput = array(
                        'id' => $id,
                        'order_id' => $create['id'],
                        'status' => 2
                    );
                    CartServiceFactory::mCartService()->update($cartInput);
                }
                // History
                $history = [
                    'user_id' => $user['id'],
                    'order_id' => $create['id'],
                    'type' => 1
                ];
                OrderServiceFactory::mHistoryService()->create($history);
                //Package
                $package = [
                    'order_id' => $create['id']
                ];
                OrderServiceFactory::mPackageService()->create($package);
            }
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
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
