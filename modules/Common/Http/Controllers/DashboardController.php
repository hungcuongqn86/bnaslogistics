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
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $count = Cart::whereDate('created_at', '>=', $date->toDateString())->count();
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
            $count = Order::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0)->count();
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
            $count = User::whereDate('created_at', '>=', $date->toDateString())
                ->where('type', '=', 1)
                ->where('active', '=', 1)
                ->where('is_deleted', '=', 0)->count();
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
            $count = Complain::whereDate('created_at', '>=', $date->toDateString())->where('is_deleted', '=', 0)->count();
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

    public function statisticbytmall(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
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

    public function statisticby1688(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
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

    public function orderStatisticByStatus(Request $request)
    {
        $input = $request->all();
        $dn = 7;
        if (isset($input['dn'])) {
            $dn = $input['dn'];
        }
        $date = Carbon::now()->subDays($dn - 1);

        try {
            $total = Order::whereDate('created_at', '>=', $date->toDateString())
                ->where('is_deleted', '=', 0)
                ->count();
            $data = [];

            if ($total > 0) {
                $result = Order::selectRaw("status, count(*) value")
                    ->whereDate('created_at', '>=', $date->toDateString())
                    ->where('is_deleted', '=', 0)
                    ->groupBy('status')
                    ->orderBy('status')
                    ->get();

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
            $result = Order::selectRaw("DATE_FORMAT(created_at, '%d/%m/%Y') date, count(*) value")
                ->whereDate('created_at', '>=', $date->toDateString())
                ->where('is_deleted', '=', 0)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

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
