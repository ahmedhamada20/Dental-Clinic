<?php

namespace App\Modules\Visits\Actions;

use App\Models\Visit\VisitNote;
use App\Modules\Visits\DTOs\VisitNoteData;

class UpdateVisitNoteAction
{
    public function execute(VisitNote $note, VisitNoteData $data): VisitNote
    {
        $note->update([
            'note_type' => $data->noteType,
            'note' => $data->note,
        ]);

        return $note->refresh()->load('createdBy');
    }
}
