<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Common\Entities\Order;

class OrderExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    private $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:W1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'Đơn hàng',
            'Trạng thái',
            'Ngày tạo',
            'Tên khách hàng',
            'Email khách hàng',
            'SĐT khách hàng',
            'Tiền hàng',
            'Phí dịch vụ mua hàng',
            'Phí kiểm đếm',
            'Tổng tiền đơn hàng',
            'Phí vận chuyển',
            'Phí đóng gỗ',
            'Phí chống sốc',
            'Phí ship nội địa',
            'Tổng cộng',
            'Đặt cọc',
            'Phụ trách',
            'Thông số SP',
            'Đơn giá',
            'Số lượng',
            'Link SP',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['User', 'OrderItems', 'Handle', 'Package']);
        $filter = $this->filter;

        $sKeySearch = isset($filter['key']) ? $filter['key'] : '';
        if (!empty($sKeySearch)) {
            $query->whereHas('User', function ($q) use ($sKeySearch) {
                $q->where('name', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('email', 'LIKE', '%' . $sKeySearch . '%');
                $q->orWhere('phone_number', 'LIKE', '%' . $sKeySearch . '%');
            });
        }

        $package_code = isset($filter['package_code']) ? trim($filter['package_code']) : '';
        $contract_code = isset($filter['contract_code']) ? trim($filter['contract_code']) : '';
        if (!empty($package_code) || !empty($contract_code)) {
            if ($package_code === '#') {
                $query->whereHas('Package', function ($q) use ($package_code, $contract_code) {
                    $q->whereNull('package_code');
                    if (!empty($contract_code)) {
                        $q->where('contract_code', '=', $contract_code);
                    }
                });
            } else {
                $query->whereHas('Package', function ($q) use ($package_code, $contract_code) {
                    if (!empty($package_code)) {
                        $q->where('package_code', '=', $package_code);
                    }
                    if (!empty($contract_code)) {
                        $q->where('contract_code', '=', $contract_code);
                    }
                });
            }
        }

        $code = isset($filter['code']) ? trim($filter['code']) : '';
        if (!empty($code)) {
            $query->where('code', '=', $code);
        }

        $iuser = isset($filter['user_id']) ? $filter['user_id'] : 0;
        if ($iuser > 0) {
            $query->where('user_id', '=', $iuser);
        }

        $ihander = isset($filter['hander']) ? $filter['hander'] : 0;
        if ($ihander > 0) {
            $query->where('hander', '=', $ihander);
        }

        $istatus = isset($filter['status']) ? $filter['status'] : 0;
        if ($istatus > 0) {
            $query->where('status', '=', $istatus);
        }

        $query->orderBy('id', 'desc');
        $orderData = $query->get()->toArray();
        $arr_status = [
            '1' => 'Chờ báo giá',
            '2' => 'Chờ đặt cọc',
            '3' => 'Đang mua hàng',
            '4' => 'Đã mua hàng',
            '5' => 'Thanh lý',
            '6' => 'Hủy',
        ];
        $data = new Collection();
        foreach ($orderData as $order) {
            $tien_van_chuyen = 0;
            $tien_dong_go = 0;
            $tien_chong_soc = 0;
            if (!empty($order['package'])) {
                $packages = $order['package'];
                foreach ($packages as $package) {
                    $tien_van_chuyen = $tien_van_chuyen + (int)$package['tien_can_tt'] + (int)$package['phi_van_phat_sinh'];
                    $tien_dong_go = $tien_dong_go + (int)$package['tien_dong_go'];
                    $tien_chong_soc = $tien_chong_soc + (int)$package['tien_chong_soc_tt'];
                }
            }

            if ($tien_van_chuyen == 0) {
                $tien_van_chuyen = (int)$order['tien_can_dk'];
            }

            if ($tien_dong_go == 0) {
                $tien_dong_go = (int)$order['tien_dong_go_dk'];
            }

            if ($tien_chong_soc == 0) {
                $tien_chong_soc = (int)$order['tien_chong_soc_dk'];
            }

            if (!empty($order['order_items'])) {
                foreach ($order['order_items'] as $key => $order_item) {
                    $size_color = "";
                    if (!empty($order_item['colortxt'])) {
                        $size_color = $order_item['colortxt'];
                    }

                    if (!empty($order_item['sizetxt'])) {
                        if (!empty($size_color)) {
                            $size_color = $size_color . " " . $order_item['sizetxt'];
                        } else {
                            $size_color = $order_item['sizetxt'];
                        }
                    }

                    if (!$key) {
                        $data[] = array(
                            'code' => $order['code'],
                            'status' => !empty($arr_status[$order['status']]) ? $arr_status[$order['status']] : $order['status'],
                            'created_at' => date('d-m-Y', strtotime($order['created_at'])),
                            'user_name' => !empty($order['user']) ? $order['user']['name'] : '',
                            'user_email' => !empty($order['user']) ? $order['user']['email'] : '',
                            'user_phone_number' => !empty($order['user']) ? $order['user']['phone_number'] : '',
                            'tien_hang' => (int)$order['tien_hang'],
                            'phi_dat_hang_tt' => (int)$order['phi_dat_hang_tt'],
                            'phi_kiem_dem_tt' => (int)$order['phi_kiem_dem_tt'],
                            'tong_tien_don' => (int)$order['tien_hang'] + (int)$order['phi_dat_hang_tt'] + (int)$order['phi_kiem_dem_tt'],
                            'tien_van_chuyen' => (int)$tien_van_chuyen,
                            'tien_dong_go' => (int)$tien_dong_go,
                            'tien_chong_soc' => (int)$tien_chong_soc,
                            'tien_ship_nd' => (int)$order['ship_khach_tt'],
                            'tong_cong' => (int)$order['tien_hang'] + (int)$order['phi_dat_hang_tt'] + (int)$order['phi_kiem_dem_tt'] + (int)$order['ship_khach_tt'] + (int)$tien_van_chuyen + (int)$tien_dong_go + (int)$tien_chong_soc,
                            'dat_coc' => (int)$order['dat_coc'],
                            'handle_name' => !empty($order['handle']) ? $order['handle']['name'] : '',
                            'size_color' => $size_color,
                            'price' => $order_item['price'],
                            'amount' => $order_item['amount'],
                            'link' => $order_item['pro_link']
                        );
                    } else {
                        $data[] = array(
                            'code' => '',
                            'status' => '',
                            'created_at' => '',
                            'user_name' => '',
                            'user_email' => '',
                            'user_phone_number' => '',
                            'tien_hang' => '',
                            'thanh_toan' => '',
                            'thieu' => '',
                            'handle_name' => '',
                            'link' => $order_item['pro_link']
                        );
                    }
                }
            } else {
                $data[] = array(
                    'code' => $order['code'],
                    'status' => !empty($arr_status[$order['status']]) ? $arr_status[$order['status']] : $order['status'],
                    'created_at' => date('d-m-Y', strtotime($order['created_at'])),
                    'user_name' => $order['user']['name'],
                    'user_email' => $order['user']['email'],
                    'user_phone_number' => $order['user']['phone_number'],
                    'tien_hang' => (int)$order['tien_hang'],
                    'phi_dat_hang_tt' => (int)$order['phi_dat_hang_tt'],
                    'phi_kiem_dem_tt' => (int)$order['phi_kiem_dem_tt'],
                    'tong_tien_don' => (int)$order['tien_hang'] + (int)$order['phi_dat_hang_tt'] + (int)$order['phi_kiem_dem_tt'],
                    'tien_van_chuyen' => (int)$tien_van_chuyen,
                    'tien_dong_go' => (int)$tien_dong_go,
                    'tien_chong_soc' => (int)$tien_chong_soc,
                    'tien_ship_nd' => (int)$order['ship_khach_tt'],
                    'tong_cong' => (int)$order['tien_hang'] + (int)$order['phi_dat_hang_tt'] + (int)$order['phi_kiem_dem_tt'] + (int)$order['ship_khach_tt'] + (int)$tien_van_chuyen + (int)$tien_dong_go + (int)$tien_chong_soc,
                    'dat_coc' => (int)$order['dat_coc'],
                    'handle_name' => !empty($order['handle']) ? $order['handle']['name'] : '',
                    'size_color' => "",
                    'price' => "",
                    'amount' => "",
                    'link' => ""
                );
            }
        }

        return collect($data);
    }
}
