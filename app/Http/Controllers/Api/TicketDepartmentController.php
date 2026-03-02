<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketDepartment;
use Illuminate\Http\JsonResponse;

/**
 * @group Ticket Departments
 *
 * Public endpoint for retrieving available support ticket departments.
 * Used by customers when creating a new support ticket.
 *
 * @unauthenticated
 */
class TicketDepartmentController extends Controller
{
    /**
     * List departments
     *
     * Returns all active ticket departments ordered by sort order.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Departments fetched successfully",
     *   "data": [
     *     {
     *       "slug": "order",
     *       "name": "Order Issues",
     *       "icon": "ti-shopping-cart",
     *       "color": "primary",
     *       "description": "Help with orders, payments, and deliveries"
     *     },
     *     {
     *       "slug": "account",
     *       "name": "Account & Billing",
     *       "icon": "ti-user",
     *       "color": "info",
     *       "description": "Account access, billing, and subscription issues"
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $departments = TicketDepartment::active()
                ->orderBy('sort_order')
                ->get(['slug', 'name', 'icon', 'color', 'description']);

            return $this->success($departments, 'Departments fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch departments', 500);
        }
    }
}
