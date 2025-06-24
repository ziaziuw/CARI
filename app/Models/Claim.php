<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claimable_id',
        'claimable_type',
        'user_id',
        'claimed_at',
        'status',
        'reason',
        'image'
    ];
}
