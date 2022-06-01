<?php

namespace Modules\Common\Entities;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Bag extends BaseEntity
{
    use Notifiable, SoftDeletes;

    protected $table = 'bags';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'note_vn',
        'note_tq',
        'note_tc',
        'dvvc',
        'employee_id',
        'status',
        'created_at',
        'updated_at'
    ];

    public function Package()
    {
        return $this->hasMany(Package::class, 'bag_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
