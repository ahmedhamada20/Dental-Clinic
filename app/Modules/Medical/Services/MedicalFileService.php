<?php
// app/Modules/Medical/Services/MedicalFileService.php
namespace App\Modules\Medical\Services;
use App\Models\Medical\MedicalFile;
use App\Models\Patient\Patient;
use App\Modules\Medical\DTOs\UpdateMedicalFileData;
use App\Modules\Medical\DTOs\UploadMedicalFileData;
use Illuminate\Support\Facades\Storage;

class MedicalFileService {
    public function byPatient(Patient $patient){ return $patient->medicalFiles()->latest('id')->get(); }
    public function show(int $id): MedicalFile { return MedicalFile::query()->findOrFail($id); }

    public function upload(Patient $patient, UploadMedicalFileData $data, int $userId): MedicalFile {
        $storedPath = $data->file->store('medical-files/'.$patient->id, 'public');
        return MedicalFile::query()->create([
            'patient_id'=>$patient->id,'visit_id'=>$data->visitId,'uploaded_by'=>$userId,'file_category'=>$data->fileCategory,
            'title'=>$data->title,'notes'=>$data->notes,'file_path'=>$storedPath,'file_name'=>$data->file->getClientOriginalName(),
            'file_extension'=>$data->file->getClientOriginalExtension(),'mime_type'=>$data->file->getClientMimeType(),
            'file_size'=>$data->file->getSize(),'is_visible_to_patient'=>$data->isVisibleToPatient,'uploaded_at'=>now(),
        ]);
    }

    public function update(MedicalFile $file, UpdateMedicalFileData $data): MedicalFile {
        $file->update(array_filter([
            'file_category'=>$data->fileCategory,'title'=>$data->title,'notes'=>$data->notes,
            'is_visible_to_patient'=>$data->isVisibleToPatient,'visit_id'=>$data->visitId,
        ], static fn($v)=>$v!==null));
        return $file->refresh();
    }

    public function delete(MedicalFile $file): void {
        if ($file->file_path) { Storage::disk('public')->delete($file->file_path); }
        $file->delete();
    }
}
