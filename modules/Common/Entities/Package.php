<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Package extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'package';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'package_code',
        'contract_code',
        'ship_khach',
        'ship_khach_tt',
        'ship_tt',
        'ship_tt_tt',
        'tra_shop',
        'thanh_toan',
        'status',
        'is_main',
        'product_name',
        'product_count',
        'carrier_brand',
        'description',
        'note',
        'note_tl',
        'vi_tri_kho_viet',
        'ghi_chu_kho_viet',
        'vi_tri_kho_trung',
        'ghi_chu_kho_trung',
        'weight',
        'weight_qd',
        'size',
        'c_d',
        'c_r',
        'c_c',
        'cal_option',
        'gia_can',
        'tien_can',
        'ck_vc_tt',
        'tien_can_tt',
        'phi_van_phat_sinh',
        'dg_1_price',
        'dg_2_price',
        'tien_dong_go',
        'chong_soc_1_price',
        'chong_soc_2_price',
        'tien_chong_soc',
        'tien_chong_soc_tt',
        'tien_thanh_ly',
        'receipt_id',
        'tq_receipt_id',
        'bag_id',
        'bill_id',
        'created_at',
        'updated_at'
    ];

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function Receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id', 'id');
    }

    public function TqReceipt()
    {
        return $this->belongsTo(TqReceipt::class, 'tq_receipt_id', 'id');
    }

    public function status()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Chờ mua'];
        $res[] = ['id' => 2, 'name' => 'Đã mua'];
        $res[] = ['id' => 3, 'name' => 'Shop đang giao'];
        $res[] = ['id' => 4, 'name' => 'Kho TQ nhận'];
        $res[] = ['id' => 5, 'name' => 'Đang về VN'];
        $res[] = ['id' => 6, 'name' => 'Trong kho VN'];
        $res[] = ['id' => 7, 'name' => 'Thanh lý'];
        return $res;
    }
}
