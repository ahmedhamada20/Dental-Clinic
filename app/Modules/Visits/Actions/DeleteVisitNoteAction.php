<?php

namespace App\Modules\Visits\Actions;

use App\Models\Visit\VisitNote;

class DeleteVisitNoteAction
{
    public function execute(VisitNote $note): void
    {
        $note->delete();
    }
}
