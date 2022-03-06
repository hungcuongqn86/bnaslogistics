<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class  Cart extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'carts';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'shop_id',
        'user_id',
        'kiem_hang',
        'dong_go',
        'bao_hiem',
        'chinh_ngach',
        'vat',
        'count_product',
        'tien_hang',
        'vip_id',
        'ck_dv',
        'ck_dv_tt',
        'phi_dat_hang_cs',
        'phi_dat_hang',
        'phi_dat_hang_tt',
        'phi_bao_hiem_cs',
        'phi_bao_hiem_tt',
        'phi_kiem_dem_cs',
        'phi_kiem_dem_tt',
        'ti_gia',
        'status',
        'created_at',
        'updated_at'
    ];

    public function CartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function Shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
