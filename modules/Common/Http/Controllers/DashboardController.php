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
use Illuminate\Support\Facades\Auth;

class DashboardController extends CommonController
{
    public function newlinks(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $query = Cart::whereDate('created_at', '>=', $date->toDateString());

            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                $userId = $user['id'];
                $query->whereHas('User', function ($q) use ($userId) {
                    $q->where('hander', '=', $userId)->where('is_deleted', '=', 0);
                });
            }

            $count = $query->count();
            return $this->sendResponse(['newlinks' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function neworders(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $query = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0);

            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                $userId = $user['id'];
                $query->where('hander', '=', $userId);
            }

            $count = $query->count();
            return $this->sendResponse(['neworders' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function newusers(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $query = User::whereDate('created_at', '>=', $date->toDateString())->where('type', '=', 1)->where('active', '=', 1)->where('is_deleted', '=', 0);
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                $userId = $user['id'];
                $query->where('hander', '=', $userId);
            }

            $count = $query->count();
            return $this->sendResponse(['newusers' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function newcomplains(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $query = Complain::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0);
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                $userId = $user['id'];
                $query->whereHas('User', function ($q) use ($userId) {
                    $q->where('hander', '=', $userId)->where('is_deleted', '=', 0);
                });
            }

            $count = $query->count();
            return $this->sendResponse(['newcomplains' => $count], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function statisticbytaobao(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $user = Auth::user();
            $userId = $user['id'];

            $query = Cart::whereDate('created_at', '>=', $date->toDateString())->where('domain', '=', 'taobao');
            if (!$user->hasRole('admin')) {
                $query->whereHas('User', function ($q) use ($userId) {
                    $q->where('hander', '=', $userId)->where('is_deleted', '=', 0);
                });
            }
            $linkCount = $query->count();

            $query = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0);
            $query->whereHas('Cart', function ($q) {
                $q->where('domain', '=', 'taobao')->where('is_deleted', '=', 0);
            });
            if (!$user->hasRole('admin')) {
                $query->where('hander', '=', $userId);
            }
            $orderCount = $query->count();

            return $this->sendResponse(['link' => $linkCount, 'order' => $orderCount], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function statisticbytmall(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $user = Auth::user();
            $userId = $user['id'];

            $query = Cart::whereDate('created_at', '>=', $date->toDateString())->where('domain', '=', 'tmall');
            if (!$user->hasRole('admin')) {
                $query->whereHas('User', function ($q) use ($userId) {
                    $q->where('hander', '=', $userId)->where('is_deleted', '=', 0);
                });
            }
            $linkCount = $query->count();

            $query = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0);
            $query->whereHas('Cart', function ($q) {
                $q->where('domain', '=', 'tmall')->where('is_deleted', '=', 0);
            });
            if (!$user->hasRole('admin')) {
                $query->where('hander', '=', $userId);
            }
            $orderCount = $query->count();

            return $this->sendResponse(['link' => $linkCount, 'order' => $orderCount], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function statisticby1688(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $user = Auth::user();
            $userId = $user['id'];

            $query = Cart::whereDate('created_at', '>=', $date->toDateString())->where('domain', '=', '1688');
            if (!$user->hasRole('admin')) {
                $query->whereHas('User', function ($q) use ($userId) {
                    $q->where('hander', '=', $userId)->where('is_deleted', '=', 0);
                });
            }
            $linkCount = $query->count();

            $query = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0);
            $query->whereHas('Cart', function ($q) {
                $q->where('domain', '=', '1688')->where('is_deleted', '=', 0);
            });
            if (!$user->hasRole('admin')) {
                $query->where('hander', '=', $userId);
            }
            $orderCount = $query->count();

            return $this->sendResponse(['link' => $linkCount, 'order' => $orderCount], 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function orderStatisticByStatus(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $user = Auth::user();
            $userId = $user['id'];

            $query = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0);
            if (!$user->hasRole('admin')) {
                $query->where('hander', '=', $userId);
            }
            $total = $query->count();

            $data = [];
            if ($total > 0) {
                $query = Order::selectRaw("status, count(*) value")
                    ->whereDate('created_at', '>=', $date->toDateString())
                    ->where('is_deleted', '=', 0);
                if (!$user->hasRole('admin')) {
                    $query->where('hander', '=', $userId);
                }
                $query->groupBy('status')->orderBy('status');
                $result = $query->get();


                $status = Order::status();
                foreach ($status as $item) {
                    $newItem = new \stdClass();
                    $newItem->id = $item['id'];
                    $newItem->name = $item['name'];
                    $newItem->total = $total;
                    $newItem->val = 0;
                    foreach ($result as $statusitem) {
                        if ($item['id'] == $statusitem->status) {
                            $newItem->val = $statusitem->value;
                            break;
                        }
                    }
                    $newItem->valp = round($newItem->val * 100 / $total, 2);
                    $newItem->valsub = 100 - $newItem->valp;
                    $data[] = $newItem;
                }
            }

            return $this->sendResponse($data, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function orderStatisticByDay(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $user = Auth::user();
            $userId = $user['id'];
            $query = Order::selectRaw("DATE_FORMAT(created_at, '%d/%m/%Y') date, count(*) value")
                ->whereDate('created_at', '>=', $date->toDateString())
                ->where('is_deleted', '=', 0);
            if (!$user->hasRole('admin')) {
                $query->where('hander', '=', $userId);
            }
            $query->groupBy('date')->orderBy('date');

            $result = $query->get();
            $datePeriod = self::returnDates($date->format('d/m/Y'), Carbon::now()->format('d/m/Y'));
            $data = [];
            foreach ($datePeriod as $date) {
                $newDate = new \stdClass();
                $newDate->name = $date->format('d/m/Y');
                $newDate->value = 0;
                foreach ($result as $item) {
                    if ($date->format('d/m/Y') == $item->date) {
                        $newDate->value = $item->value;
                        break;
                    }
                }
                $data[] = $newDate;
            }

            return $this->sendResponse($data, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    function returnDates($fromdate, $todate)
    {
        $fromdate = \DateTime::createFromFormat('d/m/Y', $fromdate);
        $todate = \DateTime::createFromFormat('d/m/Y', $todate);
        return new \DatePeriod(
            $fromdate,
            new \DateInterval('P1D'),
            $todate->modify('+1 day')
        );
    }
}
