<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Notifications\Notifiable;

class Bill extends BaseEntity
{
    use Notifiable;

    protected $table = 'bills';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'code',
        'bill_date',
        'tien_can',
        'tien_dong_go',
        'tien_chong_soc',
        'tien_thanh_ly',
        'cuoc_van_phat_sinh',
        'so_ma',
        'status',
        'employee_id',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function status()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Đang lưu'];
        $res[] = ['id' => 2, 'name' => 'Đã xuất'];
        return $res;
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function Package()
    {
        return $this->hasMany(Package::class, 'bill_id', 'id');
    }
}
