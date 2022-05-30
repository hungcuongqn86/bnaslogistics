<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Receipt extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'receipts';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'receipt_date',
        'note',
        'employee_id',
        'created_at',
        'updated_at'
    ];

    public function Package()
    {
        return $this->hasMany(Package::class, 'receipt_id', 'id');
    }
}
