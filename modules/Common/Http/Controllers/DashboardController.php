<?php

namespace Modules\Common\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Common\Entities\Cart;
use Modules\Common\Entities\Order;
use App\User;
use Modules\Common\Entities\Complain;

class DashboardController extends CommonController
{
    public function newlinks(Request $request)
    {
        $input = $request->all();
        try {
            $date = Carbon::now()->subDays(10);
            $count = Cart::whereDate('created_at', '>=', $date->toDateString())->count();
            return $this->sendResponse(['newlinks' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function neworders(Request $request)
    {
        $input = $request->all();
        try {
            $date = Carbon::now()->subDays(10);
            $count = Order::whereDate('created_at', '>=', $date->toDateString())->count();
            return $this->sendResponse(['neworders' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function newusers(Request $request)
    {
        $input = $request->all();
        try {
            $date = Carbon::now()->subDays(10);
            $count = User::whereDate('created_at', '>=', $date->toDateString())->count();
            return $this->sendResponse(['newusers' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function newcomplains(Request $request)
    {
        $input = $request->all();
        try {
            $date = Carbon::now()->subDays(10);
            $count = Complain::whereDate('created_at', '>=', $date->toDateString())->count();
            return $this->sendResponse(['newcomplains' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
