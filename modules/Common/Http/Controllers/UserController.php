<?php

namespace Modules\Common\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;

class UserController extends CommonController
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
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }
            return $this->sendResponse(CommonServiceFactory::mUserService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function handleGetAll()
    {
        try {
            return $this->sendResponse(CommonServiceFactory::mUserService()->handleGetAll(), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function custumers(Request $request)
    {
        $input = $request->all();
        try {
            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }
            if ($user->hasRole('employees')) {
                $input['hander'] = $user['id'];
            }
            return $this->sendResponse(CommonServiceFactory::mUserService()->custumer($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            // $login = session()->all();
            // dd($login);
            return $this->sendResponse(CommonServiceFactory::mUserService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $arrRules = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'c_password' => 'required|same:password',
                'phone_number' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'name.required',
                'email.required' => 'email.required',
                'email.email' => 'email.email',
                'email.unique' => 'email.unique',
                'password.required' => 'password.required',
                'c_password.required' => 'c_password.required',
                'c_password.same' => 'c_password.same',
                'phone_number.required' => 'phone_number.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }

            $input['password'] = bcrypt($input['password']);
            $input['rate'] = 0;
            $input['active'] = 1;
            $input['type'] = 0;
            $create = CommonServiceFactory::mUserService()->create($input);
            if ($create) {
                $role = CommonServiceFactory::mRoleService()->findById($input['role_id']);
                if ($role) {
                    $create->assignRole($role['role']['name']);
                }
            }
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
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $input['id'],
                'c_password' => 'same:password',
                'partner_id' => 'required',
                'phone_number' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'name.required',
                'email.required' => 'email.required',
                'email.email' => 'email.email',
                'email.unique' => 'email.unique',
                'c_password.same' => 'c_password.same',
                'partner_id.required' => 'partner_id.required',
                'phone_number.required' => 'phone_number.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            if (!empty($input['password'])) {
                $input['password'] = bcrypt($input['password']);
            }

            $user = Auth::user();
            if (!empty($user['partner_id']) && $user['partner_id'] > 0) {
                $input['partner_id'] = $user['partner_id'];
            }

            $update = CommonServiceFactory::mUserService()->update($input);
            if ($update) {
                if (isset($input['role_id'])) {
                    $role = CommonServiceFactory::mRoleService()->findById($input['role_id']);
                    if ($role) {
                        $roles = $update->getRoleNames();
                        foreach ($roles as $item) {
                            $update->removeRole($item);
                        }
                        $update->assignRole($role['role']['name']);
                    }
                }
            }
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function active(Request $request, $id)
    {
        try {
            if (!Auth::user()->hasRole('administrator')) {
                return $this->sendError('Error', ['Bạn không có quyền thực hiện chức năng này!']);
            }
            $userData = CommonServiceFactory::mUserService()->findById($id);
            if (empty($userData)) {
                return $this->sendError('Error', ['Không tồn tại user trong hệ thống!']);
            }
            $userInput['id'] = $id;
            $userInput['active'] = true;
            $userInput['activation_token'] = "";
            $update = CommonServiceFactory::mUserService()->update($userInput);
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function passwordReset(Request $request, $id)
    {
        try {
            if (!Auth::user()->hasRole('administrator')) {
                return $this->sendError('Error', ['Bạn không có quyền thực hiện chức năng này!']);
            }

            $arrRules = [
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    'min:6',             // must be at least 10 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ]
            ];
            $arrMessages = [
                'password.required' => 'Chưa nhập mật khẩu mới!',
                'password.string' => 'Mật khẩu phải là chuỗi ký tự!',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự!',
                'password.regex' => 'Mật khẩu phải có chứa 1 ký tự chữ in thường, 1 ký tự chữ in hoa, 1 ký tự số, 1 ký tự đặc biệt!',
                'password.confirmed' => 'Xác nhận mật khẩu không đúng!'
            ];

            $validator = Validator::make($request->all(), $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $user = User::where('id', $id)->first();
            if (!$user)
                return $this->sendError('Error.', ['Không tìm thấy tài khoản!']);
            $user->password = bcrypt($request->password);
            $user->save();
            return $this->sendResponse($user, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $owners = CommonServiceFactory::mUserService()->findByIds($input);
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
                $errData[] = 'User Id ' . $id . ' NotExist';
            }
        }

        if (!empty($errData)) {
            return $this->sendError('Error', $errData);
        }

        try {
            CommonServiceFactory::mUserService()->delete($input);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
