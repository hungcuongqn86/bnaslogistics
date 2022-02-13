<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;

class ServiceFeeController extends CommonController
{
    public function index(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mServiceFeeService()->search($input), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mServiceFeeService()->findById($id), 'Successfully.');
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
                'val' => 'required',
            ];
            $arrMessages = [
                'title.required' => 'Phải nhập Dịch Vụ!',
                'min_tot_tran.required' => 'Phải nhập Tiền Hàng Từ!',
                'val.required' => 'Phải nhập Tính Phí!',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $create = CommonServiceFactory::mServiceFeeService()->create($input);
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
                'val' => 'required',
            ];
            $arrMessages = [
                'title.required' => 'Phải nhập Dịch Vụ!',
                'min_tot_tran.required' => 'Phải nhập Tiền Hàng Từ!',
                'val.required' => 'Phải nhập Tính Phí!',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $serviceFee = CommonServiceFactory::mServiceFeeService()->findById($id);
            if (empty($serviceFee)) {
                return $this->sendError('Error', ['Không tồn tại dịch vụ!']);
            }

            $update = CommonServiceFactory::mServiceFeeService()->update($input);
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
            $serviceFee = CommonServiceFactory::mServiceFeeService()->findById($id);
            if (empty($serviceFee)) {
                return $this->sendError('Error', ['Không tồn tại dịch vụ!']);
            }
            return $this->sendResponse(CommonServiceFactory::mServiceFeeService()->delete([$id]), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
