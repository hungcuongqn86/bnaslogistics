<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class BankAccount extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'bank_account';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'account_number',
        'account_name',
        'bin',
        'sender',
        'is_sms',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['bank_debt'];

    public function getBankDebtAttribute()
    {
        $query = $this->Transaction()->where('is_deleted', '=', 0);
        $res = $query->orderBy('id', 'desc')->first();
        if (!empty($res)) {
            return $res->bank_debt;
        } else {
            return 0;
        }
    }

    public function Transaction()
    {
        return $this->hasMany(Transaction::class, 'bank_account', 'id');
    }
}
