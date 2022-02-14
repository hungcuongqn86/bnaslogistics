<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;

class TransportFeeController extends CommonController
{
    public function index(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mTransportFeeService()->search($input), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mTransportFeeService()->findById($id), 'Successfully.');
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
                'key' => 'required',
                'title' => 'required',
                'value' => 'required',
            ];
            $arrMessages = [
                'key.required' => 'ERRORS_MS.BAD_REQUEST',
                'title.required' => 'ERRORS_MS.BAD_REQUEST',
                'value.required' => 'ERRORS_MS.BAD_REQUEST',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $create = CommonServiceFactory::mTransportFeeService()->create($input);
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
                'key' => 'required',
                'title' => 'required',
                'value' => 'required',
            ];
            $arrMessages = [
                'key.required' => 'ERRORS_MS.BAD_REQUEST',
                'title.required' => 'ERRORS_MS.BAD_REQUEST',
                'value.required' => 'ERRORS_MS.BAD_REQUEST',
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $serviceFee = CommonServiceFactory::mTransportFeeService()->findById($id);
            if (empty($serviceFee)) {
                return $this->sendError('Error', ['Không tồn tại dịch vụ!']);
            }

            $update = CommonServiceFactory::mTransportFeeService()->update($input);
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
            $serviceFee = CommonServiceFactory::mTransportFeeService()->findById($id);
            if (empty($serviceFee)) {
                return $this->sendError('Error', ['Không tồn tại dịch vụ!']);
            }
            return $this->sendResponse(CommonServiceFactory::mTransportFeeService()->delete($id), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
