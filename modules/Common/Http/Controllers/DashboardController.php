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
    public function newlinks()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $count = Cart::whereDate('created_at', '>=', $date->toDateString())->count();
            return $this->sendResponse(['newlinks' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function neworders()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $count = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0)->count();
            return $this->sendResponse(['neworders' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function newusers()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $count = User::whereDate('created_at', '>=', $date->toDateString())
                ->where('type', '=', 1)
                ->where('active', '=', 1)
                ->where('is_deleted', '=', 0)->count();
            return $this->sendResponse(['newusers' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function newcomplains()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $count = Complain::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0)->count();
            return $this->sendResponse(['newcomplains' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function statisticbytaobao()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $linkCount = Cart::whereDate('created_at', '>=', $date->toDateString())
                ->where('domain', '=', 'taobao')->count();
            $orderCount = Order::whereDate('created_at', '>=', $date->toDateString())
                ->where('is_deleted', '=', 0)
                ->whereHas('Cart', function ($q) {
                    $q->where('domain', '=', 'taobao')->where('is_deleted', '=', 0);
                })
                ->count();
            return $this->sendResponse(['link' => $linkCount, 'order' => $orderCount], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function statisticbytmall()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $linkCount = Cart::whereDate('created_at', '>=', $date->toDateString())
                ->where('domain', '=', 'tmall')->count();
            $orderCount = Order::whereDate('created_at', '>=', $date->toDateString())
                ->where('is_deleted', '=', 0)
                ->whereHas('Cart', function ($q) {
                    $q->where('domain', '=', 'tmall')->where('is_deleted', '=', 0);
                })
                ->count();
            return $this->sendResponse(['link' => $linkCount, 'order' => $orderCount], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function statisticby1688()
    {
        try {
            $date = Carbon::now()->subDays(10);
            $linkCount = Cart::whereDate('created_at', '>=', $date->toDateString())
                ->where('domain', '=', '1688')->count();
            $orderCount = Order::whereDate('created_at', '>=', $date->toDateString())
                ->where('is_deleted', '=', 0)
                ->whereHas('Cart', function ($q) {
                    $q->where('domain', '=', '1688')->where('is_deleted', '=', 0);
                })
                ->count();
            return $this->sendResponse(['link' => $linkCount, 'order' => $orderCount], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
