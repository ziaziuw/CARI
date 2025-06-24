<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Claim;
use App\Models\Comment;

class PolylinesModel extends Model
{
    protected $table = 'polylines';
    protected $guarded = ['id'];

    protected $fillable = [
        'geom',
        'name',
        'description',
        'image',
    ];

    /**
     * Fetch polylines and return GeoJSON FeatureCollection.
     */
    public function geojson_polylines()
    {
        $polylines = DB::table($this->table)
            ->select(
                'polylines.id',
                DB::raw('ST_AsGeoJSON(polylines.geom) AS geom'),
                'polylines.name',
                'polylines.description',
                'polylines.image',
                DB::raw('ST_Length(polylines.geom, true) AS length_m'),
                DB::raw('ST_Length(polylines.geom, true) / 1000 AS length_km'),
                'polylines.created_at',
                'polylines.updated_at',
                'polylines.user_id',
                'users.name as user_created'
            )
            ->leftJoin('users', 'polylines.user_id', '=', 'users.id')
            ->get();

        return [
            'type'     => 'FeatureCollection',
            'features' => $polylines->map(function ($polyline) {
                return [
                    'type'       => 'Feature',
                    'geometry'   => json_decode($polyline->geom),
                    'properties' => [
                        'id'          => $polyline->id,
                        'name'        => $polyline->name,
                        'description' => $polyline->description,
                        'image'       => $polyline->image,
                        'length_m'    => $polyline->length_m,
                        'length_km'   => $polyline->length_km,
                        'created_at'  => $polyline->created_at,
                        'updated_at'  => $polyline->updated_at,
                        'user_id'     => $polyline->user_id,
                        'user_created' => $polyline->user_created,
                    ],
                ];
            })->toArray(),
        ];
    }

    public function geojson_polyline($id)
    {
        $polylines = DB::table($this->table)
            ->select(
                'id',
                DB::raw('ST_AsGeoJSON(geom) AS geom'),
                'name',
                'description',
                'image',
                DB::raw('ST_Length(geom, true) AS length_m'),
                DB::raw('ST_Length(geom, true) / 1000 AS length_km'),
                'created_at',
                'updated_at'
            )
            ->where('id', $id)
            ->get();

        return [
            'type'     => 'FeatureCollection',
            'features' => $polylines->map(function ($polyline) {
                return [
                    'type'       => 'Feature',
                    'geometry'   => json_decode($polyline->geom),
                    'properties' => [
                        'id'          => $polyline->id,
                        'name'        => $polyline->name,
                        'description' => $polyline->description,
                        'image'       => $polyline->image,
                        'length_m'    => $polyline->length_m,
                        'length_km'   => $polyline->length_km,
                        'created_at'  => $polyline->created_at,
                        'updated_at'  => $polyline->updated_at,
                    ],
                ];
            })->toArray(),
        ];
    }

    public function claims()
    {
        return $this->morphMany(Claim::class, 'claimable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
