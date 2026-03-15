<?php

namespace App\Modules\Appointments\Controllers\Admin;

use App\Models\Appointment\VisitTicket;
use App\Modules\Appointments\Resources\TicketResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class TicketController extends Controller
{
    /**
     * Get today's tickets.
     * GET /api/v1/admin/tickets/today
     */
    public function today(): mixed
    {
        try {
            $tickets = VisitTicket::whereDate('ticket_date', now())
                ->with(['patient', 'appointment'])
                ->orderBy('created_at', 'asc')
                ->paginate(50);

            return ApiResponse::paginated(
                $tickets,
                'Today tickets retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Call a ticket (mark as called).
     * POST /api/v1/admin/tickets/{id}/call
     */
    public function call(int $id): mixed
    {
        try {
            $ticket = VisitTicket::findOrFail($id);

            $ticket->update([
                'called_at' => now(),
            ]);

            return ApiResponse::success(
                new TicketResource($ticket),
                'Ticket called successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Start ticket (mark as started).
     * POST /api/v1/admin/tickets/{id}/start
     */
    public function start(int $id): mixed
    {
        try {
            $ticket = VisitTicket::findOrFail($id);

            if (!$ticket->called_at) {
                return ApiResponse::error('Ticket must be called first', 400);
            }

            // Update status to in-progress if exists
            if (method_exists($ticket, 'setStatusToInProgress')) {
                $ticket->setStatusToInProgress();
            }

            return ApiResponse::success(
                new TicketResource($ticket),
                'Ticket started'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * Finish ticket (mark as finished).
     * POST /api/v1/admin/tickets/{id}/finish
     */
    public function finish(int $id): mixed
    {
        try {
            $ticket = VisitTicket::findOrFail($id);

            $ticket->update([
                'finished_at' => now(),
            ]);

            return ApiResponse::success(
                new TicketResource($ticket),
                'Ticket finished'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}

