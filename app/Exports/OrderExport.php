<?php

namespace App\Exports;

use Modules\Common\Entities\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;

use Illuminate\Support\Collection;

class OrderExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
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
            'Shop',
            'Tên khách hàng',
            'SĐT khách hàng',
            'Email khách hàng',
            'Tiền hàng',
            'Thanh toán',
            'Còn thiếu',
            'Thực hiện',
            'Link sp',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['User', 'Cart', 'Shop', 'Handle'])->where('is_deleted', '=', 0);
        $query->orderBy('id', 'desc');
        $orderData = $query->get()->toArray();
        $arr_status = [
            '1' => 'Chờ báo giá',
            '2' => 'Chờ đặt cọc',
            '3' => 'Đang mua hàng',
            '5' => 'Thanh lý',
            '6' => 'Hủy',
        ];
        $data = new Collection();
        foreach ($orderData as $order) {
            foreach ($order['cart'] as $key => $cart) {
                if (!$key) {

                    $data[] = array(
                        'id' => $order['id'],
                        'status' => !empty($arr_status[$order['status']]) ? $arr_status[$order['status']] : $order['status'],
                        'created_at' => date('d-m-Y', strtotime($order['created_at'])),
                        'shop' => $order['shop']['name'],
                        'user_name' => $order['user']['name'],
                        'user_email' => $order['user']['email'],
                        'user_phone_number' => $order['user']['phone_number'],
                        'tien_hang' => (int)$order['tong'],
                        'thanh_toan' => (int)$order['thanh_toan'],
                        'thieu' => (int)$order['tong'] - (int)$order['thanh_toan'],
                        'handle_name' => !empty($order['handle']['name']) ? $order['handle']['name'] : '',
                        'link' => $cart['pro_link']
                    );
                } else {
                    $data[] = array(
                        'id' => '',
                        'status' => '',
                        'created_at' => '',
                        'shop' => '',
                        'user_name' => '',
                        'user_email' => '',
                        'user_phone_number' => '',
                        'tien_hang' => '',
                        'thanh_toan' => '',
                        'thieu' => '',
                        'handle_name' => '',
                        'link' => $cart['pro_link']
                    );
                }
            }
        }

        return collect($data);
    }
}
