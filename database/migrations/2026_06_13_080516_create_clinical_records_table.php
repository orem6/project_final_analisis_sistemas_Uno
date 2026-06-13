<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('record_type');
            $table->decimal('value', 8, 2);
            $table->string('unit');
            $table->string('severity')->default('normal');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'patient_id']);
            $table->index(['tenant_id', 'record_type']);
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_records');
    }
};
