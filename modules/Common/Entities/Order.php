<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Order extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'cart_id',
        'shop_id',
        'carrier_id',
        'code',
        'shipping',
        'ti_gia',
        'count_product',
        'kiem_hang',
        'dong_go',
        'bao_hiem',
        'chinh_ngach',
        'vat',
        'tien_hang',
        'tra_shop',
        'ship_khach_tt',
        'ship_tt_tt',
        'vip_id',
        'ck_dv',
        'ck_dv_tt',
        'ck_vc',
        'deposit',
        'phi_dat_hang_cs',
        'phi_dat_hang',
        'phi_dat_hang_tt',
        'phi_bao_hiem_cs',
        'phi_bao_hiem_tt',
        'phi_kiem_dem_cs',
        'phi_kiem_dem_tt',

        'can_nang_dk',
        'kich_thuoc_dk',
        'cal_option',
        'gia_can_dk',
        'ck_vc_dk',
        'tien_can_dk',
        'dg_1_price',
        'dg_2_price',
        'tien_dong_go_dk',
        'chong_soc_1_price',
        'chong_soc_2_price',
        'tien_chong_soc_dk',

        'dat_coc',
        'dat_coc_content',
        'thanh_ly',
        'hander',
        'china_warehouses_id',
        'china_warehouses_address',
        'content_pc',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id', 'id');
    }

    public function Handle()
    {
        return $this->belongsTo(User::class, 'hander', 'id');
    }

    public function Shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function Cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function Package()
    {
        return $this->hasMany(Package::class, 'order_id', 'id');
    }

    public function History()
    {
        return $this->hasMany(History::class, 'order_id', 'id');
    }

    public static function status()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Chờ báo giá'];
        $res[] = ['id' => 2, 'name' => 'Chờ đặt cọc'];
        $res[] = ['id' => 3, 'name' => 'Đang mua hàng'];
        $res[] = ['id' => 4, 'name' => 'Đã mua hàng'];
        $res[] = ['id' => 5, 'name' => 'Thanh lý'];
        $res[] = ['id' => 6, 'name' => 'Hủy'];
        return $res;
    }
}
