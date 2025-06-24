<?php
// app/Models/PolygonsModel.php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PolygonsModel extends Model
{
    protected $table = 'polygons';
    protected $guarded = ['id'];

    public function geojson_polygons()
    {
        $polygons = DB::table($this->table)
            ->leftJoin('users', 'polygons.user_id', '=', 'users.id')
            ->selectRaw("
            polygons.id,
            ST_AsGeoJSON(polygons.geom) AS geom,
            polygons.name,
            polygons.description,
            polygons.image,
            ST_Area(polygons.geom, true) AS area_m2,
            ST_Area(polygons.geom, true) / 10000 AS area_ha,
            polygons.created_at,
            polygons.updated_at,
            polygons.user_id,
            users.name AS user_created
    ")
            ->get();


        return [
            'type' => 'FeatureCollection',
            'features' => collect($polygons)->map(function ($polygon) {
                return [
                    'type' => 'Feature',
                    'geometry' => json_decode($polygon->geom),
                    'properties' => [
                        'id'            => $polygon->id,
                        'name'          => $polygon->name,
                        'description'   => $polygon->description,
                        'image'         => $polygon->image,
                        'area_m2'       => $polygon->area_m2,
                        'area_ha'       => $polygon->area_ha,
                        'created_at'    => $polygon->created_at,
                        'updated_at'    => $polygon->updated_at,
                        'user_id'       => $polygon->user_id,
                        'user_created'  => $polygon->user_created,
                    ],
                ];
            })->toArray(),
        ];
    }

    public function geojson_polygon($id)
    {
        $polygon = DB::table($this->table)
            ->join('users', 'polygons.user_id', '=', 'users.id')
            ->selectRaw(
                "polygons.id, \
                ST_AsGeoJSON(polygons.geom) AS geom, \
                polygons.name, \
                polygons.description, \
                polygons.image, \
                ST_Area(polygons.geom, true) AS area_m2, \
                ST_Area(polygons.geom, true) / 10000 AS area_ha, \
                polygons.created_at, \
                polygons.updated_at, \
                polygons.user_id, \
                users.name AS user_created"
            )
            ->where('polygons.id', $id)
            ->first();

        if (! $polygon) {
            return null;
        }

        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => json_decode($polygon->geom),
                    'properties' => [
                        'id'            => $polygon->id,
                        'name'          => $polygon->name,
                        'description'   => $polygon->description,
                        'image'         => $polygon->image,
                        'area_m2'       => $polygon->area_m2,
                        'area_ha'       => $polygon->area_ha,
                        'created_at'    => $polygon->created_at,
                        'updated_at'    => $polygon->updated_at,
                        'user_id'       => $polygon->user_id,
                        'user_created'  => $polygon->user_created,
                    ],
                ],
            ],
        ];
    }

    protected $fillable = [
        'geom',
        'name',
        'description',
        'image',
        'user_id',
    ];

    public function claims()
    {
        return $this->morphMany(Claim::class, 'claimable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
