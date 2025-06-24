<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claimable_id'); // ID barang yang diklaim
            $table->string('claimable_type'); // Tipe barang (PolylinesModel, PointsModel, etc.)
            $table->foreignId('user_id')->constrained(); // ID user yang klaim
            $table->timestamp('claimed_at')->nullable(); // Waktu klaim
            $table->string('status')->default('pending'); // Status klaim, misal 'pending' atau 'approved'
            $table->text('reason'); // Alasan klaim
            $table->string('image')->nullable(); // Nama file gambar klaim (opsional)
            $table->timestamps(); // Timestamps untuk created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
