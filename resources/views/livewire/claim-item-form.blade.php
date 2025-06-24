{{-- Improved claim-item-form.blade.php --}}
<div wire:id="claim-item-form-{{ $this->getId() }}"
     data-component-name="claim-item-form"
     class="claim-item-form-wrapper">

    <!-- Modal dengan ID yang konsisten -->
    <div class="modal fade" id="claim-modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Klaim Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session('claim_success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>
                            {{ session('claim_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('claim_error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            {{ session('claim_error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <!-- Informasi item yang akan diklaim -->
                        @if($itemName)
                            <div class="alert alert-primary border-primary">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Item yang akan diklaim:</strong>
                                        <div class="fw-bold text-black">{{ $itemName }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning border-warning">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong>Item belum dipilih.</strong>
                                        <div class="small">Silakan tutup modal ini dan klik tombol "Klaim Item" pada marker di peta.</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Form input alasan -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fa fa-comment-dots me-1"></i>
                                Alasan Klaim <span class="text-danger">*</span>
                            </label>
                            <textarea wire:model.live="reason"
                                class="form-control @error('reason') is-invalid @enderror"
                                rows="4"
                                placeholder="Jelaskan secara detail mengapa Anda yakin ini adalah barang Anda atau barang yang Anda temukan. Sebutkan ciri-ciri khusus, lokasi kehilangan, atau informasi lain"
                                required
                                maxlength="500"></textarea>

                            <!-- Character counter -->
                            <div class="d-flex justify-content-between mt-1">
                                @error('reason')
                                    <span class="text-danger small">{{ $message }}</span>
                                @else
                                    <span></span>
                                @enderror
                                <small class="text-muted">
                                    <span wire:ignore>{{ strlen($reason ?? '') }}</span>/500 karakter
                                </small>
                            </div>
                        </div>

                        <!-- Form input foto -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fa fa-camera me-1"></i>
                                Foto Bukti (Opsional)
                            </label>
                            <input type="file"
                                wire:model="image"
                                class="form-control @error('image') is-invalid @enderror"
                                accept="image/*"
                                id="claim-image-input">

                            <div class="form-text">
                                <i class="fa fa-info-circle me-1"></i>
                                Upload foto sebagai bukti kepemilikan (maks. 10MB, format: JPG, PNG, GIF, WebP)
                            </div>

                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Image upload status -->
                            @if ($image)
                                <div class="mt-2 p-2 bg-light rounded">
                                    <div class="d-flex align-items-center text-success">
                                        <i class="fa fa-check-circle me-2"></i>
                                        <div>
                                            <strong>File terpilih:</strong> {{ $image->getClientOriginalName() }}
                                            <br>
                                            <small class="text-muted">
                                                Ukuran: {{ number_format($image->getSize() / 1024, 1) }} KB
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Loading indicator untuk upload -->
                            <div wire:loading wire:target="image" class="mt-2">
                                <div class="d-flex align-items-center text-primary">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <small>Mengupload gambar...</small>
                                </div>
                            </div>
                        </div>

                        <!-- Validasi status sebelum submit -->
                        @if(!$itemId || !$itemType)
                            <div class="alert alert-warning border-warning mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong>Data item belum tersedia.</strong>
                                        <div class="small">Silakan tutup modal dan klik tombol "Klaim Item" pada marker di peta.</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Login check -->
                        @guest
                            <div class="alert alert-danger border-danger mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-user-times me-2"></i>
                                    <div>
                                        <strong>Anda harus login terlebih dahulu.</strong>
                                        <div class="small">
                                            <a href="{{ route('login') }}" class="text-decoration-none">Klik di sini untuk login</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endguest

                        <!-- Form buttons -->
                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa fa-times me-1"></i>Batal
                            </button>

                            <button type="submit"
                                class="btn btn-primary position-relative"
                                @if(!$itemId || !$itemType || !auth()->check()) disabled @endif>

                                <span wire:loading.remove wire:target="submit">
                                    <i class="fa fa-hand-holding-heart me-1"></i>Kirim Klaim
                                </span>

                                <span wire:loading wire:target="submit" class="d-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Mengirim Klaim...
                                </span>
                            </button>
                        </div>

                        <!-- Additional info -->
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fa fa-shield-alt me-1"></i>
                                Klaim Anda akan diverifikasi oleh admin sebelum diproses
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript untuk debugging dan integration -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Claim Item Form loaded');

            // Initialize component reference
            window.claimComponent = null;

            // Find and register component
            function findClaimComponent() {
                const selectors = [
                    '[wire\\:id*="claim-item-form"]',
                    '[data-component-name="claim-item-form"]',
                    '.claim-item-form-wrapper'
                ];

                for (const selector of selectors) {
                    const element = document.querySelector(selector);
                    if (element) {
                        console.log('✅ Claim component found:', selector);

                        // Store reference for global access
                        window.claimComponent = element;

                        // Try to get Livewire instance
                        if (element.__livewire) {
                            window.claimLivewire = element.__livewire;
                            console.log('✅ Livewire instance attached');
                        }

                        return element;
                    }
                }

                console.error('❌ Claim component not found');
                return null;
            }

            // Initialize component
            const component = findClaimComponent();

            if (component) {
                // Modal event handlers
                const modal = document.getElementById('claim-modal');
                if (modal) {
                    modal.addEventListener('shown.bs.modal', function() {
                        console.log('📢 Claim modal opened');
                        // Focus pada textarea
                        const textarea = modal.querySelector('textarea[wire\\:model*="reason"]');
                        if (textarea) {
                            textarea.focus();
                        }
                    });

                    modal.addEventListener('hidden.bs.modal', function() {
                        console.log('📢 Claim modal closed');
                        // Reset form via Livewire if available
                        if (window.claimLivewire && window.claimLivewire.call) {
                            window.claimLivewire.call('resetForm');
                        }
                    });
                }
            }

            // Auto-close success modal
            @if (session('claim_success'))
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('claim-modal'));
                    if (modal) {
                        modal.hide();

                        // Reload map data if function exists
                        if (typeof loadPoints === 'function') {
                            setTimeout(loadPoints, 500);
                        }
                    }
                }, 3000);
            @endif
        });

        // Enhanced testing functions for debug mode
        @if(config('app.debug'))

            // Refresh debug info
            window.refreshDebugInfo = function() {
                console.log('🔄 Refreshing debug info...');

                if (window.claimLivewire && window.claimLivewire.call) {
                    window.claimLivewire.call('debugInfo').then(info => {
                        console.log('Debug info received:', info);

                        // Update debug display
                        const debugInfo = document.getElementById('debug-info');
                        if (debugInfo && info) {
                            document.getElementById('debug-item-id').textContent = `Item ID: ${info.item_data?.itemId || 'Belum di-set'}`;
                            document.getElementById('debug-item-type').textContent = `Item Type: ${info.item_data?.itemType || 'Belum di-set'}`;
                            document.getElementById('debug-item-name').textContent = `Item Name: ${info.item_data?.itemName || 'Belum di-set'}`;
                            document.getElementById('debug-timestamp').textContent = new Date().toLocaleTimeString();

                            const status = info.validation_status ? 'Ready' : 'Waiting for data';
                            const statusClass = info.validation_status ? 'bg-success' : 'bg-warning';
                            const statusElement = document.getElementById('debug-status');
                            statusElement.textContent = status;
                            statusElement.className = `badge ${statusClass}`;
                        }
                    }).catch(error => {
                        console.error('❌ Failed to refresh debug info:', error);
                    });
                } else {
                    console.error('❌ Livewire component not available');
                }
            };

            // Test SetItem method directly
            window.testSetItemMethod = function() {
                console.log('🧪 Testing setItem method...');

                const testData = {
                    id: 999,
                    type: 'App\\Models\\PointsModel',
                    name: 'Test Item from Debug'
                };

                if (window.claimLivewire && window.claimLivewire.call) {
                    window.claimLivewire.call('setItem', testData.id, testData.type, testData.name)
                        .then(result => {
                            console.log('✅ setItem method successful:', result);
                            refreshDebugInfo();
                        })
                        .catch(error => {
                            console.error('❌ setItem method failed:', error);
                        });
                } else {
                    console.error('❌ Livewire component not available');
                }
            };

            // Test event dispatch
            // Replace the testEventDispatch function (around line 370-400)
            window.testEventDispatch = function() {
                console.log('🧪 Testing event dispatch...');
            
                const testData = {
                    id: 888,
                    type: 'App\\Models\\PointsModel',
                    name: 'Test Event Item'
                };
            
                // Try multiple event methods for Livewire v3
                const methods = [
                    () => {
                        // Livewire v3 dispatch method
                        if (window.Livewire) {
                            window.Livewire.dispatch('set-item', testData);
                        }
                    },
                    () => {
                        // Direct component call if available
                        if (window.claimLivewire && window.claimLivewire.call) {
                            window.claimLivewire.call('handleSetItemV3', testData);
                        }
                    },
                    () => {
                        // Custom event dispatch
                        window.dispatchEvent(new CustomEvent('livewire:dispatch', {
                            detail: { name: 'set-item', payload: testData }
                        }));
                    }
                ];
            
                methods.forEach((method, index) => {
                    try {
                        method();
                        console.log(`✅ Event method ${index + 1} executed`);
                    } catch (error) {
                        console.error(`❌ Event method ${index + 1} failed:`, error);
                    }
                });
            
                setTimeout(refreshDebugInfo, 500);
            };

            // Show comprehensive component debug info
            window.showComponentDebug = function() {
                console.log('🐛 Comprehensive component debug:');

                const component = window.claimComponent;
                console.log('Component element:', component);
                console.log('Livewire instance:', window.claimLivewire);
                console.log('Available Livewire methods:', window.Livewire ? Object.keys(window.Livewire) : 'Not available');

                if (component) {
                    console.log('Component attributes:', {
                        wireId: component.getAttribute('wire:id'),
                        dataLivewireId: component.getAttribute('data-livewire-id'),
                        componentName: component.getAttribute('data-component-name'),
                        hasLivewire: !!component.__livewire,
                        hasAlpine: !!component._x_dataStack
                    });
                }

                // Test component accessibility
                const allComponents = document.querySelectorAll('[wire\\:id], [data-livewire-id]');
                console.log(`Found ${allComponents.length} Livewire components total:`, allComponents);

                // Display in alert for user
                alert(`Debug Info:\n- Component Found: ${!!component}\n- Livewire Instance: ${!!window.claimLivewire}\n- Total Components: ${allComponents.length}\n\nCheck console for detailed info.`);
            };

        @endif

        // Enhanced global test function
        window.testClaimComponent = function() {
            console.log('=== ENHANCED CLAIM COMPONENT TEST ===');

            const tests = [
                {
                    name: 'Component Element',
                    test: () => !!window.claimComponent,
                    details: () => window.claimComponent ? 'Found' : 'Not found'
                },
                {
                    name: 'Livewire Instance',
                    test: () => !!window.claimLivewire,
                    details: () => window.claimLivewire ? 'Connected' : 'Not connected'
                },
                {
                    name: 'Modal Element',
                    test: () => !!document.getElementById('claim-modal'),
                    details: () => document.getElementById('claim-modal') ? 'Found' : 'Not found'
                },
                {
                    name: 'Bootstrap Modal',
                    test: () => typeof bootstrap !== 'undefined' && !!bootstrap.Modal,
                    details: () => typeof bootstrap !== 'undefined' ? 'Available' : 'Not available'
                }
            ];

            console.table(tests.map(test => ({
                Test: test.name,
                Status: test.test() ? '✅ PASS' : '❌ FAIL',
                Details: test.details()
            })));

            // Try opening modal
            try {
                const modal = new bootstrap.Modal(document.getElementById('claim-modal'));
                modal.show();
                console.log('✅ Modal opened successfully');
            } catch (error) {
                console.error('❌ Failed to open modal:', error);
            }
        };
    </script>
</div>
