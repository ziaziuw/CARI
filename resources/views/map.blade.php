@extends('layout.template')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

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
        }

        /* Modal Z-Index Fix */
        #claim-modal,
        #createpointModal {
            z-index: 10050 !important;
        }

        .modal-backdrop {
            z-index: 10040 !important;
        }

        .leaflet-control,
        .leaflet-top,
        .leaflet-bottom {
            z-index: 1000 !important;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        /* Image preview styles */
        .image-preview {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .image-error {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <div id="map"></div>

    <!-- Panggil komponen Livewire untuk claim-item-form -->
    @livewire('claim-item-form')

    <!-- Legend -->
    <div id="legend" class="position-absolute top-0 end-0 m-3 bg-white p-2 border rounded shadow">
        <div class="d-flex align-items-center mb-1">
            <i class="fa fa-question-circle text-warning me-2"></i>
            <span>Kehilangan</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="fa fa-check-circle text-success me-2"></i>
            <span>Temuan</span>
        </div>
    </div>

    <!-- Modal Create Point-->
    <div class="modal fade" id="createpointModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Laporan Titik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('points.store') }}" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="geom_point" id="geom_point">

                        <!-- Nama Barang -->
                        <div class="mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input name="name" class="form-control" required placeholder="Masukkan nama barang">
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" required
                                placeholder="Deskripsikan barang secara detail"></textarea>
                        </div>

                        <!-- Kategori -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="lost">Kehilangan</option>
                                <option value="found">Temuan</option>
                            </select>
                        </div>

                        <!-- Foto -->
                        <div class="mb-3">
                            <label for="image_point" class="form-label">Foto Barang</label>
                            <input type="file" class="form-control" id="image_point" name="image" accept="image/*"
                                onchange="previewImage(this, 'preview-image_point')">
                            <div class="mt-2">
                                <img src="" alt="" id="preview-image_point" class="image-preview d-none">
                                <div id="no-image-placeholder" class="image-error">
                                    <i class="fa fa-image fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada foto dipilih</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Lokasi telah dipilih di peta
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@terraformer/wkt"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.js"></script>

    <script>
        // ================= HELPER FUNCTIONS =================

        // Fungsi untuk preview gambar di modal create
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById('no-image-placeholder');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    if (placeholder) placeholder.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('d-none');
                preview.src = '';
                if (placeholder) placeholder.style.display = 'block';
            }
        }

        // Fungsi untuk membuat URL gambar yang benar
        function getImageUrl(imagePath) {
            if (!imagePath || imagePath.trim() === '') {
                return null;
            }

            // Debug log
            console.log('Processing image path:', imagePath);

            // Jika sudah URL lengkap
            if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
                return imagePath;
            }

            // Jika dalam format storage/images/filename
            if (imagePath.startsWith('storage/images/')) {
                return "{{ url('/') }}/" + imagePath;
            }

            // Jika dalam format images/filename
            if (imagePath.startsWith('images/')) {
                return "{{ url('storage/public') }}/" + imagePath;
            }

            // Jika hanya nama file (default case)
            const finalUrl = "{{ url('storage/public/images') }}/" + imagePath;
            console.log('Generated URL:', finalUrl);
            return finalUrl;
        }

        // Fungsi untuk format tanggal Indonesia
        function formatIndonesianDate(dateString) {
            const date = new Date(dateString);
            const formattedDate = date.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const formattedTime = date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            return {
                date: formattedDate,
                time: formattedTime
            };
        }

        // ================= MAP INITIALIZATION =================

        console.log('🛠 L.AwesomeMarkers:', L.AwesomeMarkers);

        // Definisi ikon
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

        // Inisialisasi peta
        var map = L.map('map').setView([-7.769845309213193, 110.37927389149422], 15.5);
        console.log('🚀 Map initialized');

        // Tambahkan tile layer
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        console.log('🗺 Tile layer added');

        // Layer untuk marker yang dapat diklik
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // ================= MAP EVENTS =================

        // Event click untuk menambah marker
        map.on('click', function(e) {
            console.log('Map clicked at:', e.latlng);

            // Hapus marker sebelumnya jika ada
            drawnItems.clearLayers();

            // Buat marker baru
            var marker = L.marker(e.latlng, {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<i class="fa fa-map-marker-alt text-danger" style="font-size: 24px;"></i>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                })
            }).addTo(drawnItems);

            // Konversi ke WKT
            var point = {
                type: "Point",
                coordinates: [e.latlng.lng, e.latlng.lat]
            };
            var wkt = Terraformer.geojsonToWKT(point);

            // Set value ke input hidden
            $('#geom_point').val(wkt);
            console.log('WKT set:', wkt);

            // Tampilkan modal
            $('#createpointModal').modal('show');
        });

        // ================= POINT LAYER CONFIGURATION =================

        var pointLayer = L.geoJson(null, {
            filter: function(feature) {
                // Filter hanya point yang belum di-claim
                return feature.properties.status !== 'claimed';
            },
            pointToLayer: function(feature, latlng) {
                // Pilih ikon berdasarkan kategori
                var icon = feature.properties.category === 'lost' ? lostIcon : foundIcon;
                return L.marker(latlng, {
                    icon: icon
                });
            },
            onEachFeature: function(feature, layer) {
                var p = feature.properties;
                console.log('Processing point:', p);

                // Format tanggal
                var dateTime = formatIndonesianDate(p.created_at);

                // Generate routes
                var routeDelete = "{{ route('points.destroy', ':id') }}".replace(':id', p.id);
                var routeEdit = "{{ route('points.edit', ':id') }}".replace(':id', p.id);

                // Generate image HTML
                var imageHtml = generateImageHtml(p.image);

                // Generate popup content
                var popupContent = `

                    <div style="max-width: 280px; min-width: 250px;">
                        <div class="card border-0">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fa fa-${p.category === 'lost' ? 'question-circle' : 'check-circle'} me-2"></i>
                                    ${p.name}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <strong>Deskripsi:</strong>
                                    <p class="mb-2 text-muted">${p.description}</p>
                                </div>

                                <div class="mb-2">
                                    <strong>Kategori:</strong>
                                    <span class="badge bg-${p.category === 'lost' ? 'warning' : 'success'} ms-1">
                                        ${p.category === 'lost' ? 'Kehilangan' : 'Temuan'}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <strong>Dibuat:</strong>
                                    <small class="text-muted d-block">${dateTime.date}</small>
                                    <small class="text-muted">Pukul ${dateTime.time}</small>
                                </div>

                                ${imageHtml}

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fa fa-user me-1"></i>
                                        Oleh: ${p.user_created || 'Unknown'}
                                    </small>
                                </div>

                                <!-- Action buttons -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm"
                                        onclick="openClaimModal({id: ${p.id}, type: 'App\\Models\\PointsModel', name: '${p.name.replace(/'/g, "\\'")}'})">
                                        <i class="fa-solid fa-hand-holding-heart me-1"></i> Klaim Item
                                    </button>

                                    <div class="d-flex gap-2">
                                        <a href="${routeEdit}" class="btn btn-warning btn-sm flex-fill">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                        </a>
                                        <form method="POST" action="${routeDelete}" class="flex-fill"
                                            onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                <i class="fa-solid fa-trash-can me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Bind popup dan tooltip
                layer.bindPopup(popupContent, {
                    maxWidth: 300,
                    className: 'custom-popup'
                });
                layer.bindTooltip(p.name, {
                    direction: 'top',
                    offset: [0, -10]
                });
            }
        });

        // Fungsi untuk generate HTML gambar
        function generateImageHtml(imagePath) {
            if (!imagePath || imagePath.trim() === '') {
                return `
                    <div class="mb-3">
                        <div class="image-error text-center py-3">
                            <i class="fa fa-image fa-2x text-muted mb-2"></i>
                            <p class="mb-0 small text-muted">Tidak ada foto</p>
                        </div>
                    </div>
                `;
            }

            var imageUrl = getImageUrl(imagePath);

            return `
                <div class="mb-3">
                    <strong>Foto:</strong>
                    <div class="mt-2">
                        <img src="${imageUrl}"
                            alt="Foto ${imagePath}"
                            class="image-preview w-100"
                            style="cursor: pointer;"
                            onclick="window.open('${imageUrl}', '_blank')"
                            onerror="handleImageError(this, '${imagePath}')"
                            onload="console.log('✅ Image loaded:', this.src);">
                    </div>
                </div>
            `;
        }

        // Handle image error
        function handleImageError(img, originalPath) {
            console.error('❌ Failed to load image:', img.src);
            console.log('Original path:', originalPath);

            img.style.display = 'none';
            img.insertAdjacentHTML('afterend', `
                <div class="image-error text-center py-3">
                    <i class="fa fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                    <p class="mb-1 small">Foto tidak dapat dimuat</p>
                    <small class="text-muted">${originalPath}</small>
                </div>
            `);
        }

        // ================= DATA LOADING =================

        // Load dan tampilkan data points
        function loadPoints() {
            console.log('Loading points from API...');

            $.getJSON("{{ route('api.points') }}")
                .done(function(data) {
                    console.log('✅ Points loaded:', data.length, 'items');
                    pointLayer.clearLayers();
                    pointLayer.addData(data);
                    if (!map.hasLayer(pointLayer)) {
                        map.addLayer(pointLayer);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('❌ Failed to load points:', textStatus, errorThrown);
                    alert('Gagal memuat data peta. Silakan refresh halaman.');
                });
        }

        // Load points saat halaman dimuat
        loadPoints();

        // ================= // ================= LIVEWIRE INTEGRATION (COMPLETELY FIXED) =================

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up Livewire integration...');

            // Update the waitForLivewire function (around line 520-570)
            function waitForLivewire(callback, maxAttempts = 50) {
                let attempts = 0;
                
                function check() {
                    attempts++;
                    
                    if (window.Livewire && document.querySelector('[wire\\:id], [data-livewire-id]')) {
                        // Set up component references
                        const claimForm = document.querySelector('[wire\\:id*="claim"], [data-livewire-id*="claim"]') || 
                                         document.querySelector('.claim-item-form') ||
                                         document.querySelector('[data-component-name="claim-item-form"]');
                        
                        if (claimForm) {
                            window.claimComponent = claimForm;
                            // For Livewire v3, get the component instance
                            window.claimLivewire = window.Livewire.find(claimForm.getAttribute('wire:id') || claimForm.getAttribute('data-livewire-id'));
                        }
                        
                        callback();
                    } else if (attempts < maxAttempts) {
                        setTimeout(check, 100);
                    } else {
                        console.error('❌ Livewire failed to load after', maxAttempts, 'attempts');
                    }
                }
                
                check();
            }

            waitForLivewire(function() {
                // IMPROVED: Fungsi untuk membuka modal klaim dengan multiple fallback methods
                window.openClaimModal = function(itemData) {
                    console.log('🎯 Opening claim modal with data:', itemData);
                    
                    // Find the Livewire component
                    const claimForm = document.querySelector('[wire\\:id], [data-livewire-id]');
                    
                    if (claimForm && window.Livewire) {
                        const componentId = claimForm.getAttribute('wire:id') || claimForm.getAttribute('data-livewire-id');
                        const component = window.Livewire.find(componentId);
                        
                        if (component) {
                            // Use Livewire v3 syntax
                            component.call('handleSetItemV3', itemData)
                                .then(() => {
                                    // Open the modal
                                    const modal = new bootstrap.Modal(document.getElementById('claim-modal'));
                                    modal.show();
                                })
                                .catch(error => {
                                    console.error('❌ Failed to set item data:', error);
                                });
                        } else {
                            console.error('❌ Livewire component not found');
                        }
                    } else {
                        console.error('❌ Livewire not available');
                    }
                };

                // Enhanced event listeners
                const eventMethods = ['on', 'addEventListener'];
                eventMethods.forEach(method => {
                    if (window.Livewire[method]) {
                        try {
                            window.Livewire[method]('close-claim-modal', function() {
                                console.log('🔄 Closing claim modal...');
                                const modal = document.getElementById('claim-modal');
                                if (modal) {
                                    const bootstrapModal = bootstrap.Modal.getInstance(
                                        modal);
                                    if (bootstrapModal) {
                                        bootstrapModal.hide();
                                    }
                                }
                            });

                            window.Livewire[method]('claim-success', function() {
                                console.log('✅ Claim successful, refreshing map...');
                                const modal = document.getElementById('claim-modal');
                                if (modal) {
                                    const bootstrapModal = bootstrap.Modal.getInstance(
                                        modal);
                                    if (bootstrapModal) {
                                        bootstrapModal.hide();
                                    }
                                }

                                // Refresh map data
                                setTimeout(() => {
                                    if (typeof loadPoints === 'function') {
                                        loadPoints();
                                    }
                                }, 1000);
                            });

                            window.Livewire[method]('item-data-set', function(data) {
                                console.log('✅ Item data confirmed set:', data);
                            });

                            console.log(`✅ Event listeners registered via ${method}`);
                        } catch (error) {
                            console.error(`❌ Failed to register events via ${method}:`, error);
                        }
                    }
                });

                // Additional debugging
                console.log('=== LIVEWIRE DEBUG INFO ===');
                console.log('Livewire object:', window.Livewire);
                console.log('Available methods:', Object.keys(window.Livewire || {}));

                const components = document.querySelectorAll('[wire\\:id], [data-livewire-id]');
                console.log('Found components:', components.length);
                components.forEach((el, i) => {
                    console.log(`Component ${i}:`, {
                        element: el,
                        wireId: el.getAttribute('wire:id'),
                        dataLivewireId: el.getAttribute('data-livewire-id'),
                        hasLivewire: !!el.__livewire,
                        hasAlpine: !!el._x_dataStack
                    });
                });
            });
        });

        // Enhanced test function
        window.testLivewireConnection = function() {
            console.log('=== ENHANCED LIVEWIRE TEST ===');

            // Test with sample data
            const testData = {
                id: 999,
                type: 'App\\Models\\PointsModel',
                name: 'Test Item from JS'
            };

            console.log('Testing with data:', testData);

            // Call the openClaimModal function
            if (window.openClaimModal) {
                window.openClaimModal(testData.id, testData.type, testData.name);
            } else {
                console.error('❌ openClaimModal function not available');
            }
        };

        // Auto-refresh function to periodically check Livewire status
        function checkLivewireStatus() {
            const components = document.querySelectorAll('[wire\\:id], [data-livewire-id]');
            console.log(`🔍 Status check: Found ${components.length} Livewire components`);

            components.forEach((el, i) => {
                const hasLivewire = !!el.__livewire;
                const wireId = el.getAttribute('wire:id') || el.getAttribute('data-livewire-id');
                console.log(`Component ${i}: ID=${wireId}, Connected=${hasLivewire}`);
            });
        }

        // Check status every 10 seconds in debug mode
        if (window.location.search.includes('debug=1')) {
            setInterval(checkLivewireStatus, 10000);
        }

        // ================= MODAL EVENTS =================

        // Reset form ketika modal ditutup
        $('#createpointModal').on('hidden.bs.modal', function() {
            console.log('Create point modal closed, cleaning up...');

            // Clear form
            this.querySelector('form').reset();

            // Clear image preview
            const preview = document.getElementById('preview-image_point');
            const placeholder = document.getElementById('no-image-placeholder');

            if (preview) {
                preview.src = '';
                preview.classList.add('d-none');
            }
            if (placeholder) {
                placeholder.style.display = 'block';
            }

            // Clear drawn items
            drawnItems.clearLayers();

            // Clear WKT input
            $('#geom_point').val('');
        });
    </script>
@endpush
