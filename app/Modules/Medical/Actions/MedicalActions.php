<?php
// Actions: one-liner wrappers to keep controller thin
namespace App\Modules\Medical\Actions;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use App\Models\Medical\TreatmentPlan;
use App\Models\Medical\TreatmentPlanItem;
use App\Models\Medical\Prescription;
use App\Models\Medical\MedicalFile;
use App\Modules\Medical\DTOs\{UpdateToothStatusData,CreateTreatmentPlanData,UpdateTreatmentPlanData,TreatmentPlanItemData,CreatePrescriptionData,PrescriptionItemData,UploadMedicalFileData};
use App\Modules\Medical\Services\{OdontogramService,TreatmentPlanService,PrescriptionService,MedicalFileService};

class UpdateToothStatusAction { public function __construct(private readonly OdontogramService $s){} public function execute(Patient $p, UpdateToothStatusData $d, int $u){ return $this->s->updateTooth($p,$d,$u);} }
class CreateTreatmentPlanAction { public function __construct(private readonly TreatmentPlanService $s){} public function execute(Patient $p, CreateTreatmentPlanData $d, int $u){ return $this->s->create($p,$d,$u);} }
class UpdateTreatmentPlanAction { public function __construct(private readonly TreatmentPlanService $s){} public function execute(TreatmentPlan $p, UpdateTreatmentPlanData $d){ return $this->s->update($p,$d);} }
class AddTreatmentPlanItemAction { public function __construct(private readonly TreatmentPlanService $s){} public function execute(TreatmentPlan $p, TreatmentPlanItemData $d){ return $this->s->addItem($p,$d);} }
class CompleteTreatmentPlanItemAction { public function __construct(private readonly TreatmentPlanService $s){} public function execute(TreatmentPlanItem $i, ?int $v){ return $this->s->completeItem($i,$v);} }
class CreatePrescriptionAction { public function __construct(private readonly PrescriptionService $s){} public function execute(Visit $v, CreatePrescriptionData $d, int $u){ return $this->s->createFromVisit($v,$d,$u);} }
class AddPrescriptionItemAction { public function __construct(private readonly PrescriptionService $s){} public function execute(Prescription $p, PrescriptionItemData $d){ return $this->s->addItem($p,$d);} }
class UploadMedicalFileAction { public function __construct(private readonly MedicalFileService $s){} public function execute(Patient $p, UploadMedicalFileData $d, int $u){ return $this->s->upload($p,$d,$u);} }
class DeleteMedicalFileAction { public function __construct(private readonly MedicalFileService $s){} public function execute(MedicalFile $f): void { $this->s->delete($f);} }
