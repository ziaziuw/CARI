<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolygonsModel;

class PolygonsController extends Controller
{
    protected $polygons;
    protected $imageFolder;

    public function __construct()
    {
        $this->middleware('auth');
        $this->polygons = new PolygonsModel();
        $this->imageFolder = public_path('storage/images');
    }

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
        //return view('polygons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'name'         => 'required|unique:polygons,name',
            'description'  => 'required',
            'geom_polygon' => 'required',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ], [
            'name.required'        => 'Name is required',
            'name.unique'          => 'Name already exist',
            'description.required' => 'Description is required',
            'geom_polygon.required' => 'Geometry is required',
            'image.image'          => 'File harus berupa gambar',
            'image.mimes'          => 'Format gambar hanya jpeg,png,jpg,gif,svg',
            'image.max'            => 'Ukuran gambar maksimal 10MB',
        ]);

        // Buat folder jika belum ada
        if (!is_dir($this->imageFolder)) {
            mkdir($this->imageFolder, 0777, true);
        }

        // Proses upload gambar
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name_image = time() . '_polygon.' . $file->getClientOriginalExtension();
            $file->move($this->imageFolder, $name_image);
        } else {
            $name_image = null;
        }

        // Simpan data
        $data = [
            'geom'        => $request->geom_polygon,
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $name_image,
            'user_id'     => auth()->user()->id, //auth user memanggil/mendapatkan id dari user yg login (ini di dlm store)
        ];

        if (! $this->polygons->create($data)) {
            return redirect()->route('map')->with('error', 'Polygon failed to add');
        }

        return redirect()->route('map')->with('success', 'Polygon has been added');
    }

    public function edit(string $id)
    {
        $data = [
            'title' => 'Edit Polygon',
            'id'    => $id,
        ];

        return view('edit-polygon', $data);
    }

    public function update(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'name'         => 'required|unique:polygons,name,' . $id,
            'description'  => 'required',
            'geom_polygon' => 'required',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ], [
            'name.required'        => 'Name is required',
            'name.unique'          => 'Name already exists',
            'description.required' => 'Description is required',
            'geom_polygon.required' => 'Geometry polygon is required',
            'image.image'          => 'File harus berupa gambar',
            'image.mimes'          => 'Format gambar hanya jpeg,png,jpg,gif,svg',
            'image.max'            => 'Ukuran gambar maksimal 10MB',
        ]);

        // Buat folder jika belum ada
        if (!is_dir($this->imageFolder)) {
            mkdir($this->imageFolder, 0777, true);
        }

        // Ambil nama file lama
        $old_image = $this->polygons->find($id)->image;

        // Proses upload gambar baru dan hapus lama
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name_image = time() . '_polygon.' . $file->getClientOriginalExtension();
            $file->move($this->imageFolder, $name_image);

            if ($old_image && file_exists($this->imageFolder . '/' . $old_image)) {
                unlink($this->imageFolder . '/' . $old_image);
            }
        } else {
            $name_image = $old_image;
        }

        // Update data
        $data = [
            'geom'        => $request->geom_polygon,
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $name_image,
        ];

        if (! $this->polygons->find($id)->update($data)) {
            return redirect()->route('map')->with('error', 'Polygon failed to update');
        }

        return redirect()->route('map')->with('success', 'Polygon has been updated');
    }

    public function destroy(string $id)
    {
        // Ambil nama file image
        $imagefile = $this->polygons->find($id)->image;

        // Hapus record
        if (! $this->polygons->destroy($id)) {
            return redirect()->route('map')->with('error', 'Failed to delete polygon');
        }

        // Hapus file jika ada
        if ($imagefile && file_exists($this->imageFolder . '/' . $imagefile)) {
            unlink($this->imageFolder . '/' . $imagefile);
        }

        return redirect()->route('map')->with('success', 'Polygon deleted successfully');
    }
}
