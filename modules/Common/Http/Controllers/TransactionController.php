<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Common\Services\CommonServiceFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TransactionController extends CommonController
{
    public function index()
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if ($user->hasRole('custumer')) {
                $input['user_id'] = $user['id'];
            }
            return $this->sendResponse(CommonServiceFactory::mTransactionService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function types()
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mTransactionService()->types(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function bankMessGetAll(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if ((!$user->hasRole('admin')) && (!$user->hasRole('administrator'))) {
                return $this->sendError('Error', ['Not Permission!']);
            }
            return $this->sendResponse(CommonServiceFactory::mBankMessService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function createbybankmess(Request $request)
    {
        $input = $request->all();
        DB::beginTransaction();
        try {
            $arrRules = [
                'msg_id' => 'required',
                'address' => 'required',
                'body' => 'required',
                'date' => 'required',
            ];
            $arrMessages = [
                'msg_id.required' => 'msg_id.required',
                'address.required' => 'address.required',
                'body.required' => 'body.required',
                'date.required' => 'date.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                DB::rollBack();
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if ((!$user->hasRole('admin')) && (!$user->hasRole('administrator'))) {
                DB::rollBack();
                return $this->sendError('Error', ['Not Permission!']);
            }

            $create = CommonServiceFactory::mBankMessService()->create($input);
            if (!empty($create)) {
                // Lay transaction_requests address
                $body = $create->body;
                $transaction_req = CommonServiceFactory::mBankAccountService()->transactionRequestsAvailable($create->address, $body);
                if (!empty($transaction_req) && !empty($transaction_req['sms_temp'])) {
                    $temp = $transaction_req['sms_temp'];
                    preg_match_all($temp, $body, $matches);
                    if (sizeof($matches) == 2) {
                        if (sizeof($matches[1]) == 1) {
                            $val = $matches[1][0];
                            $val = str_replace(',', '', $val);
                            $val = str_replace('.', '', $val);
                            if ($val == $transaction_req['value']) {
                                $trInput['type'] = 1;
                                $trInput['code'] = 'sms'.$create->id.'req'.$transaction_req['id'];
                                $trInput['content'] = 'sms'.$create->id.'req'.$transaction_req['id'];
                                $trInput['value'] = $val;

                                $trInput['created_by'] = $user['id'];

                                $trInput['user_id'] = $transaction_req['user_id'];
                                $duNo = CommonServiceFactory::mTransactionService()->debt($transaction_req['user_id']);
                                $duNo = $duNo + ($val*1);
                                $trInput['debt'] = $duNo;

                                $trInput['bank_account'] = $transaction_req['bank_account'];
                                $duNoBank = CommonServiceFactory::mTransactionService()->bankdebt($transaction_req['bank_account']);
                                $duNoBank = $duNoBank + ($val*1);
                                $trInput['bank_debt'] = $duNoBank;

                                $create = CommonServiceFactory::mTransactionService()->create($trInput);
                            }
                        }
                    }
                }
            }
            DB::commit();
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'type' => 'required',
                'code' => 'required',
                'value' => 'required',
                'bank_account' => 'required'
            ];
            $arrMessages = [
                'type.required' => 'type.required',
                'code.required' => 'code.required',
                'bank_account.required' => 'bank_account.required',
                'value.required' => 'value.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if ((!$user->hasRole('admin')) && (!$user->hasRole('administrator'))) {
                return $this->sendError('Error', ['Not Permission!']);
            }

            $input['created_by'] = $user['id'];
            // Du no
            $duNo = 0;
            if (!empty($input['user_id'])) {
                $duNo = CommonServiceFactory::mTransactionService()->debt($input['user_id']);
            }

            $duNoBank = CommonServiceFactory::mTransactionService()->bankdebt($input['bank_account']);

            $types = CommonServiceFactory::mTransactionService()->types();
            foreach ($types as $type) {
                if ($type->id == $input['type']) {
                    $duNo = $duNo + ($input['value'] * $type->value);
                    $duNoBank = $duNoBank + ($input['value'] * $type->value);
                }
            }
            $input['debt'] = $duNo;
            $input['bank_debt'] = $duNoBank;
            $create = CommonServiceFactory::mTransactionService()->create($input);
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
                'user_id' => 'required',
                'type' => 'required',
                'code' => 'required',
                'value' => 'required'
            ];
            $arrMessages = [
                'user_id.required' => 'name.required',
                'type.required' => 'email.required',
                'code.required' => 'email.required',
                'value.required' => 'email.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $update = CommonServiceFactory::mTransactionService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $owners = CommonServiceFactory::mTransactionService()->findByIds($input);
        $deleteData = array();
        $errData = array();
        foreach ($input as $id) {
            $check = false;
            foreach ($owners as $owner) {
                if ($id == $owner['id']) {
                    $check = true;
                    $owner['is_deleted'] = 1;
                    $deleteData[] = $owner;
                }
            }
            if (!$check) {
                $errData[] = 'Transaction Id ' . $id . ' NotExist';
            }
        }

        if (!empty($errData)) {
            return $this->sendError('Error', $errData);
        }

        try {
            CommonServiceFactory::mTransactionService()->delete($input);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function withdrawalRequest(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'value' => 'required'
            ];
            $arrMessages = [
                'value.required' => 'Phải nhập số tiền muốn rút!'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if (!$user->hasRole('custumer')) {
                return $this->sendError('Error', ['Not Permission!']);
            }

            $input['user_id'] = $user['id'];
            $input['status'] = 1;
            $input['created_by'] = $user['id'];
            $create = CommonServiceFactory::mWithdrawalRequestService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function updatewithdrawalrequest(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'id' => 'required',
                'value' => 'required'
            ];
            $arrMessages = [
                'id.required' => 'id.required',
                'value.required' => 'value.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if (!$user->hasRole('custumer')) {
                return $this->sendError('Error', ['Not Permission!']);
            }

            $create = CommonServiceFactory::mWithdrawalRequestService()->update($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function approvewithdrawalrequest(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                return $this->sendError('Error', ['Not Permission!']);
            }

            $arrRules = [
                'id' => 'required',
                'status' => 'required'
            ];
            $arrMessages = [
                'id.required' => 'id.required',
                'status.required' => 'status.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            if ($input['status'] == '3') {
                if (empty($input['feedback'])) {
                    return $this->sendError('Error', ['Phải nhập lý do từ chối!']);
                }
            }

            if ($input['status'] == '2') {
                if (empty($input['bank_account'])) {
                    return $this->sendError('Error', ['Phải chọn ngân hàng!']);
                }

                $withdrawalrequest = CommonServiceFactory::mWithdrawalRequestService()->findById($input['id']);
                if (empty($withdrawalrequest)) {
                    return $this->sendError('Error', ['Không tìm thấy yêu cầu rút tiền!']);
                }

                // Du no
                $duNo = 0;
                if (!empty($withdrawalrequest['user_id'])) {
                    $duNo = CommonServiceFactory::mTransactionService()->debt($withdrawalrequest['user_id']);
                }
                $duNoBank = CommonServiceFactory::mTransactionService()->bankdebt($input['bank_account']);

                if ($duNo < $withdrawalrequest['value']) {
                    return $this->sendError('Error', ['Dư nợ khách hàng không đủ để rut tiền!']);
                }

                if ($duNoBank < $withdrawalrequest['value']) {
                    return $this->sendError('Error', ['Dư nợ tài khoản không đủ để chuyển tiền cho khách!']);
                }

                $duNo = $duNo - $input['value'];
                $duNoBank = $duNoBank - $input['value'];

                $transactionInput = [
                    'user_id' => $withdrawalrequest['user_id'],
                    'type' => 2,
                    'code' => 'Request' . $input['id'],
                    'value' => $withdrawalrequest['value'],
                    'debt' => $duNo,
                    'content' => 'Duyệt yêu cầu rút tiền #' . $input['id'],
                    'bank_debt' => $duNoBank,
                    'bank_account' => $input['bank_account']
                ];

                $create = CommonServiceFactory::mTransactionService()->create($transactionInput);
            }
            $item = CommonServiceFactory::mWithdrawalRequestService()->update($input);
            return $this->sendResponse($item, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function withdrawalRequests(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if ($user->hasRole('custumer')) {
                $input['user_id'] = $user['id'];
            }
            return $this->sendResponse(CommonServiceFactory::mWithdrawalRequestService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function withdrawalrequestsstatus()
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mWithdrawalRequestService()->status(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function withdrawalrequestcount(Request $request)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mWithdrawalRequestService()->countByStatus(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
