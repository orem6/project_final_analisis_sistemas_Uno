<?php

namespace App\Services;

use App\Models\ClinicalRange;
use App\Models\ClinicalRecord;
use Illuminate\Support\Collection;

class ClinicalAlertService
{
    public function evaluate(ClinicalRecord $record): ClinicalRecord
    {
        $range = ClinicalRange::query()
            ->where('tenant_id', $record->tenant_id)
            ->where('record_type', $record->record_type)
            ->first();

        if ($range === null) {
            $record->severity = 'normal';
            $record->save();

            return $record;
        }

        $record->severity = $range->evaluate((float) $record->value);
        $record->save();

        return $record;
    }

    public function getAlertsSummary(string $tenantId): array
    {
        $records = ClinicalRecord::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('severity', ['warning_low', 'warning_high', 'critical'])
            ->get();

        return [
            'critical' => $records->where('severity', 'critical')->count(),
            'warning_high' => $records->where('severity', 'warning_high')->count(),
            'warning_low' => $records->where('severity', 'warning_low')->count(),
            'total' => $records->count(),
        ];
    }

    public function getActiveAlerts(string $tenantId): Collection
    {
        return ClinicalRecord::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('severity', ['warning_low', 'warning_high', 'critical'])
            ->with('patient')
            ->orderByDesc('recorded_at')
            ->get();
    }
}
