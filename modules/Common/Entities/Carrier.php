<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Carrier extends BaseEntity
{
    use Notifiable, SoftDeletes;

    const CHO_DUYET = 1;
    const DA_DUYET = 2;
    const KHONG_DUYET = 3;

    protected $table = 'carriers';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'product_count',
        'kiem_hang',
        'dong_go',
        'bao_hiem',
        'chinh_ngach',
        'vat',
        'vip_id',
        'ck_vc',
        'phi_kiem_dem_cs',
        'phi_kiem_dem_tt',
        'china_warehouses_id',
        'china_warehouses_address',
        'approve_id',
        'approve_at',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['statusname'];

    public function getStatusnameAttribute()
    {
        $statusname = '';
        if (!empty($this->attributes['status'])) {
            $statusname = $this->list_of_status[$this->attributes['status']];
        }
        return $statusname;
    }

    public function Order()
    {
        return $this->hasMany(Order::class, 'carrier_id', 'id');
    }

    public function CarrierPackage()
    {
        return $this->hasMany(CarrierPackage::class, 'carrier_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public $list_of_status = [
        self::CHO_DUYET => "Chờ nhận",
        self::DA_DUYET => "Đã nhận",
        self::KHONG_DUYET => "Từ chối"
    ];

    public function status()
    {
        $res = [];
        foreach ($this->list_of_status as $key => $item) {
            $res[] = ['id' => $key, 'name' => $item];
        }
        return $res;
    }
}
