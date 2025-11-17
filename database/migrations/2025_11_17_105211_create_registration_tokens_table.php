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
        Schema::create('registration_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_code', 32)->unique(); // e.g., "ENROLL-2024-ABCD1234"
            $table->enum('status', ['unused', 'used', 'disabled', 'expired'])->default('unused');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade'); // admin who created it
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('used_by_student_id')->nullable(); // FK added after students table
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable(); // admin notes
            $table->timestamps();

            $table->index('token_code');
            $table->index('status');
            $table->index('generated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_tokens');
    }
};
