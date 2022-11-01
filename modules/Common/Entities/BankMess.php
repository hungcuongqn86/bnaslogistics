<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class BankMess extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'bank_mess';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'msg_id',
        'address',
        'body',
        'date'
    ];
}
