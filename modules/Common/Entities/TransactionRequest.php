<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class TransactionRequest extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'transaction_requests';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'code',
        'value',
        'vqr_bank_code',
        'vqr_bank_name',
        'vqr_bank_bin',
        'vqr_bank_qr_code',
        'account_name',
        'account_number',
        'created_at',
        'updated_at'
    ];
}
