<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PointsModel extends Model
{
    protected $table = 'points';

    protected $guarded = ['id'];

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
            ->where('points.status', '!=', 'claimed')   // Hanya yang belum diklaim
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
                    'image'         => $p->image,
                    'category'      => $p->category,
                    'status'        => $p->status,
                    'created_at'    => $p->created_at,
                    'updated_at'    => $p->updated_at,
                    'user_id'       => $p->user_id,
                    'user_created'  => $p->user_created,
                ],
            ];
            array_push($geojson['features'], $feature);
        }
        return $geojson;
    }

    public function geojson_point($id)
    {
        $points = $this->select(DB::raw(
            'id,
            st_asgeojson(geom) as geom,
            name,
            description,
            image,
            category,
            status,
            created_at,
            updated_at'
        ))

            ->where('id', $id)
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
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                    'image' => $p->image,
                    'category'    => $p->category,
                    'status'      => $p->status,
                ],
            ];
            array_push($geojson['features'], $feature);
        }
        return $geojson;
    }
}
