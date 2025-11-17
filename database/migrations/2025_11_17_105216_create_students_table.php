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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Unique admission number - auto-generated
            $table->string('admission_number', 50)->unique()->nullable();

            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group', 10)->nullable();

            // Contact Information
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('current_address');
            $table->text('permanent_address')->nullable();

            // Academic Information
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->string('session_year', 20); // e.g., "2024-2025"
            $table->date('admission_date');
            $table->integer('admission_year'); // extracted from admission_date
            $table->integer('admission_month'); // extracted from admission_date
            $table->integer('admission_position_in_month')->nullable(); // generated
            $table->integer('admission_position_overall')->nullable(); // generated, auto-increment

            // Previous School Information
            $table->string('previous_school_name')->nullable();
            $table->text('previous_school_address')->nullable();
            $table->string('previous_class')->nullable();
            $table->text('reason_for_transfer')->nullable();

            // Health Information
            $table->boolean('has_allergy')->default(false);
            $table->string('allergy_type')->nullable();
            $table->enum('allergy_severity', ['mild', 'moderate', 'severe'])->nullable();
            $table->text('allergy_notes')->nullable();
            $table->text('health_notes')->nullable();

            // Token Information
            $table->foreignId('registration_token_id')->nullable()->constrained('registration_tokens')->onDelete('set null');

            // Status
            $table->enum('status', ['active', 'inactive', 'graduated', 'transferred'])->default('active');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('admission_number');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('status');
            $table->index('admission_year');
            $table->index('admission_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
