<?php

namespace App\Exports;

use Modules\Common\Entities\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class OrderExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'Đơn hàng',
            'Link sp',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['Cart'])->where('is_deleted', '=', 0);
        $orderData = $query->get(['id'])->toArray();
        $data = new Collection();
        foreach ($orderData as $order) {
            foreach ($order['cart'] as $key => $cart) {
                if (!$key) {
                    $data[] = array(
                        'id' => $order['id'],
                        'link' => $cart['pro_link']
                    );
                } else {
                    $data[] = array(
                        'id' => '',
                        'link' => $cart['pro_link']
                    );
                }
            }
        }

        return collect($data);
    }
}
