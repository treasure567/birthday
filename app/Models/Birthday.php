<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Birthday extends Model
{
    use HasFactory;

    protected $table = 'birthdays';

    protected $fillable = [
        'name',
        'picture',
        'month',
        'day',
        'whatsapp',
        'gender',
        'status',
        'last_sent_at'
    ];
}
