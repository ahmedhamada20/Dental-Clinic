<?php

namespace App\Modules\Appointments\Controllers\Patient;

use App\Models\Appointment\WaitingListRequest;
use App\Modules\Appointments\Actions\ClaimWaitingSlotAction;
use App\Modules\Appointments\Actions\JoinWaitingListAction;
use App\Modules\Appointments\DTOs\WaitingListRequestData;
use App\Modules\Appointments\Requests\Patient\ClaimWaitingSlotRequest;
use App\Modules\Appointments\Requests\Patient\JoinWaitingListRequest;
use App\Modules\Appointments\Resources\WaitingListRequestResource;
use App\Modules\Appointments\Services\WaitingListService;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class WaitingListController extends Controller
{
    public function __construct(
        private JoinWaitingListAction $joinAction,
        private ClaimWaitingSlotAction $claimAction,
        private WaitingListService $waitingListService,
    ) {}

    /**
     * Get patient's waiting list requests.
     * GET /api/v1/patient/waiting-list
     */
    public function index(): mixed
    {
        try {
            $patient = auth()->user();
            $status = request()->query('status');

            $query = WaitingListRequest::where('patient_id', $patient->id)
                ->with(['patient', 'service']);

            if ($status) {
                $query->where('status', $status);
            }

            $requests = $query->paginate(15);

            return ApiResponse::paginated(
                $requests,
                'Waiting list requests retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Join waiting list.
     * POST /api/v1/patient/waiting-list
     */
    public function store(JoinWaitingListRequest $request): mixed
    {
        try {
            $patient = auth()->user();

            $data = WaitingListRequestData::fromArray([
                'patient_id' => $patient->id,
                'service_id' => $request->service_id,
                'preferred_date' => $request->preferred_date,
                'preferred_from_time' => $request->preferred_from_time,
                'preferred_to_time' => $request->preferred_to_time,
            ]);

            $waitingListRequest = ($this->joinAction)($data);

            return ApiResponse::success(
                new WaitingListRequestResource($waitingListRequest),
                'Added to waiting list successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Remove from waiting list.
     * DELETE /api/v1/patient/waiting-list/{id}
     */
    public function destroy(int $id): mixed
    {
        try {
            $patient = auth()->user();
            $request = WaitingListRequest::where('patient_id', $patient->id)
                ->findOrFail($id);

            $request->delete();

            return ApiResponse::success(
                null,
                'Removed from waiting list successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Request not found', 404);
        }
    }

    /**
     * Claim a slot from waiting list.
     * POST /api/v1/patient/waiting-list/{id}/claim-slot
     */
    public function claimSlot(int $id, ClaimWaitingSlotRequest $request): mixed
    {
        try {
            $patient = auth()->user();
            $waitingRequest = WaitingListRequest::where('patient_id', $patient->id)
                ->findOrFail($id);

            // Verify request can be claimed
            if (!$this->waitingListService->isRequestValidForClaim($waitingRequest)) {
                return ApiResponse::error('This waiting list request cannot be claimed', 400);
            }

            $claimedRequest = ($this->claimAction)(
                $id,
                $request->waiting_list_request_id ? request()->appointment_date : $request->appointment_date ?? date('Y-m-d'),
                $request->waiting_list_request_id ? request()->start_time : $request->start_time ?? '09:00'
            );

            return ApiResponse::success(
                new WaitingListRequestResource($claimedRequest),
                'Slot claimed successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}

