<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'record_type',
        'label',
        'unit',
        'min_value_normal',
        'max_value_normal',
        'min_value_warning',
        'max_value_warning',
        'critical_low',
        'critical_high',
    ];

    protected function casts(): array
    {
        return [
            'min_value_normal' => 'decimal:2',
            'max_value_normal' => 'decimal:2',
            'min_value_warning' => 'decimal:2',
            'max_value_warning' => 'decimal:2',
            'critical_low' => 'decimal:2',
            'critical_high' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function evaluate(float $value): string
    {
        if ($this->critical_low !== null && $value < $this->critical_low) {
            return 'critical';
        }

        if ($this->critical_high !== null && $value > $this->critical_high) {
            return 'critical';
        }

        if ($this->min_value_warning !== null && $value < $this->min_value_warning) {
            return 'warning_low';
        }

        if ($this->max_value_warning !== null && $value > $this->max_value_warning) {
            return 'warning_high';
        }

        if ($value >= $this->min_value_normal && $value <= $this->max_value_normal) {
            return 'normal';
        }

        if ($value < $this->min_value_normal) {
            return 'warning_low';
        }

        return 'warning_high';
    }
}
