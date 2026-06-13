<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('document_number')->unique();
            $table->string('name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
