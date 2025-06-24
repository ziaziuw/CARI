<?php

namespace App\Http\Controllers;

use App\Models\PointsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PointsController extends Controller
{
    protected $points;

    public function __construct()
    {
        $this->points = new PointsModel();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Map',

        ];
        return view('map', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'title' => 'Buat Laporan Baru',
        ];
        return view('create-point', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'name'        => 'required|string|max:255|unique:points,name',
            'description' => 'required|string|max:1000',
            'category'    => 'required|in:lost,found',
            'geom_point'  => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ], [
            'name.required'        => 'Nama barang wajib diisi',
            'name.unique'          => 'Nama barang sudah terdaftar',
            'name.max'             => 'Nama barang maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'description.max'      => 'Deskripsi maksimal 1000 karakter',
            'category.required'    => 'Kategori harus dipilih',
            'category.in'          => 'Kategori tidak valid (harus lost atau found)',
            'geom_point.required'  => 'Lokasi pada peta wajib dipilih',
            'image.image'          => 'File harus berupa gambar',
            'image.mimes'          => 'Format gambar hanya jpeg, png, jpg, gif, webp',
            'image.max'            => 'Ukuran gambar maksimal 5MB',
        ]);

        try {
            DB::beginTransaction();
            // Proses upload gambar menggunakan Laravel Storage
            $imageName = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // Generate unique filename
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $imageName = time() . '_' . Str::slug($originalName) . '.' . $extension;

                // Store gambar di storage/app/public/images
                $path = $file->storeAs('public/images', $imageName);

                if (!$path) {
                    throw new \Exception('Gagal menyimpan gambar');
                }

                Log::info('Image uploaded successfully: ' . $imageName);
            }

            // Prepare data untuk disimpan
            $data = [
                'geom'        => DB::raw("ST_GeomFromText('{$request->geom_point}', 4326)"),
                'name'        => trim($request->name),
                'description' => trim($request->description),
                'image'       => $imageName, // Simpan hanya nama file
                'user_id'     => auth()->user()->id,
                'category'    => $request->category,
                'status'      => 'available',
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Insert data
            $point = $this->points->create($data);

            if (!$point) {
                throw new \Exception('Gagal menyimpan data ke database');
            }

            DB::commit();

            Log::info('Point created successfully', [
                'id' => $point->id,
                'name' => $point->name,
                'image' => $imageName
            ]);

            return redirect()->route('map')
                ->with('success', 'Laporan berhasil dibuat! Data akan muncul di peta.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus gambar jika ada error
            if ($imageName && Storage::exists('public/images/' . $imageName)) {
                Storage::delete('public/images/' . $imageName);
            }

            Log::error('Error creating point: ' . $e->getMessage(), [
                'request_data' => $request->except(['image', '_token']),
                'user_id' => auth()->user()->id ?? null
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat laporan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $point = $this->points->with('user')->findOrFail($id);

            $data = [
                'title' => 'Detail Laporan',
                'point' => $point,
            ];

            return view('show-point', $data);
        } catch (\Exception $e) {
            Log::error('Error showing point: ' . $e->getMessage());
            return redirect()->route('map')
                ->with('error', 'Laporan tidak ditemukan');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $point = $this->points->findOrFail($id);

            // Check permission (optional: only allow owner or admin to edit)
            if (auth()->user()->id !== $point->user_id && !auth()->user()->is_admin) {
                return redirect()->route('map')
                    ->with('error', 'Anda tidak memiliki izin untuk mengedit laporan ini');
            }

            $data = [
                'title' => 'Edit Laporan',
                'point' => $point,
            ];  
            return view('edit-point', $data);
        } catch (\Exception $e) {
            Log::error('Error editing point: ' . $e->getMessage());
            return redirect()->route('map')
                ->with('error', 'Laporan tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate request
        $request->validate([
            'name'        => 'required|string|max:255|unique:points,name,' . $id,
            'description' => 'required|string|max:1000',
            'category'    => 'required|in:lost,found',
            'geom_point'  => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'name.required'        => 'Nama barang wajib diisi',
            'name.unique'          => 'Nama barang sudah terdaftar',
            'name.max'             => 'Nama barang maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'description.max'      => 'Deskripsi maksimal 1000 karakter',
            'category.required'    => 'Kategori harus dipilih',
            'category.in'          => 'Kategori tidak valid',
            'geom_point.required'  => 'Lokasi pada peta wajib dipilih',
            'image.image'          => 'File harus berupa gambar',
            'image.mimes'          => 'Format gambar hanya jpeg, png, jpg, gif, webp',
            'image.max'            => 'Ukuran gambar maksimal 5MB',
        ]);

        try {
            DB::beginTransaction();
            $point = $this->points->findOrFail($id);

            // Check permission
            if (auth()->user()->id !== $point->user_id && !auth()->user()->is_admin) {
                throw new \Exception('Anda tidak memiliki izin untuk mengedit laporan ini');
            }

            $oldImage = $point->image;
            $imageName = $oldImage; // Default: keep old image

            // Proses upload gambar baru
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // Generate unique filename
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $imageName = time() . '_' . Str::slug($originalName) . '.' . $extension;

                // Store new image
                $path = $file->storeAs('public/images', $imageName);

                if (!$path) {
                    throw new \Exception('Gagal menyimpan gambar baru');
                }

                // Delete old image if exists
                if ($oldImage && Storage::exists('public/images/' . $oldImage)) {
                    Storage::delete('public/images/' . $oldImage);
                    Log::info('Old image deleted: ' . $oldImage);
                }

                Log::info('New image uploaded: ' . $imageName);
            }

            // Prepare update data
            $data = [
                'geom'        => DB::raw("ST_GeomFromText('{$request->geom_point}', 4326)"),
                'name'        => trim($request->name),
                'description' => trim($request->description),
                'image'       => $imageName,
                'category'    => $request->category,
                'updated_at'  => now(),
                // Note: Don't change status unless specifically intended
            ];

            // Update data
            $point->update($data);

            DB::commit();

            Log::info('Point updated successfully', [
                'id' => $point->id,
                'name' => $point->name,
                'old_image' => $oldImage,
                'new_image' => $imageName
            ]);

            return redirect()->route('map')
                ->with('success', 'Laporan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete new image if upload was successful but DB update failed
            if (isset($imageName) && $imageName !== $oldImage && Storage::exists('public/images/' . $imageName)) {
                Storage::delete('public/images/' . $imageName);
            }

            Log::error('Error updating point: ' . $e->getMessage(), [
                'point_id' => $id,
                'user_id' => auth()->user()->id ?? null
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $point = $this->points->findOrFail($id);

            // Check permission
            if (auth()->user()->id !== $point->user_id && !auth()->user()->is_admin) {
                throw new \Exception('Anda tidak memiliki izin untuk menghapus laporan ini');
            }

            $imageName = $point->image;

            // Delete point from database
            if (!$point->delete()) {
                throw new \Exception('Gagal menghapus data dari database');
            }

            // Delete image file if exists
            if ($imageName && Storage::exists('public/images/' . $imageName)) {
                Storage::delete('public/images/' . $imageName);
                Log::info('Image deleted: ' . $imageName);
            }

            DB::commit();

            Log::info('Point deleted successfully', [
                'id' => $id,
                'name' => $point->name,
                'image' => $imageName
            ]);

            return redirect()->route('map')
                ->with('success', 'Laporan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting point: ' . $e->getMessage(), [
                'point_id' => $id,
                'user_id' => auth()->user()->id ?? null
            ]);

            return redirect()->route('map')
                ->with('error', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
    }

    /**
     * Claim a point/report
     */
    public function claim(string $id)
    {
        try {
            DB::beginTransaction();

            $point = $this->points->findOrFail($id);

            // Check if the point is already claimed
            if ($point->status === 'claimed') {
                throw new \Exception('Laporan ini sudah diklaim');
            }

            // Check if the user is trying to claim their own report
            if (auth()->user()->id === $point->user_id) {
                throw new \Exception('Anda tidak dapat mengklaim laporan Anda sendiri');
            }

            // Update the point's status to "claimed"
            $point->status = 'claimed';
            $point->claimed_by = auth()->user()->id;
            $point->claimed_at = now();

            // Save the updated point
            $point->save();

            DB::commit();

            Log::info('Point claimed successfully', [
                'point_id' => $id,
                'claimed_by' => auth()->user()->id,
                'point_name' => $point->name
            ]);

            return redirect()->back()
                ->with('success', 'Laporan berhasil diklaim! Terima kasih telah membantu.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error claiming point: ' . $e->getMessage(), [
                'point_id' => $id,
                'user_id' => auth()->user()->id ?? null
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mengklaim laporan: ' . $e->getMessage());
        }
    }

    /**
     * Get points data for API (for map display)
     */
    public function apiPoints()
    {
        try {
            $points = $this->points
                ->with('user:id,name')
                ->select([
                    'id',
                    'name',
                    'description',
                    'category',
                    'status',
                    'image',
                    'user_id',
                    'created_at',
                    'updated_at',
                    DB::raw('ST_AsGeoJSON(geom) as geojson')
                ])
                ->where('status', '!=', 'deleted') // Exclude deleted items
                ->orderBy('created_at', 'desc')
                ->get();

            $geojson = [
                'type' => 'FeatureCollection',
                'features' => []
            ];

            foreach ($points as $point) {
                $geometry = json_decode($point->geojson);

                $feature = [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $point->id,
                        'name' => $point->name,
                        'description' => $point->description,
                        'category' => $point->category,
                        'status' => $point->status,
                        'image' => $point->image,
                        'user_created' => $point->user ? $point->user->name : 'Unknown',
                        'created_at' => $point->created_at->toISOString(),
                        'updated_at' => $point->updated_at->toISOString(),
                    ],
                    'geometry' => $geometry
                ];

                $geojson['features'][] = $feature;
            }

            return response()->json($geojson['features']);
        } catch (\Exception $e) {
            Log::error('Error getting API points: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data peta'], 500);
        }
    }

    /**
     * Get image URL helper
     */
    public function getImageUrl($imageName)
    {
        if (!$imageName) {
            return null;
        }

        // Check if image exists
        if (Storage::exists('public/images/' . $imageName)) {
            return asset('storage/images/' . $imageName);
        }

        return null;
    }

    /**
     * Debug storage and images
     */
    public function debugStorage()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        $storageExists = Storage::exists('public/images');
        $publicLinkExists = is_link(public_path('storage'));
        $publicLinkTarget = $publicLinkExists ? readlink(public_path('storage')) : null;

        $images = [];
        if ($storageExists) {
            $images = Storage::files('public/images');
        }

        return response()->json([
            'storage_path' => storage_path('app/public/images'),
            'public_path' => public_path('storage/images'),
            'storage_exists' => $storageExists,
            'public_link_exists' => $publicLinkExists,
            'public_link_target' => $publicLinkTarget,
            'images_count' => count($images),
            'images' => $images,
            'sample_urls' => [
                'storage_url' => Storage::url('public/images/sample.jpg'),
                'asset_url' => asset('storage/images/sample.jpg'),
            ]
        ]);
    }
}
