<?php

namespace Modules\Common\Http\Controllers;

use App\Models\PasswordReset;
use App\Notifications\PasswordResetSuccess;
use App\Notifications\ResetPasswordRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends CommonController
{

    /**
     * @SWG\POST(
     *      path="/password/create",
     *      operationId="resetPassword",
     *      tags={"Auth"},
     *      summary="Reset password",
     *      description="Reset password",
     *      @SWG\Parameter(
     *         description="Reset password",
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email", type="string")
     *         )
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request"),
     *       security={
     *           {"api_key_security_example": {}}
     *       }
     *     )
     *
     * Reset password
     */

    public function create(Request $request)
    {
        $arrRules = [
            'email' => 'required|string|email'
        ];
        $arrMessages = [
            'email.required' => 'Chưa nhập email!',
            'email.email' => 'Email không đúng!'
        ];

        $validator = Validator::make($request->all(), $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user)
            return $this->sendError('Error.', ['Không tìm thấy tài khoản đăng ký với Email này!']);

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60)
            ]
        );
        if ($user && $passwordReset)
            $user->notify(
                new ResetPasswordRequest($passwordReset->token)
            );

        return $this->sendResponse(1, 'Successfully.');
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        if (!$passwordReset)
            return $this->sendError('Error.', 'This password reset token is invalid.');
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return $this->sendError('Error.', 'This password reset token is invalid.');
        }
        return $this->sendResponse($passwordReset, 'Successfully.');
    }

    /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        $arrRules = [
            'email' => 'required|string|email',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:6',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'token' => 'required|string'
        ];
        $arrMessages = [
            'email.required' => 'Chưa nhập email!',
            'email.email' => 'Email không đúng!',
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

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset)
            return $this->sendError('Error.', ['Cập nhật mật khẩu không thành công, Phiên làm việc không đúng!.']);
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return $this->sendError('Error.', ['Không tìm thấy tài khoản đăng ký với Email này!']);
        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return $this->sendResponse($user, 'Successfully.');
    }
}
