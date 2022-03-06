<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Http\Controllers\CommonController;

class HistoryController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(OrderServiceFactory::mHistoryService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(OrderServiceFactory::mHistoryService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function types()
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
            'order_id' => 'required',
            'type' => 'required',
            'content' => 'required'
        ];
        $arrMessages = [
            'order_id.required' => 'Không xác định được đơn hàng!',
            'type.required' => 'Chưa chọn công việc thực hiện!',
            'content.required' => 'Chưa nhập nội dung thực hiện!'
        ];

        $user = $request->user();
        $input['user_id'] = $user['id'];
        $validator = Validator::make($input, $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        // Order
        $order = OrderServiceFactory::mOrderService()->findById($input['order_id']);
        if (empty($order)) {
            return $this->sendError('Error', ['Đơn hàng không tồn tại!']);
        }

        $userId = $order['user_id'];
        if ($user['type'] == 1) {
            if($user['id'] != $userId){
                return $this->sendError('Error', ['Không có quyền truy cập!'], 403);
            }
        }

        DB::beginTransaction();
        try {
            $debt = CommonServiceFactory::mTransactionService()->debt(['user_id' => $userId]);
            $create = OrderServiceFactory::mHistoryService()->create($input);
            if (!empty($create)) {
                // Update order status
                $orderInput = array();
                $orderInput['id'] = $order['id'];

                if ($input['type'] == 4) {
                    $orderInput['status'] = 4;
                }
                if ($input['type'] == 6) {
                    $orderInput['status'] = 6;
                }
                if ($input['type'] == 7) {
                    $tiencoc = $order['dat_coc'];
                    if (!empty($tiencoc) && $tiencoc > 0) {
                        // Hoan tien
                        $orderInput['dat_coc_content'] = $input['content'];
                        $orderInput['dat_coc'] = 0;

                        // Transaction
                        $transaction = [
                            'user_id' => $userId,
                            'type' => 5,
                            'code' => $order['id'] . '.H' . $create['id'],
                            'value' => $tiencoc,
                            'debt' => $debt + $tiencoc,
                            'content' => $input['content']
                        ];
                        CommonServiceFactory::mTransactionService()->create($transaction);
                    }
                }
                OrderServiceFactory::mOrderService()->update($orderInput);
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
