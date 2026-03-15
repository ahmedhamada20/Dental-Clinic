<?php
// app/Modules/Medical/Services/OdontogramService.php
namespace App\Modules\Medical\Services;
use App\Models\Medical\OdontogramHistory;
use App\Models\Medical\OdontogramTooth;
use App\Models\Patient\Patient;
use App\Modules\Medical\DTOs\UpdateToothStatusData;
use Illuminate\Support\Facades\DB;

class OdontogramService {
    public function listTeeth(Patient $patient) {
        return OdontogramTooth::query()->where('patient_id',$patient->id)->orderBy('tooth_number')->get();
    }
    public function listHistory(Patient $patient) {
        return OdontogramHistory::query()->where('patient_id',$patient->id)->latest('created_at')->get();
    }
    public function updateTooth(Patient $patient, UpdateToothStatusData $data, int $userId): OdontogramTooth {
        return DB::transaction(function () use ($patient,$data,$userId) {
            $tooth = OdontogramTooth::query()->firstOrNew([
                'patient_id'=>$patient->id,'tooth_number'=>$data->toothNumber
            ]);
            $oldStatus = $tooth->exists ? ($tooth->status?->value ?? (string)$tooth->status) : null;
            $tooth->status = $data->status;
            $tooth->surface = $data->surface;
            $tooth->notes = $data->notes;
            $tooth->visit_id = $data->visitId;
            $tooth->last_updated_by = $userId;
            $tooth->save();

            OdontogramHistory::query()->create([
                'patient_id'=>$patient->id,
                'tooth_number'=>$data->toothNumber,
                'old_status'=>$oldStatus,
                'new_status'=>$data->status,
                'surface'=>$data->surface,
                'notes'=>$data->notes,
                'visit_id'=>$data->visitId,
                'changed_by'=>$userId,
                'created_at'=>now(),
            ]);

            return $tooth->refresh();
        });
    }
}
