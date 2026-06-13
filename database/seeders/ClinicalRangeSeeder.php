<?php

namespace Database\Seeders;

use App\Models\ClinicalRange;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ClinicalRangeSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if ($tenant === null) {
            return;
        }

        $ranges = [
            [
                'record_type' => 'temperature',
                'label' => 'Temperatura Corporal',
                'unit' => '°C',
                'min_value_normal' => 36.1,
                'max_value_normal' => 37.2,
                'min_value_warning' => 35.0,
                'max_value_warning' => 38.5,
                'critical_low' => 35.0,
                'critical_high' => 38.5,
            ],
            [
                'record_type' => 'blood_pressure_systolic',
                'label' => 'Presión Arterial Sistólica',
                'unit' => 'mmHg',
                'min_value_normal' => 90,
                'max_value_normal' => 120,
                'min_value_warning' => 80,
                'max_value_warning' => 140,
                'critical_low' => 80,
                'critical_high' => 140,
            ],
            [
                'record_type' => 'blood_pressure_diastolic',
                'label' => 'Presión Arterial Diastólica',
                'unit' => 'mmHg',
                'min_value_normal' => 60,
                'max_value_normal' => 80,
                'min_value_warning' => 50,
                'max_value_warning' => 90,
                'critical_low' => 50,
                'critical_high' => 90,
            ],
            [
                'record_type' => 'heart_rate',
                'label' => 'Frecuencia Cardíaca',
                'unit' => 'lpm',
                'min_value_normal' => 60,
                'max_value_normal' => 100,
                'min_value_warning' => 50,
                'max_value_warning' => 120,
                'critical_low' => 50,
                'critical_high' => 120,
            ],
            [
                'record_type' => 'respiratory_rate',
                'label' => 'Frecuencia Respiratoria',
                'unit' => 'rpm',
                'min_value_normal' => 12,
                'max_value_normal' => 20,
                'min_value_warning' => 10,
                'max_value_warning' => 25,
                'critical_low' => 10,
                'critical_high' => 25,
            ],
            [
                'record_type' => 'oxygen_saturation',
                'label' => 'Saturación de Oxígeno',
                'unit' => '%',
                'min_value_normal' => 95,
                'max_value_normal' => 100,
                'min_value_warning' => 90,
                'max_value_warning' => 100,
                'critical_low' => 85,
                'critical_high' => null,
            ],
            [
                'record_type' => 'blood_glucose',
                'label' => 'Glucosa en Ayuno',
                'unit' => 'mg/dL',
                'min_value_normal' => 70,
                'max_value_normal' => 100,
                'min_value_warning' => 60,
                'max_value_warning' => 126,
                'critical_low' => 60,
                'critical_high' => 126,
            ],
        ];

        foreach ($ranges as $range) {
            ClinicalRange::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'record_type' => $range['record_type'],
                ],
                $range
            );
        }

        $this->command?->info('Rangos clínicos creados para tenant: '.$tenant->name);
    }
}
