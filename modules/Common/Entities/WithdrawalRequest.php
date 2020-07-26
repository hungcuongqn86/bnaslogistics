<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Notifications\Notifiable;

class WithdrawalRequest extends BaseEntity
{
    use Notifiable;

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
}
