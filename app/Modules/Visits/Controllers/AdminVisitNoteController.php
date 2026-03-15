<?php

namespace App\Modules\Visits\Controllers;

use App\Models\Visit\Visit;
use App\Models\Visit\VisitNote;
use App\Modules\Visits\Actions\AddVisitNoteAction;
use App\Modules\Visits\Actions\DeleteVisitNoteAction;
use App\Modules\Visits\Actions\UpdateVisitNoteAction;
use App\Modules\Visits\DTOs\VisitNoteData;
use App\Modules\Visits\Requests\StoreVisitNoteRequest;
use App\Modules\Visits\Requests\UpdateVisitNoteRequest;
use App\Modules\Visits\Resources\VisitNoteResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminVisitNoteController
{
    public function store(
        int $id,
        StoreVisitNoteRequest $request,
        AddVisitNoteAction $action
    ): JsonResponse {
        $visit = Visit::query()->findOrFail($id);

        $note = $action->execute(
            $visit,
            new VisitNoteData(
                noteType: $request->validated('note_type'),
                note: $request->validated('note')
            ),
            (int) Auth::id()
        );

        return ApiResponse::success(new VisitNoteResource($note), 'Visit note added successfully.');
    }

    public function update(
        int $id,
        UpdateVisitNoteRequest $request,
        UpdateVisitNoteAction $action
    ): JsonResponse {
        $note = VisitNote::query()->findOrFail($id);

        $updated = $action->execute(
            $note,
            new VisitNoteData(
                noteType: $request->validated('note_type'),
                note: $request->validated('note')
            )
        );

        return ApiResponse::success(new VisitNoteResource($updated), 'Visit note updated successfully.');
    }

    public function destroy(int $id, DeleteVisitNoteAction $action): JsonResponse
    {
        $note = VisitNote::query()->findOrFail($id);
        $action->execute($note);

        return ApiResponse::success(null, 'Visit note deleted successfully.');
    }
}
