<?php

namespace App\Modules\Audit\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Audit\DTOs\AuditLogFilterDTO;
use App\Modules\Audit\Requests\AuditLogListRequest;
use App\Modules\Audit\Resources\AuditLogResource;
use App\Modules\Audit\Services\AuditLogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(AuditLogListRequest $request): JsonResponse|View
    {
        $dto = AuditLogFilterDTO::fromArray($request->validated());
        $paginator = $this->auditLogService->list($dto);

        if ($request->expectsJson()) {
            return ApiResponse::success([
                'items' => AuditLogResource::collection(collect($paginator->items())),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ], 'Audit logs retrieved.');
        }

        return view('admin.audit-logs.index', [
            'logs' => $paginator,
            'filters' => $request->validated(),
            'modules' => $this->auditLogService->modules(),
            'actions' => $this->auditLogService->actions(),
            'actors' => $this->auditLogService->actors(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse|View
    {
        abort_unless($request->user()?->can('audit-logs.view'), 403);

        $log = $this->auditLogService->find($id);

        if ($request->expectsJson()) {
            return ApiResponse::success(
                new AuditLogResource($log),
                'Audit log retrieved.'
            );
        }

        return view('admin.audit-logs.show', [
            'log' => $log,
        ]);
    }
}
