@extends('layout.template')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">
    <!-- Leaflet.AwesomeMarkers CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.css" />

    <style>
        .edit-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .map-container {
            position: relative;
            width: 100%;
            max-width: 100%;
            height: 500px;
            max-height: 500px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        #map {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }
        
        .map-instructions {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #1565c0;
        }
        
        .leaflet-control {
            z-index: 1000 !important;
        }
        
        .image-preview {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@section('content')
<div class="edit-container">
    <div class="form-card">
        <h2 class="mb-4">
            <i class="fa fa-edit me-2"></i>
            Edit Point: {{ $point->name }}
        </h2>
        
        <form method="POST" action="{{ route('points.update', $point->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ $point->name }}" placeholder="Fill point name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required>{{ $point->description }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="lost" {{ $point->category == 'lost' ? 'selected' : '' }}>Kehilangan</option>
                            <option value="found" {{ $point->category == 'found' ? 'selected' : '' }}>Temuan</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="image" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="image_point" name="image"
                               onchange="previewImage(this)">
                        @if($point->image)
                            <img src="{{ asset('storage/public/images/' . $point->image) }}" alt="Current image" 
                                 id="preview-image-point" class="image-preview mt-2">
                        @else
                            <img src="" alt="" id="preview-image-point" class="image-preview mt-2" style="display: none;">
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="geom_point" class="form-label">Geometry (WKT)</label>
                <textarea class="form-control" id="geom_point" name="geom_point" rows="2" readonly>{{ $point->geom_point }}</textarea>
                <small class="text-muted">Geometry akan diupdate otomatis saat Anda menggeser marker di peta</small>
            </div>
            
            <div class="map-instructions">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Petunjuk:</strong> Klik dan geser marker merah di peta untuk mengubah lokasi point. Geometry akan terupdate otomatis.
            </div>
            
            <div class="map-container">
                <div id="map"></div>
            </div>
            
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-2"></i>Update Point
                </button>
                <a href="{{ route('map') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Back to Map
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/@terraformer/wkt"></script>
    <!-- Leaflet.AwesomeMarkers JS -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.min.js"></script>

    <script>
        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('preview-image-point');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function() {
            console.log('🚀 Initializing edit point map...');
            
            // Definisi ikon seperti di map.blade.php
            var editIcon = L.AwesomeMarkers.icon({
                icon: 'edit',
                prefix: 'fa',
                markerColor: 'red',
                iconColor: 'white'
            });
            
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
            
            // Inisialisasi peta dengan koordinat yang valid
            var map = L.map('map').setView([-7.769845309213193, 110.37927389149422], 15);
            
            // Tambahkan tile layer
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Layer untuk marker yang dapat diedit
            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            
            // Variable untuk menyimpan marker yang sedang diedit
            var currentMarker = null;
            
            // Fungsi untuk update WKT geometry
            function updateGeometry(latlng) {
                var point = {
                    type: "Point",
                    coordinates: [latlng.lng, latlng.lat]
                };
                var wkt = Terraformer.geojsonToWKT(point);
                $('#geom_point').val(wkt);
                console.log('Geometry updated:', wkt);
            }
            
            // Load dan tampilkan point yang sedang diedit
            $.getJSON("{{ route('api.point', $point->id) }}", function(data) {
                console.log('📍 Loading point data:', data);
                
                // Perbaikan: Handle FeatureCollection format
                var pointData = null;
                if (data && data.features && data.features.length > 0) {
                    pointData = data.features[0];
                } else if (data && data.geometry) {
                    pointData = data;
                } else {
                    console.error('❌ Invalid point data structure:', data);
                    alert('Error: Invalid point data structure received.');
                    return;
                }
                
                if (pointData && pointData.geometry && pointData.geometry.coordinates) {
                    var coords = pointData.geometry.coordinates;
                    var latlng = L.latLng(coords[1], coords[0]);
                    
                    // Pilih ikon berdasarkan kategori
                    var icon = pointData.properties.category === 'lost' ? lostIcon : foundIcon;
                    
                    // Buat marker yang dapat di-drag
                    currentMarker = L.marker(latlng, {
                        icon: editIcon,
                        draggable: true
                    }).addTo(drawnItems);
                    
                    // Popup dengan informasi point
                    var popupContent = `
                        <div style="min-width: 200px;">
                            <h6><i class="fa fa-edit me-2"></i>Editing: ${pointData.properties.name}</h6>
                            <p class="mb-1"><strong>Category:</strong> 
                                <span class="badge bg-${pointData.properties.category === 'lost' ? 'warning' : 'success'}">
                                    ${pointData.properties.category === 'lost' ? 'Kehilangan' : 'Temuan'}
                                </span>
                            </p>
                            <p class="mb-0 text-muted">Drag marker untuk mengubah lokasi</p>
                        </div>
                    `;
                    
                    currentMarker.bindPopup(popupContent).openPopup();
                    
                    // Event listener untuk drag
                    currentMarker.on('dragend', function(e) {
                        var newLatLng = e.target.getLatLng();
                        updateGeometry(newLatLng);
                        console.log('Marker dragged to:', newLatLng);
                    });
                    
                    // Center map pada point
                    map.setView(latlng, 16);
                    
                    // Update geometry field dengan posisi awal
                    updateGeometry(latlng);
                    
                    console.log('✅ Point loaded successfully:', pointData.properties.name);
                } else {
                    console.error('❌ Invalid point geometry data');
                    alert('Error: Point geometry data is missing or invalid.');
                }
            }).fail(function(xhr, status, error) {
                console.error('❌ Failed to load point data:', error);
                alert('Failed to load point data: ' + error);
            });
            
            // Event click pada map untuk memindahkan marker
            map.on('click', function(e) {
                if (currentMarker) {
                    currentMarker.setLatLng(e.latlng);
                    updateGeometry(e.latlng);
                    console.log('Marker moved to:', e.latlng);
                }
            });
            
            // Resize map setelah container siap
            setTimeout(function() {
                map.invalidateSize();
                console.log('✅ Map resized and ready');
            }, 100);
        });
    </script>
@endpush