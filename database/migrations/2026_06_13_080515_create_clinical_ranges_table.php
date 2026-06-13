<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('record_type');
            $table->string('label');
            $table->string('unit');
            $table->decimal('min_value_normal', 8, 2);
            $table->decimal('max_value_normal', 8, 2);
            $table->decimal('min_value_warning', 8, 2)->nullable();
            $table->decimal('max_value_warning', 8, 2)->nullable();
            $table->decimal('critical_low', 8, 2)->nullable();
            $table->decimal('critical_high', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'record_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_ranges');
    }
};
