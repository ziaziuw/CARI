@extends('layout.template')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">
    <!-- Leaflet.AwesomeMarkers CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.css" />
    <style>
        #map {
            width: 100%;
            height: calc(100vh - 56px);
            position: absolute;
            top: 56px;
            left: 0;
            right: 0;
        }

        #legend {
            z-index: 1001;
            /* harus lebih tinggi dari leaflet-control */
        }


        body,
        html {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
    </style>
@endsection


@section('content')
    <div id="map"></div>

    <!-- Legend -->
    <div id="legend" class="position-absolute top-0 end-0 m-3 bg-white p-2 border">
        <i class="fa fa-question-circle text-warning"></i> Kehilangan<br>
        <i class="fa fa-check-circle text-success"></i> Temuan
    </div>

    <!-- Modal Create Point-->
    <div class="modal fade" id="createpointModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Laporan Titik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('points.store') }}" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf

                        <!-- Nama Barang -->
                        <div class="mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input name="name" class="form-control" required>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" required></textarea>
                        </div>

                        <!-- Kategori -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="lost">Kehilangan</option>
                                <option value="found">Temuan</option>
                            </select>
                        </div>

                        <!-- Geometry (akan diisi otomatis oleh JS) -->
                        <input type="hidden" name="geom_point" id="geom_point">


                        <!-- Foto (opsional) -->
                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="image" class="form-control">
                            <img id="preview-image-point" class="img-thumbnail mt-2" width="200" src="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal Create Polyline-->
    <div class="modal fade" id="createpolylineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Buat Laporan Jalur</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('polylines.store') }}" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Fill point name">
                        </div>


                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <!-- kategori -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="lost">Kehilangan</option>
                                <option value="found">Temuan</option>
                            </select>
                        </div>
                        <!-- Geometry (akan diisi otomatis oleh JS) -->
                        <input type="hidden" name="geom_polyline" id="geom_polyline">


                        <div class="mb-3">
                            <label for="image" class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" id="image_polyline" name="image"
                                onchange="document.getElementById('preview-image-polyline').src = window.URL.createObjectURL(this.files[0])">
                            <img src="" alt="" id="preview-image-polyline" class="img-thumbnail"
                                width="400">
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Create Polygon -->
    <div class="modal fade" id="createpolygonModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Buat Laporan Area</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('polygons.store') }}" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Fill point name">
                        </div>


                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <!-- Kategori -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="lost">Kehilangan</option>
                                <option value="found">Temuan</option>
                            </select>
                        </div>

                        <!-- Geometry (akan diisi otomatis oleh JS) -->
                        <input type="hidden" name="geom_polygon" id="geom_polygon">
                        <p class="small text-muted">Klik peta untuk memilih area</p>

                        <div class="mb-3">
                            <label for="image_polygon" class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" id="image_polygon" name="image"
                                onchange="document.getElementById('preview-image-polygon').src = window.URL.createObjectURL(this.files[0])">
                            <img src="" alt="" id="preview-image-polygon" class="img-thumbnail mt-2"
                                width="200">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- 1. Leaflet core -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- 2. Leaflet.draw -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <!-- 3. jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- 4. Terraformer WKT -->
    <script src="https://unpkg.com/@terraformer/wkt"></script>

    <!-- 5. LEAFLET.AWESOMEMARKERS (JS) -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.js"></script>

    <script>
        console.log('🛠️ L.AwesomeMarkers:', L.AwesomeMarkers);
        // definisi ikon
        var lostIcon = L.AwesomeMarkers.icon({
            icon: 'question-circle',
            prefix: 'fa',
            markerColor: 'orange',
            iconColor: 'white'
        });
        var foundIcon = L.AwesomeMarkers.icon({
            icon: 'check-circle',
            prefix: 'fa',
            markerColor: 'green',
            iconColor: 'white'
        });

        var map = L.map('map').setView([-7.769845309213193, 110.37927389149422], 15.5);
        console.log('🚀 Map init start');
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        console.log('🗺️ Tile layer added');

        // Inisialisasi layer dasar
        var osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Daftar baseMaps untuk kontrol layer
        var baseMaps = {
            "OSM Standard": osmLayer
        };



        /* Digitize Function */
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
                position: 'topleft',
                polyline: true,
                polygon: true,
                rectangle: true,
                circle: true,
                marker: true,
                circlemarker: true
            },
            edit: false
        });

        map.addControl(drawControl);

        map.on('draw:created', function(e) {
            var type = e.layerType;
            var layer = e.layer;

            // Konversi layer ke GeoJSON, lalu ke WKT
            var drawnJSONObject = layer.toGeoJSON();
            var objectGeometry = Terraformer.geojsonToWKT(drawnJSONObject.geometry);

            // Isi input hidden dan tampilkan modal sesuai tipe gambar
            if (type === 'marker') {
                $('#geom_point').val(objectGeometry);
                $('#createpointModal').modal('show');

            } else if (type === 'polyline') {
                $('#geom_polyline').val(objectGeometry);
                $('#createpolylineModal').modal('show');

            } else if (type === 'polygon' || type === 'rectangle') {
                $('#geom_polygon').val(objectGeometry);
                $('#createpolygonModal').modal('show');
            }

            // Tambahkan elemen yang digambar ke layer group agar tetap terlihat
            drawnItems.addLayer(layer);
        });


        /* GeoJSON Point dengan Ikon & Filter Status */
        var point = L.geoJson(null, {
            filter: function(feature) {
                return feature.properties.status !== 'claimed';
            },
            pointToLayer: function(feature, latlng) {
                var icon = feature.properties.category === 'lost' ? lostIcon : foundIcon;
                return L.marker(latlng, {
                    icon: icon
                });
            },
            onEachFeature: function(feature, layer) {
                // generate route delete & edit
                var routedelete = "{{ route('points.destroy', ':id') }}".replace(':id', feature.properties.id);
                var routeedit = "{{ route('points.edit', ':id') }}".replace(':id', feature.properties.id);

                // siapkan isi popup
                var p = feature.properties;
                var popupContent =
                    "<strong>Nama:</strong> " + p.name + "<br>" +
                    "<strong>Deskripsi:</strong> " + p.description + "<br>" +
                    "<strong>Kategori:</strong> " + p.category + "<br>" +
                    "<strong>Dibuat:</strong> " + p.created_at + "<br>" +
                    "<img src='{{ asset('storage/images') }}/" + p.image +
                    "' width='250' alt='Foto Barang'><br>" +
                    "<em>Oleh: " + p.user_created + "</em>";

                // tombol Edit & Hapus
                var tombolHTML =
                    "<div class='d-flex justify-content-end mt-3'>" +
                    "<a href='" + routeedit + "' class='btn btn-warning btn-sm me-2'>" +
                    "<i class='fa-solid fa-pen-to-square'></i>" +
                    "</a>" +
                    "<form method='POST' action='" + routedelete +
                    "' onsubmit='return confirm(\"Yakin ingin menghapus?\")'>" +
                    "<input type='hidden' name='_token' value='{{ csrf_token() }}'>" +
                    "<input type='hidden' name='_method' value='DELETE'>" +
                    "<button type='submit' class='btn btn-danger btn-sm'>" +
                    "<i class='fa-solid fa-trash-can'></i>" +
                    "</button>" +
                    "</form>" +
                    "</div>";

                layer.bindPopup(popupContent + tombolHTML);
                layer.bindTooltip(p.name);
            }
        });

        // ambil data dan tampilkan
        $.getJSON("{{ route('api.points') }}", function(data) {
            point.addData(data);
            map.addLayer(point);
        });




        /* GeoJSON Polylines */
        var polyline = L.geoJson(null, {
            style: function(feature) {
                return {
                    color: "red", // Warna garis
                    weight: 4, // Ketebalan
                    opacity: 0.8 // Transparansi
                };
            },
            onEachFeature: function(feature, layer) {
                // generate route delete & edit
                var routedelete = "{{ route('polylines.destroy', ':id') }}";
                routedelete = routedelete.replace(':id', feature.properties.id);

                var routeedit = "{{ route('polylines.edit', ':id') }}";
                routeedit = routeedit.replace(':id', feature.properties.id);

                // isi popup yang udah ada
                var popupContent =
                    "Nama: " + feature.properties.name + "<br>" +
                    "Deskripsi: " + feature.properties.description + "<br>" +
                    "Dibuat: " + feature.properties.created_at + "<br>" +
                    "<img src='{{ asset('storage/images') }}/" + feature.properties.image +
                    "' width='200' alt='Foto Barang'><br>" +
                    "<p>Dibuat: " + feature.properties.user_created + "</p>";

                // bikin HTML tombol edit & hapus, pakai escape quote biar Blade token gak nge-crash JS
                var tombolHTML =
                    "<div class='row mt-4'>" +
                    "<div class='col-6 text-end'>" +
                    "<a href='" + routeedit +
                    "' class='btn btn-warning btn-sm'><i class='fa-solid fa-pen-to-square'></i></a>" +
                    "</div>" +
                    "<div class='col-6'>" +
                    "<form method='POST' action='" + routedelete + "'>" +
                    "<input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token() }}\">" +
                    "<input type=\"hidden\" name=\"_method\" value=\"DELETE\">" +
                    "<button type='submit' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure to delete?\")'>" +
                    "<i class='fa-solid fa-trash-can'></i>" +
                    "</button>" +
                    "</form>" +
                    "</div>" +
                    "</div>";

                layer.on({
                    click: function(e) {
                        // gabungkan popupContent + tombolHTML, lalu bind ke layer
                        layer.bindPopup(popupContent + tombolHTML);
                    },
                    mouseover: function(e) {
                        layer.bindTooltip(feature.properties.name);
                    }
                });
            },
        });


        $.getJSON("{{ route('api.polylines') }}", function(data) {
            polyline.addData(data);
            map.addLayer(polyline);
        });




        /* GeoJSON Polygons */
        var polygon = L.geoJson(null, {
            style: function(feature) {
                return {
                    color: "#2D336B", // Warna garis tepi
                    fillColor: "#FBE4D6", // Warna isi
                    weight: 2, // Ketebalan garis
                    opacity: 1, // Transparansi garis
                    fillOpacity: 0.5 // Transparansi isi
                };
            },
            onEachFeature: function(feature, layer) {

                // generate route delete & edit
                var routedelete = "{{ route('polygons.destroy', ':id') }}";
                routedelete = routedelete.replace(':id', feature.properties.id);

                var routeedit = "{{ route('polygons.edit', ':id') }}";
                routeedit = routeedit.replace(':id', feature.properties.id);

                // isi popup yang udah ada
                var popupContent =
                    "Nama: " + feature.properties.name + "<br>" +
                    "Deskripsi: " + feature.properties.description + "<br>" +
                    "Dibuat: " + feature.properties.created_at + "<br>" +
                    "<img src='{{ asset('storage/images') }}/" + feature.properties.image +
                    "' width='200' alt='Foto Barang'><br>" +
                    "<p>Dibuat: " + feature.properties.user_created + "</p>";

                // Bikin HTML tombol edit & hapus, pake escape biar Blade token aman
                var tombolHTML =
                    "<div class='row mt-4'>" +
                    "<div class='col-6 text-end'>" +
                    "<a href='" + routeedit +
                    "' class='btn btn-warning btn-sm'><i class='fa-solid fa-pen-to-square'></i></a>" +
                    "</div>" +
                    "<div class='col-6'>" +
                    "<form method='POST' action='" + routedelete +
                    "' onsubmit='return confirm(\"Are you sure to delete?\")'>" +
                    "<input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token() }}\">" +
                    "<input type=\"hidden\" name=\"_method\" value=\"DELETE\">" +
                    "<button type='submit' class='btn btn-danger btn-sm'>" +
                    "<i class='fa-solid fa-trash-can'></i>" +
                    "</button>" +
                    "</form>" +
                    "</div>" +
                    "</div>";

                // Gabungin isi popup + tombol, lalu bind ke layer
                layer.bindPopup(popupContent + tombolHTML);
            },
        });


        $.getJSON("{{ route('api.polygons') }}", function(data) {
            polygon.addData(data);
            map.addLayer(polygon);
        });


        // Overlay layers (data GeoJSON)
        var overlayMaps = {
            "Points": point,
            "Polylines": polyline,
            "Polygons": polygon
        };

        // Add layer control to map
        L.control.layers(baseMaps, overlayMaps, {
            position: 'bottomright',
            collapsed: false
        }).addTo(map);
    </script>
@endsection
