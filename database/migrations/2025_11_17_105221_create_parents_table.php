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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // if parent has login
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('relationship', ['father', 'mother', 'guardian', 'other']);
            $table->string('phone_primary', 20);
            $table->string('phone_secondary', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->text('address');
            $table->string('national_id', 50)->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('phone_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
