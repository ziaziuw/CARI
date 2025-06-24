<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\Claim;
use App\Models\Comment;
use App\Models\User;

class PointsModel extends Model
{
    protected $table = 'points';

    protected $guarded = ['id'];

    /**
     * Relationship dengan User (pembuat point)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship dengan Claims (polymorphic)
     */
    public function claims()
    {
        return $this->morphMany(Claim::class, 'claimable');
    }

    /**
     * Relationship dengan Comments (polymorphic)
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Mendapatkan klaim yang sedang pending untuk point ini
     */
    public function pendingClaim()
    {
        return $this->claims()->where('status', 'pending')->first();
    }

    /**
     * Mendapatkan klaim yang sudah approved untuk point ini
     */
    public function approvedClaim()
    {
        return $this->claims()->where('status', 'approved')->first();
    }

    /**
     * Cek apakah point sudah diklaim oleh user tertentu
     */
    public function isClaimedBy($userId)
    {
        return $this->claims()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    /**
     * Cek apakah point dapat diklaim (belum ada klaim yang approved)
     */
    public function isClaimable()
    {
        return !$this->claims()->where('status', 'approved')->exists();
    }

    /**
     * Mendapatkan semua points dalam format GeoJSON
     * Dengan informasi klaim yang ditambahkan
     */
    public function geojson_points()
    {
        $points = $this
            ->select(DB::raw(
                'points.id,
                st_asgeojson(points.geom) as geom,
                points.name,
                points.description,
                points.image,
                points.category,
                points.status,
                points.created_at,
                points.updated_at,
                points.user_id,
                users.name as user_created'
            ))
            ->leftJoin('users', 'points.user_id', '=', 'users.id')
            ->with(['claims' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->get();

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($points as $p) {
            // Cek status klaim
            $claimStatus = 'available'; // default
            $claimedBy = null;
            $claimedAt = null;

            if ($p->claims->isNotEmpty()) {
                $approvedClaim = $p->claims->first();
                $claimStatus = 'claimed';
                $claimedBy = $approvedClaim->user->name ?? 'Unknown';
                $claimedAt = $approvedClaim->claimed_at;
            } elseif ($p->claims()->where('status', 'pending')->exists()) {
                $claimStatus = 'pending';
            }

            $feature = [
                'type' => 'Feature',
                'geometry' => json_decode($p->geom),
                'properties' => [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'description'   => $p->description,
                    'image'         => $p->image ? asset('storage/' . $p->image) : null,
                    'category'      => $p->category,
                    'status'        => $p->status,
                    'created_at'    => $p->created_at,
                    'updated_at'    => $p->updated_at,
                    'user_id'       => $p->user_id,
                    'user_created'  => $p->user_created,
                    // Informasi klaim
                    'claim_status'  => $claimStatus,
                    'claimed_by'    => $claimedBy,
                    'claimed_at'    => $claimedAt,
                    'is_claimable'  => $claimStatus === 'available',
                ],
            ];
            array_push($geojson['features'], $feature);
        }
        return $geojson;
    }

    /**
     * Mendapatkan points yang belum diklaim dalam format GeoJSON
     */
    public function geojson_available_points()
    {
        $points = $this
            ->select(DB::raw(
                'points.id,
                st_asgeojson(points.geom) as geom,
                points.name,
                points.description,
                points.image,
                points.category,
                points.status,
                points.created_at,
                points.updated_at,
                points.user_id,
                users.name as user_created'
            ))
            ->leftJoin('users', 'points.user_id', '=', 'users.id')
            ->whereDoesntHave('claims', function ($query) {
                $query->where('status', 'approved');
            })
            ->get();

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($points as $p) {
            $feature = [
                'type' => 'Feature',
                'geometry' => json_decode($p->geom),
                'properties' => [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'description'   => $p->description,
                    'image'         => $p->image ? asset('storage/' . $p->image) : null,
                    'category'      => $p->category,
                    'status'        => $p->status,
                    'created_at'    => $p->created_at,
                    'updated_at'    => $p->updated_at,
                    'user_id'       => $p->user_id,
                    'user_created'  => $p->user_created,
                    'is_claimable'  => true,
                ],
            ];
            array_push($geojson['features'], $feature);
        }
        return $geojson;
    }

    /**
     * Mendapatkan single point dalam format GeoJSON dengan informasi klaim
     */
    public function geojson_point($id)
    {
        $point = $this->select(DB::raw(
            'points.id,
            st_asgeojson(points.geom) as geom,
            points.name,
            points.description,
            points.image,
            points.category,
            points.status,
            points.created_at,
            points.updated_at,
            points.user_id'
        ))
            ->with(['claims.user', 'user'])
            ->where('points.id', $id)
            ->first();

        if (!$point) {
            return null;
        }

        // Informasi klaim
        $claimInfo = [
            'status' => 'available',
            'claimed_by' => null,
            'claimed_at' => null,
            'claim_reason' => null,
            'pending_claims_count' => 0,
        ];

        $approvedClaim = $point->claims->where('status', 'approved')->first();
        if ($approvedClaim) {
            $claimInfo = [
                'status' => 'claimed',
                'claimed_by' => $approvedClaim->user->name ?? 'Unknown',
                'claimed_at' => $approvedClaim->claimed_at,
                'claim_reason' => $approvedClaim->reason,
                'pending_claims_count' => $point->claims->where('status', 'pending')->count(),
            ];
        } elseif ($point->claims->where('status', 'pending')->isNotEmpty()) {
            $claimInfo['status'] = 'pending';
            $claimInfo['pending_claims_count'] = $point->claims->where('status', 'pending')->count();
        }

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => json_decode($point->geom),
                    'properties' => [
                        'id' => $point->id,
                        'name' => $point->name,
                        'description' => $point->description,
                        'created_at' => $point->created_at,
                        'updated_at' => $point->updated_at,
                        'image' => $point->image ? asset('storage/' . $point->image) : null,
                        'category' => $point->category,
                        'status' => $point->status,
                        'user_id' => $point->user_id,
                        'user_created' => $point->user->name ?? 'Unknown',
                        // Informasi klaim
                        'claim_info' => $claimInfo,
                        'is_claimable' => $claimInfo['status'] === 'available',
                    ],
                ]
            ],
        ];

        return $geojson;
    }

    /**
     * Scope untuk points yang dapat diklaim
     */
    public function scopeClaimable($query)
    {
        return $query->whereDoesntHave('claims', function ($q) {
            $q->where('status', 'approved');
        });
    }

    /**
     * Scope untuk points yang sudah diklaim
     */
    public function scopeClaimed($query)
    {
        return $query->whereHas('claims', function ($q) {
            $q->where('status', 'approved');
        });
    }

    /**
     * Scope untuk points dengan klaim pending
     */
    public function scopeWithPendingClaims($query)
    {
        return $query->whereHas('claims', function ($q) {
            $q->where('status', 'pending');
        });
    }

    /**
     * Accessor untuk image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Accessor untuk cek apakah ada gambar
     */
    public function getHasImageAttribute()
    {
        return !empty($this->image) && $this->image !== 'null';
    }
}
