<?php
// app/Modules/Medical/Services/PrescriptionService.php
namespace App\Modules\Medical\Services;
use App\Models\Medical\Prescription;
use App\Models\Medical\PrescriptionItem;
use App\Models\Visit\Visit;
use App\Modules\Medical\DTOs\CreatePrescriptionData;
use App\Modules\Medical\DTOs\PrescriptionItemData;

class PrescriptionService {
    public function forPatient(int $patientId){ return Prescription::query()->where('patient_id',$patientId)->with('items')->latest('id')->get(); }
    public function show(int $id): Prescription { return Prescription::query()->with('items')->findOrFail($id); }

    public function createFromVisit(Visit $visit, CreatePrescriptionData $data, int $doctorId): Prescription {
        return Prescription::query()->create([
            'patient_id'=>$visit->patient_id,'visit_id'=>$visit->id,'doctor_id'=>$doctorId,'notes'=>$data->notes,'issued_at'=>now(),
        ]);
    }

    public function update(Prescription $prescription, ?string $notes): Prescription {
        $prescription->update(['notes'=>$notes]); return $prescription->refresh()->load('items');
    }

    public function addItem(Prescription $prescription, PrescriptionItemData $data): PrescriptionItem {
        return $prescription->items()->create([
            'medicine_name'=>$data->medicineName,'dosage'=>$data->dosage,'frequency'=>$data->frequency,
            'duration'=>$data->duration,'instructions'=>$data->instructions,
        ]);
    }

    public function updateItem(PrescriptionItem $item, PrescriptionItemData $data): PrescriptionItem {
        $item->update([
            'medicine_name'=>$data->medicineName,'dosage'=>$data->dosage,'frequency'=>$data->frequency,
            'duration'=>$data->duration,'instructions'=>$data->instructions,
        ]);
        return $item->refresh();
    }
}
