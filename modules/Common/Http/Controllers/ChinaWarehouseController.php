<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;

class ChinaWarehouseController extends CommonController
{
    public function index(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mChinaWarehouseService()->search($input), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mChinaWarehouseService()->findById($id), 'Successfully.');
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
                'name' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'Phải nhập TÊN KHO!'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $create = CommonServiceFactory::mChinaWarehouseService()->create($input);
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
                'name' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'Phải nhập TÊN KHO!'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $cnwh = CommonServiceFactory::mChinaWarehouseService()->findById($id);
            if (empty($cnwh)) {
                return $this->sendError('Error', ['Không tồn tại KHO TRUNG QUỐC!']);
            }

            $update = CommonServiceFactory::mChinaWarehouseService()->update($input);
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
            $cnwh = CommonServiceFactory::mChinaWarehouseService()->findById($id);
            if (empty($cnwh)) {
                return $this->sendError('Error', ['Không tồn tại KHO TRUNG QUỐC!']);
            }
            return $this->sendResponse(CommonServiceFactory::mChinaWarehouseService()->delete($id), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
