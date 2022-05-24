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
            'SĐT khách hàng',
            'Email khách hàng',
            'Tổng tiền',
            'Thanh toán',
            'Còn thiếu',
            'Phụ trách',
            'Link sp',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['User', 'OrderItems', 'Handle']);
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
            if (!empty($order['order_items'])) {
                foreach ($order['order_items'] as $key => $order_item) {
                    if (!$key) {
                        $data[] = array(
                            'id' => $order['code'],
                            'status' => !empty($arr_status[$order['status']]) ? $arr_status[$order['status']] : $order['status'],
                            'created_at' => date('d-m-Y', strtotime($order['created_at'])),
                            'user_name' => $order['user']['name'],
                            'user_email' => $order['user']['email'],
                            'user_phone_number' => $order['user']['phone_number'],
                            'tien_hang' => (int)$order['tien_hang'],
                            'thanh_toan' => (int)$order['dat_coc'],
                            'thieu' => (int)$order['tien_hang'] - (int)$order['dat_coc'],
                            'handle_name' => !empty($order['handle']['name']) ? $order['handle']['name'] : '',
                            'link' => $order_item['pro_link']
                        );
                    } else {
                        $data[] = array(
                            'id' => '',
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
                    'id' => $order['code'],
                    'status' => !empty($arr_status[$order['status']]) ? $arr_status[$order['status']] : $order['status'],
                    'created_at' => date('d-m-Y', strtotime($order['created_at'])),
                    'user_name' => $order['user']['name'],
                    'user_email' => $order['user']['email'],
                    'user_phone_number' => $order['user']['phone_number'],
                    'tien_hang' => (int)$order['tien_hang'],
                    'thanh_toan' => (int)$order['dat_coc'],
                    'thieu' => (int)$order['tien_hang'] - (int)$order['dat_coc'],
                    'handle_name' => !empty($order['handle']['name']) ? $order['handle']['name'] : '',
                    'link' => ''
                );
            }
        }

        return collect($data);
    }
}
