<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;

class WarehouseController extends CommonController
{
    public function index(Request $request)
    {
        return $this->sendResponse([], 'Successfully.');
    }

    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(CommonServiceFactory::mWarehouseService()->search($input), 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mWarehouseService()->findById($id), 'Successfully.');
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

            $create = CommonServiceFactory::mWarehouseService()->create($input);
            return $this->sendResponse($create, 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function update(Request $request)
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
            $update = CommonServiceFactory::mWarehouseService()->update($input);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\PDOException $e) {
            return $this->sendError('PDOError', $e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
