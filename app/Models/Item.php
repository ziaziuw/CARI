<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'location',
        'latitude',
        'longitude',
        'image',
        'status',
        'user_id',
        'found_at',
        'reported_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'found_at' => 'datetime',
        'reported_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Relationship dengan User (yang melaporkan item)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relationship dengan Claims
     * Item bisa diklaim oleh banyak user
     */
    public function claims()
    {
        return $this->morphMany(Claim::class, 'claimable');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter item yang masih tersedia (belum diklaim)
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope untuk filter item yang sudah diklaim
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Accessor untuk mendapatkan URL gambar lengkap
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Accessor untuk format lokasi yang readable
     */
    public function getLocationDisplayAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "Lat: {$this->latitude}, Lng: {$this->longitude}";
        }
        return $this->location ?? 'Lokasi tidak diketahui';
    }

    /**
     * Method untuk mengecek apakah item sudah diklaim
     */
    public function isClaimed()
    {
        return $this->status === 'claimed';
    }

    /**
     * Method untuk mengecek apakah item masih tersedia
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Method untuk mendapatkan klaim yang disetujui
     */
    public function getApprovedClaim()
    {
        return $this->claims()->where('status', 'approved')->first();
    }

    /**
     * Method untuk mendapatkan semua klaim yang pending
     */
    public function getPendingClaims()
    {
        return $this->claims()->where('status', 'pending')->get();
    }
}
