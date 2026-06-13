<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'document_number',
        'name',
        'last_name',
        'birth_date',
        'phone',
        'email',
        'gender',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function clinicalRecords(): HasMany
    {
        return $this->hasMany(ClinicalRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }
}
