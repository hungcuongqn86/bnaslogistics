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

    private function generateRandomString($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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

            $bankAccountId = $input['vqrSelBank']['account']['id'];
            $bankAccount = CommonServiceFactory::mBankAccountService()->findById($bankAccountId);
            if(empty($bankAccount)){
                return $this->sendError('Error', ['Bank Account Không tồn tại!']);
            }

            // Tao vietqr
            $vqr_client_id = "de3be5b0-f790-4e86-96d6-8ba753b8a831";
            $vqr_api_key = "b67705a2-8713-4ad6-9bc0-10d5363c0fcf";

            // Post
            $url = "https://api.vietqr.io/v2/generate";
            $accountNo = $input['vqrSelBank']['account']['account_number'];
            $accountName = $input['vqrSelBank']['account']['account_name'];
            $acqId = $input['vqrSelBank']['bin'];
            $amount = $input['n_value'];
            $addInfo = self::generateRandomString(6);

            $postVar = "accountNo=";
            $postVar .= $accountNo;
            $postVar .= "&accountName=";
            $postVar .= $accountName;
            $postVar .= "&acqId=";
            $postVar .= $acqId;
            $postVar .= "&amount=";
            $postVar .= $amount;
            $postVar .= "&addInfo=";
            $postVar .= $addInfo;
            $postVar .= "&format=text";
            $postVar .= "&template=compact";

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postVar);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'x-client-id: ' . $vqr_client_id,
                'x-api-key: ' . $vqr_api_key
            ));
            $data = curl_exec($curl);
            curl_close($curl);
            if (empty($data)) {
                return $this->sendError('Error', ['Không tạo được QR Code!']);
            }
            $data = json_decode($data, true);
            if (empty($data['data'])) {
                return $this->sendError('Error', ['Không tạo được QR Code!']);
            }
            $user = Auth::user();

            $traReq = [
                'user_id' => $user['id'],
                'code' => $addInfo,
                'value' => $amount,
                'vqr_bank_code' => $input['vqrSelBank']['code'],
                'vqr_bank_name' => $input['vqrSelBank']['name'],
                'vqr_bank_bin' => $acqId,
                'vqr_bank_qr_code' => $data['data']['qrCode'],
                'account_name' => $accountName,
                'account_number' => $accountNo,
                'sender' => $bankAccount['bank_account']['sender'],
                'sms_temp' => $bankAccount['bank_account']['sms_temp']
            ];

            $create = CommonServiceFactory::mBankAccountService()->transaction_requests_create($traReq);
            if(empty($create)){
                return $this->sendError('Error', ['Không lưu được QR Code!']);
            }
            $create['vqr_bank_qr_data_url'] = $data['data']['qrDataURL'];
            return $this->sendResponse($create, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
