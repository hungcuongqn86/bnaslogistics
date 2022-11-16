<?php

namespace Modules\Common\Entities;

use Illuminate\Notifications\Notifiable;
use App\User;

class History extends BaseEntity
{
    use Notifiable;

    protected $table = 'history';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'content',
        'hide',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['user_name'];

    public function getUserNameAttribute()
    {
        return $this->User()->first()->name;
    }

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function types()
    {
        $res = [];
        $res[] = ['id' => 1, 'name' => 'Kết đơn', 'sys' => 1];
        $res[] = ['id' => 12, 'name' => 'Xác nhận đơn hàng', 'sys' => 0];
        $res[] = ['id' => 2, 'name' => 'Phân công thực hiện', 'sys' => 1];
        $res[] = ['id' => 3, 'name' => 'Đặt cọc', 'sys' => 1];
        $res[] = ['id' => 4, 'name' => 'Mua hàng', 'sys' => 0];
        $res[] = ['id' => 6, 'name' => 'Hủy', 'sys' => 0];
        $res[] = ['id' => 7, 'name' => 'Hoàn cọc', 'sys' => 0];
        $res[] = ['id' => 8, 'name' => 'Sửa đơn đặt hàng', 'sys' => 1];
        $res[] = ['id' => 9, 'name' => 'Xuất kho thanh lý', 'sys' => 1];
        $res[] = ['id' => 10, 'name' => 'Xác nhận đơn ký gửi', 'sys' => 1];
        $res[] = ['id' => 11, 'name' => 'Cập nhật kiện hàng', 'sys' => 1];
        return $res;
    }
}
