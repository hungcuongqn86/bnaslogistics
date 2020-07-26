<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Notifications\Notifiable;

class WithdrawalRequest extends BaseEntity
{
    use Notifiable;

    const CHO_DUYET = 1;
    const DA_DUYET = 2;
    const KHONG_DUYET = 3;

    protected $table = 'withdrawal_request';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'value',
        'content',
        'status',
        'feedback',
        'is_deleted',
        'created_at',
        'updated_at',
        'created_by'
    ];

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
        foreach($this->list_of_status as $key => $item){
            $res[] = ['id' => $key, 'name' => $item];
        }
        return $res;
    }
}
