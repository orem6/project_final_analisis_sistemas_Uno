<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClinicalRange;
use App\Models\ClinicalRecord;
use App\Models\Patient;
use App\Services\ClinicalAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClinicalAlertController extends Controller
{
    public function __construct(
        private readonly ClinicalAlertService $alertService
    ) {}

    public function patientsIndex(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $patients = Patient::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $patients]);
    }

    public function patientsStore(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $validated = Validator::make($request->all(), [
            'document_number' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
        ])->validate();

        $validated['tenant_id'] = $tenant->id;

        $patient = Patient::query()->create($validated);

        return response()->json(['data' => $patient], 201);
    }

    public function recordsIndex(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $query = ClinicalRecord::query()
            ->where('tenant_id', $tenant->id)
            ->with('patient');

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->input('patient_id'));
        }

        if ($request->has('record_type')) {
            $query->where('record_type', $request->input('record_type'));
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->has('from')) {
            $query->where('recorded_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('recorded_at', '<=', $request->input('to'));
        }

        $records = $query->orderByDesc('recorded_at')->paginate(50);

        return response()->json($records);
    }

    public function recordsStore(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $validated = Validator::make($request->all(), [
            'patient_id' => ['required', 'exists:patients,id'],
            'record_type' => ['required', 'string', 'max:100'],
            'value' => ['required', 'numeric', 'min:0', 'max:9999'],
            'unit' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $hasRange = ClinicalRange::query()
            ->where('tenant_id', $tenant->id)
            ->where('record_type', $validated['record_type'])
            ->exists();

        if (! $hasRange) {
            return response()->json([
                'message' => 'No hay rango configurado para este tipo de registro en el tenant actual.',
            ], 422);
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['recorded_by'] = auth('api')->id();
        $validated['recorded_at'] = now();

        $record = ClinicalRecord::query()->create($validated);

        $record = $this->alertService->evaluate($record);
        $record->load('patient');

        return response()->json(['data' => $record], 201);
    }

    public function alertsSummary(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $summary = $this->alertService->getAlertsSummary($tenant->id);

        return response()->json(['data' => $summary]);
    }

    public function activeAlerts(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $alerts = $this->alertService->getActiveAlerts($tenant->id);

        return response()->json(['data' => $alerts]);
    }

    public function rangesIndex(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $ranges = ClinicalRange::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('label')
            ->get();

        return response()->json(['data' => $ranges]);
    }
}
