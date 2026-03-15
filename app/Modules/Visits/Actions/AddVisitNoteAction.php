<?php

namespace App\Modules\Visits\Actions;

use App\Models\Visit\Visit;
use App\Models\Visit\VisitNote;
use App\Modules\Visits\DTOs\VisitNoteData;

class AddVisitNoteAction
{
    public function execute(Visit $visit, VisitNoteData $data, int $userId): VisitNote
    {
        return $visit->notes()->create([
            'note_type' => $data->noteType,
            'note' => $data->note,
            'created_by' => $userId,
        ])->load('createdBy');
    }
}
