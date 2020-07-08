<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\ResetPasswordRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\Models\PasswordReset;

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
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return $this->sendError('Error.', 'We can not find a user with that e-mail address.');

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
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset)
            return $this->sendError('Error.', 'This password reset token is invalid.');
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return $this->sendError('Error.', 'We can not find a user with that e-mail address.');
        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return $this->sendResponse($user, 'Successfully.');
    }
}
