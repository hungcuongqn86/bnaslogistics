<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends CommonController
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
            $input['type'] = $user['type'];
            return $this->sendResponse(CommonServiceFactory::mBankAccountService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mBankAccountService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'name' => 'required',
                'account_number' => 'required',
                'account_name' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'Phải nhập tên ngân hàng!',
                'account_number.required' => 'Phải nhập số tài khoản!',
                'account_name.required' => 'Phải nhập chủ tài khoản!'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if ((!$user->hasRole('admin')) && (!$user->hasRole('administrator'))) {
                return $this->sendError('Error', ['Not Permission!']);
            }

            $bankAccount = CommonServiceFactory::mBankAccountService()->findById($id);
            if(empty($bankAccount)){
                return $this->sendError('Error', ['Bank Account Không tồn tại!']);
            }

            return $this->sendResponse(CommonServiceFactory::mBankAccountService()->update($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function recharge(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'n_value' => 'required',
                'vqrSelBank' => 'required'
            ];
            $arrMessages = [
                'n_value.required' => 'Phải nhập số tiền cần nạp!',
                'vqrSelBank.required' => 'Phải chọn ngân hàng nạp!'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            if(empty($input['vqrSelBank']['account'])){
                return $this->sendError('Error', ['Không có tài khoản ngân hàng!']);
            }

            return $this->sendResponse($input, 'Successfully.');

            $bankAccount = CommonServiceFactory::mBankAccountService()->findById($id);
            if(empty($bankAccount)){
                return $this->sendError('Error', ['Bank Account Không tồn tại!']);
            }

            return $this->sendResponse(CommonServiceFactory::mBankAccountService()->update($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
