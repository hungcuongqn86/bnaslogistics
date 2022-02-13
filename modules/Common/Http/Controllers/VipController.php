<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;

class VipController extends CommonController
{
    public function index(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mVipService()->search($input), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mVipService()->findById($id), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'title' => 'required',
                'min_tot_tran' => 'required',
                'ck_dv' => 'required',
                'ck_vc' => 'required',
                'deposit' => 'required',
            ];
            $arrMessages = [
                'title.required' => 'Phải nhập CẤP ĐỘ!',
                'min_tot_tran.required' => 'Phải nhập TỔNG GIAO DỊCH TỪ!',
                'ck_dv.required' => 'Phải nhập CHIẾT KHẤU DỊCH VỤ!',
                'ck_vc.required' => 'Phải nhập CHIẾT KHẤU VẬN CHUYỂN!',
                'deposit.required' => 'Phải nhập ĐẶT CỌC TIỀN HÀNG!',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $create = CommonServiceFactory::mVipService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'title' => 'required',
                'min_tot_tran' => 'required',
                'ck_dv' => 'required',
                'ck_vc' => 'required',
                'deposit' => 'required',
            ];
            $arrMessages = [
                'title.required' => 'Phải nhập CẤP ĐỘ!',
                'min_tot_tran.required' => 'Phải nhập TỔNG GIAO DỊCH TỪ!',
                'ck_dv.required' => 'Phải nhập CHIẾT KHẤU DỊCH VỤ!',
                'ck_vc.required' => 'Phải nhập CHIẾT KHẤU VẬN CHUYỂN!',
                'deposit.required' => 'Phải nhập ĐẶT CỌC TIỀN HÀNG!',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $vip = CommonServiceFactory::mVipService()->findById($id);
            if (empty($vip)) {
                return $this->sendError('Error', ['Không tồn tại VIP!']);
            }

            $update = CommonServiceFactory::mVipService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $vip = CommonServiceFactory::mVipService()->findById($id);
            if (empty($vip)) {
                return $this->sendError('Error', ['Không tồn tại VIP!']);
            }
            return $this->sendResponse(CommonServiceFactory::mVipService()->delete($id), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
