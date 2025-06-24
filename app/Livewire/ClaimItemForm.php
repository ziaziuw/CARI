<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Claim;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ClaimItemForm extends Component
{
    use WithFileUploads;

    public $itemId;
    public $itemType;
    public $itemName;
    public $reason;
    public $image;

    // IMPROVED: Multiple event listeners untuk kompatibilitas

    // Untuk Livewire v3 dengan Attributes
    #[On('set-item')]
    public function handleSetItemV3($data = null)
    {
        Log::info('🎯 Menerima event set-item via Attributes (v3)', ['data' => $data]);
        $this->processItemData($data);
    }

    // Untuk Livewire v2 compatibility
    protected $listeners = [
        'set-item' => 'handleSetItemV2',
        'setItem' => 'setItem',
        'item-selected' => 'handleItemSelected'
    ];

    public function handleSetItemV2($data = null)
    {
        Log::info('🎯 Menerima event set-item via listeners (v2)', $data);
        $this->processItemData($data);
    }

    public function handleItemSelected($data = null)
    {
        Log::info('🎯 Menerima event item-selected', $data);
        $this->processItemData($data);
    }

    // IMPROVED: Method untuk memproses data dari berbagai sumber
    protected function processItemData($data = null)
    {
        try {
            // Handle berbagai format data
            if (is_array($data)) {
                $this->itemId = $data['id'] ?? $data['itemId'] ?? null;
                $this->itemType = $data['type'] ?? $data['itemType'] ?? null;
                $this->itemName = $data['name'] ?? $data['itemName'] ?? null;
            } elseif (is_object($data)) {
                $this->itemId = $data->id ?? $data->itemId ?? null;
                $this->itemType = $data->type ?? $data->itemType ?? null;
                $this->itemName = $data->name ?? $data->itemName ?? null;
            } else {
                Log::warning('⚠️ Data format tidak dikenali:', ['data' => $data, 'type' => gettype($data)]);
                return false;
            }

            // Validasi data
            if (!$this->itemId || !$this->itemType) {
                Log::error('❌ Data item tidak lengkap setelah parsing', [
                    'itemId' => $this->itemId,
                    'itemType' => $this->itemType,
                    'itemName' => $this->itemName,
                    'originalData' => $data
                ]);
                return false;
            }

            Log::info('✅ Data item berhasil di-set via event', [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'itemName' => $this->itemName
            ]);

            // Force re-render untuk memperbarui tampilan
            $this->dispatch('$refresh');

            // Dispatch konfirmasi
            $this->dispatch('item-data-set', [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'itemName' => $this->itemName,
                'status' => 'success'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Error processing item data: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    // IMPROVED: Method langsung dengan multiple signatures
    public function setItem($id = null, $type = null, $name = null)
    {
        Log::info('🎯 setItem method dipanggil langsung', [
            'id' => $id,
            'type' => $type,
            'name' => $name,
            'arguments_count' => func_num_args()
        ]);

        // Handle jika parameter pertama adalah array/object
        if (is_array($id) || is_object($id)) {
            return $this->processItemData($id);
        }

        // Handle parameter individual
        if ($id !== null && $type !== null) {
            $this->itemId = $id;
            $this->itemType = $type;
            $this->itemName = $name ?? 'Item #' . $id;

            Log::info('✅ Data item berhasil di-set via method langsung', [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'itemName' => $this->itemName
            ]);

            // Force re-render
            $this->dispatch('$refresh');

            // Dispatch konfirmasi
            $this->dispatch('item-data-set', [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'itemName' => $this->itemName,
                'status' => 'success'
            ]);

            return true;
        }

        Log::warning('⚠️ setItem dipanggil dengan parameter tidak lengkap');
        return false;
    }

    // NEW: Method untuk set data via property binding
    public function setItemId($value)
    {
        Log::info('Setting itemId property:', ['value' => $value]);
        $this->itemId = $value;
    }

    public function setItemType($value)
    {
        Log::info('Setting itemType property:', ['value' => $value]);
        $this->itemType = $value;
    }

    public function setItemName($value)
    {
        Log::info('Setting itemName property:', ['value' => $value]);
        $this->itemName = $value;
    }

    // IMPROVED: Method validasi yang lebih detail
    public function validateItemData()
    {
        $isValid = !empty($this->itemId) && !empty($this->itemType);

        Log::info('Validating item data', [
            'itemId' => $this->itemId,
            'itemType' => $this->itemType,
            'itemName' => $this->itemName,
            'isValid' => $isValid
        ]);

        if (!$isValid) {
            $errorMsg = 'Data item tidak valid. ';
            if (empty($this->itemId)) $errorMsg .= 'ID kosong. ';
            if (empty($this->itemType)) $errorMsg .= 'Type kosong. ';
            $errorMsg .= 'Silakan tutup modal dan coba lagi.';

            session()->flash('claim_error', $errorMsg);
            Log::error('❌ Validasi item data gagal', [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'error' => $errorMsg
            ]);
        }

        return $isValid;
    }

    // IMPROVED: Submit method dengan logging yang lebih detail
    public function submit()
    {
        Log::info('🚀 Submit klaim dimulai', [
            'itemId' => $this->itemId,
            'itemType' => $this->itemType,
            'itemName' => $this->itemName,
            'reason_length' => strlen($this->reason ?? ''),
            'has_image' => !empty($this->image),
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
        ]);

        // Validasi data item terlebih dahulu
        if (!$this->validateItemData()) {
            Log::error('❌ Submit dibatalkan karena data item tidak valid');
            return;
        }

        // Validasi input form
        try {
            $validationRules = [
                'itemId' => 'required',
                'itemType' => 'required|string',
                'reason' => 'required|string|max:500',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ];

            $this->validate($validationRules);
            Log::info('✅ Validasi form berhasil');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validasi form gagal', [
                'errors' => $e->errors(),
                'input_data' => [
                    'itemId' => $this->itemId,
                    'itemType' => $this->itemType,
                    'reason_length' => strlen($this->reason ?? ''),
                    'has_image' => !empty($this->image)
                ]
            ]);
            session()->flash('claim_error', 'Data yang dimasukkan tidak valid. Periksa kembali form Anda.');
            return;
        }

        try {
            // Cek login
            if (!auth()->check()) {
                Log::warning('❌ User belum login saat mencoba klaim');
                session()->flash('claim_error', 'Anda harus login terlebih dahulu.');
                return;
            }

            // Cek existing claim
            $existingClaim = Claim::where('claimable_id', $this->itemId)
                ->where('claimable_type', $this->itemType)
                ->first();

            if ($existingClaim) {
                Log::warning('❌ Item sudah diklaim sebelumnya', [
                    'existing_claim_id' => $existingClaim->id,
                    'existing_user_id' => $existingClaim->user_id,
                    'current_user_id' => auth()->id()
                ]);
                session()->flash('claim_error', 'Item ini sudah diklaim oleh pengguna lain.');
                return;
            }

            // Proses upload gambar
            $imageName = null;
            if ($this->image && $this->image->isValid()) {
                try {
                    $imageName = $this->image->store('claims', 'public');
                    Log::info('✅ Gambar berhasil diupload', [
                        'original_name' => $this->image->getClientOriginalName(),
                        'stored_name' => $imageName,
                        'size' => $this->image->getSize()
                    ]);
                } catch (\Exception $e) {
                    Log::error('❌ Gagal upload gambar: ' . $e->getMessage());
                    session()->flash('claim_error', 'Gagal mengupload gambar. Silakan coba lagi.');
                    return;
                }
            }

            // Simpan klaim
            $claim = Claim::create([
                'claimable_id' => $this->itemId,
                'claimable_type' => $this->itemType,
                'user_id' => auth()->id(),
                'claimed_at' => now(),
                'status' => 'pending',
                'reason' => $this->reason,
                'image' => $imageName,
            ]);

            Log::info('✅ Klaim berhasil dibuat', [
                'claim_id' => $claim->id,
                'claimable_id' => $claim->claimable_id,
                'claimable_type' => $claim->claimable_type,
                'user_id' => $claim->user_id
            ]);

            // Update status point
            if ($this->itemType === 'App\\Models\\PointsModel') {
                $point = \App\Models\PointsModel::find($this->itemId);
                if ($point) {
                    $point->update([
                        'status' => 'claimed',
                        'claimed_by' => auth()->id(),
                        'claimed_at' => now()
                    ]);
                    Log::info('✅ Point status berhasil diupdate', ['point_id' => $point->id]);
                } else {
                    Log::warning('⚠️ Point tidak ditemukan untuk update status', ['point_id' => $this->itemId]);
                }
            }

            // Success response
            session()->flash('claim_success', 'Klaim berhasil dikirim! Kami akan menghubungi Anda segera.');

            // Reset form (kecuali item data untuk debug)
            $this->reset(['reason', 'image']);

            // Dispatch events
            $this->dispatch('close-claim-modal');
            $this->dispatch('claim-success');

            Log::info('✅ Submit klaim selesai dengan sukses', ['claim_id' => $claim->id]);
        } catch (\Exception $e) {
            Log::error('❌ Error saat submit klaim: ' . $e->getMessage(), [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('claim_error', 'Terjadi kesalahan saat mengirim klaim. Silakan coba lagi.');
        }
    }

    // Method untuk reset form
    public function resetForm()
    {
        Log::info('🔄 Resetting form');
        $this->reset(['itemId', 'itemType', 'itemName', 'reason', 'image']);
        session()->forget(['claim_success', 'claim_error']);
    }

    // Lifecycle hooks
    public function mount()
    {
        Log::info('🏗️ ClaimItemForm component mounted', [
            'component_id' => $this->getId(),
            'user_id' => auth()->id()
        ]);
    }

    public function hydrate()
    {
        Log::info('💧 ClaimItemForm component hydrated', [
            'component_id' => $this->getId(),
            'itemId' => $this->itemId,
            'itemType' => $this->itemType,
            'itemName' => $this->itemName
        ]);
    }

    // Enhanced debugging method
    public function debugInfo()
    {
        $debugData = [
            'component_id' => $this->getId(),
            'item_data' => [
                'itemId' => $this->itemId,
                'itemType' => $this->itemType,
                'itemName' => $this->itemName
            ],
            'form_data' => [
                'reason' => $this->reason ? strlen($this->reason) . ' chars' : 'empty',
                'has_image' => !empty($this->image)
            ],
            'user_data' => [
                'logged_in' => auth()->check(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId()
            ],
            'validation_status' => $this->validateItemData(),
            'timestamp' => now()->toISOString()
        ];

        Log::info('🐛 Debug info requested', $debugData);
        return $debugData;
    }

    public function render()
    {
        return view('livewire.claim-item-form');
    }
}
