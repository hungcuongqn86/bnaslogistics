<?php

namespace Modules\Common\Http\Controllers;

use App\Notifications\SignupActivate;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Common\Services\CommonServiceFactory;
use Spatie\Permission\Models\Role;

class PassportController extends CommonController
{
    public $sucessStatus = 200;

    /**
     * @SWG\POST(
     *      path="/login",
     *      operationId="postLogin",
     *      tags={"Auth"},
     *      summary="login get token",
     *      description="Returns token",
     *      @SWG\Parameter(
     *         description="login",
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="password", type="string"),
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
     * Login
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        $credentials['active'] = 1;
        $credentials['is_deleted'] = 0;
        if (!Auth::attempt($credentials))
            return response()->json([
                'status' => false,
                'code' => 401,
                'message' => __('auth.login_failed')
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        $request->session()->invalidate();
        $request->session()->put('login', $user);
        return response()->json([
            'status' => true,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return response()->json([true]);
    }

    /**
     * @SWG\POST(
     *      path="/register",
     *      operationId="postRegister",
     *      tags={"Auth"},
     *      summary="Register user",
     *      description="Returns user",
     *      @SWG\Parameter(
     *         description="user",
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="name", type="string"),
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="password", type="string"),
     *              @SWG\Property(property="c_password", type="string"),
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
     * Register
     */

    public function register(Request $request)
    {
        $arrRules = [
            'name' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ];
        $arrMessages = [
            'name.required' => 'Chưa nhập tên!',
            'phone_number.required' => 'Chưa nhập số điện thoại!',
            'email.required' => 'Chưa nhập email!',
            'email.email' => 'Email không đúng!',
            'email.unique' => 'Email đã được sử dụng!',
            'password.required' => 'Chưa nhập mật khẩu!',
            'c_password.required' => 'Chưa nhập mật khẩu xác nhận!',
            'c_password.same' => 'Mật khẩu xác nhận không chính xác!',
        ];

        $validator = Validator::make($request->all(), $arrRules, $arrMessages);
        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['activation_token'] = str::random(60);
        $input['type'] = 1;
        // Set Vip
        $firstVip = CommonServiceFactory::mVipService()->getFirstVip();
        if (!empty($firstVip)) {
            $input['vip'] = $firstVip['id'];
        }
        $input['code'] = self::genCode();
        $user = User::create($input);
        $user->assignRole('custumer');
        $user->notify(new SignupActivate($user));
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;
        return $this->sendResponse($success, 'Successfully.');
    }

    private function genCode($rec = 0)
    {
        try {
            $code = random_int(100000, 999999);
            $existing = User::where('code', '=', $code)->count();
            if ($existing > 0) {
                if ($rec < 25) {
                    $code = $this->genCode($rec + 1);
                } else {
                    $code = '';
                }
            }
            return $code;
        } catch (\Exception $e) {
            return '';
        }
    }

    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            echo 'Kích hoạt tài khoản thất bại!';
            exit();
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        echo 'Kích hoạt tài khoản thành công!';
        header("Location: https://nguonhang.net/order/");
        exit();
    }

    /*
     * details api
     *
     * @return \Illumiante\Http\Response
     */
    public function getDetails(Request $request)
    {
        $user = Auth::user();
        $rResult = User::with(['Partner', 'roles'])->where('id', '=', $user['id'])->first();
        if (!empty($rResult) && !$request->session()->has('login')) {
            $request->session()->put('login', $user);
        }
        return response()->json(['success' => $rResult], $this->sucessStatus);
    }

    public function getPermissions()
    {
        return response()->json(['success' => self::getNav()], $this->sucessStatus);
    }

    private function getNav()
    {
        $nav = [];
        $user = Auth::user();
        if ($user->hasPermissionTo('dashboard')) {
            $newobj = new \stdClass();
            $newobj->name = 'Bảng tổng hợp';
            $newobj->url = '/dashboard';
            $newobj->icon = 'iconsax isax-house';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('myshipping')) {
            $newobj = new \stdClass();
            $newobj->name = 'Bảng tổng hợp';
            $newobj->url = '/home';
            $newobj->icon = 'iconsax isax-house';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('cart')) {
            $newobj = new \stdClass();
            $newobj->name = 'Giỏ hàng';
            $newobj->url = '/cart';
            $newobj->icon = 'iconsax isax-bag';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('myshipping')) {
            $newobj = new \stdClass();
            $newobj->name = 'Yêu cầu ký gửi';
            $newobj->url = '/shipping/myshipping';
            $newobj->icon = 'iconsax isax-truck';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('myorder')) {
            $newobj = new \stdClass();
            $newobj->name = 'Đơn hàng';
            $newobj->url = '/order/myorder';
            $newobj->icon = 'iconsax isax-receipt-edit';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('mypackage')) {
            /*$newobj = new \stdClass();
            $newobj->name = 'Kiện hàng';
            $newobj->url = '/mypackage';
            $newobj->icon = 'iconsax isax-d-cube-scan';
            $nav[] = $newobj;*/
        }

        if ($user->hasPermissionTo('wallet')) {
            $newobj = new \stdClass();
            $newobj->name = 'Ví điện tử';
            $newobj->url = '/wallet';
            $newobj->icon = 'iconsax isax-empty-wallet';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('order')) {
            $newobj = new \stdClass();
            $newobj->name = 'Tài chính Việt Nam';
            $newobj->url = '/mcustumer';
            $newobj->icon = 'iconsax isax-empty-wallet';
            $children = [];

            $newchildren = new \stdClass();
            $newchildren->name = 'Tài khoản khách';
            $newchildren->url = '/mcustumer/custumer';
            $children[] = $newchildren;

            if ($user->hasPermissionTo('mcustumer')) {
                $newchildren = new \stdClass();
                $newchildren->name = 'Yêu cầu rút tiền';
                $newchildren->url = '/mcustumer/withdrawal';
                $children[] = $newchildren;

                $newchildren = new \stdClass();
                $newchildren->name = 'Chi nội bộ';
                $newchildren->url = '/mcustumer/internal';
                $children[] = $newchildren;
            }

            $newobj->children = $children;
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('order')) {
            $newobj = new \stdClass();
            $newobj->name = 'Đơn hàng';
            $newobj->url = '/order/list';
            $newobj->icon = 'iconsax isax-receipt-edit';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('shipping')) {
            $newobj = new \stdClass();
            $newobj->name = 'Yêu cầu ký gửi';
            $newobj->url = '/shipping/list';
            $newobj->icon = 'iconsax isax-truck';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('complain')) {
            // Khieu nai
            $newobj = new \stdClass();
            $newobj->name = 'Khiếu nại';
            $newobj->url = '/complain';
            $newobj->icon = 'iconsax isax-dislike';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('package')) {
            // Kien hang
            $newobj = new \stdClass();
            $newobj->name = 'Kiện hàng';
            $newobj->url = '/package';
            $newobj->icon = 'iconsax isax-d-cube-scan';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('warehouse')) {
            // Kho
            $newobj = new \stdClass();
            $newobj->name = 'Kho Việt';
            $newobj->url = '/warehouse';
            $newobj->icon = 'iconsax isax-box';
            $children = [];

            $newchildren = new \stdClass();
            $newchildren->name = 'Chờ xuất';
            $newchildren->url = '/warehouse/wait';
            $children[] = $newchildren;

            $newchildren = new \stdClass();
            $newchildren->name = 'Phiếu xuất';
            $newchildren->url = '/warehouse/bill';
            $children[] = $newchildren;

            $newobj->children = $children;
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('muser')) {
            $newobj = new \stdClass();
            $newobj->name = 'Người dùng';
            $newobj->url = '/muser/user';
            $newobj->icon = 'iconsax isax-user';
            $nav[] = $newobj;
        }

        if ($user->hasPermissionTo('setting')) {
            $newobj = new \stdClass();
            $newobj->name = 'Cài đặt';
            $newobj->url = '/setting';
            $newobj->icon = 'iconsax isax-setting-2';
            $nav[] = $newobj;
        }
        return $nav;
    }

    public function setPermissions()
    {
        /*$user = User::where('id', 11)->first();
        $user->assignRole('custumer');*/

        echo 1;
        exit;
        // Permissions
        /*Permission::create(['name' => 'dashboard']);
        Permission::create(['name' => 'mcustumer']);
        Permission::create(['name' => 'cart']);
        Permission::create(['name' => 'order']);
        Permission::create(['name' => 'myorder']);
        Permission::create(['name' => 'package']);
        Permission::create(['name' => 'mypackage']);
        Permission::create(['name' => 'complain']);
        Permission::create(['name' => 'mycomplain']);
        Permission::create(['name' => 'warehouse']);
        Permission::create(['name' => 'shipping']);
        Permission::create(['name' => 'myshipping']);
        Permission::create(['name' => 'wallet']);
        Permission::create(['name' => 'muser']);
        Permission::create(['name' => 'account']);
        Permission::create(['name' => 'setting']);*/

        // Role
        // administrator
        $role = Role::findByName('administrator');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('mcustumer');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');
        $role->givePermissionTo('complain');
        $role->givePermissionTo('warehouse');
        $role->givePermissionTo('shipping');
        $role->givePermissionTo('muser');
        $role->givePermissionTo('account');
        $role->givePermissionTo('setting');

        // quản lý
        $role = Role::findByName('admin');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('mcustumer');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');
        $role->givePermissionTo('complain');
        $role->givePermissionTo('warehouse');
        $role->givePermissionTo('shipping');
        $role->givePermissionTo('account');
        $role->givePermissionTo('setting');

        // chuyên viên
        $role = Role::findByName('employees');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');
        $role->givePermissionTo('complain');
        $role->givePermissionTo('account');

        // kho
        $role = Role::findByName('stocker');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');
        $role->givePermissionTo('warehouse');
        $role->givePermissionTo('account');

        // owner
        $role = Role::findByName('custumer');
        $role->givePermissionTo('cart');
        $role->givePermissionTo('myorder');
        $role->givePermissionTo('mypackage');
        $role->givePermissionTo('wallet');
        $role->givePermissionTo('mycomplain');
        $role->givePermissionTo('myshipping');
        $role->givePermissionTo('account');
        echo 1;
        exit;
    }
}
