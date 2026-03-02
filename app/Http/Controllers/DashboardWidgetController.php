<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\StatsWidgetService;
use App\Services\Dashboard\ViewSalesWidgetService;
use App\Services\Dashboard\ProfitWidgetService;
use App\Services\Dashboard\ExpensesWidgetService;
use App\Services\Dashboard\RevenueReportWidgetService;
use App\Services\Dashboard\EarningReportWidgetService;
use App\Services\Dashboard\PopularProductsWidgetService;
use App\Services\Dashboard\RecentOrdersWidgetService;
use App\Services\Dashboard\RecentTransactionsWidgetService;
use App\Services\Dashboard\GeneratedLeadsWidgetService;
use App\Services\Dashboard\InvoiceWidgetService;

class DashboardWidgetController extends Controller
{
    private function hasFullAccess(): bool
    {
        $user = Auth::user();
        return $user->isSuperAdmin() || $user->hasRole('admin');
    }

    private function denyAccess(): JsonResponse
    {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    public function statistics(StatsWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function viewSales(ViewSalesWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function profit(ProfitWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function expenses(ExpensesWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function revenueReport(RevenueReportWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function earningReport(EarningReportWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function popularProducts(PopularProductsWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function recentOrders(RecentOrdersWidgetService $service): JsonResponse
    {
        return response()->json($service->data(Auth::user()));
    }

    public function recentTransactions(RecentTransactionsWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function generatedLeads(GeneratedLeadsWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }

    public function invoices(InvoiceWidgetService $service): JsonResponse
    {
        if (!$this->hasFullAccess()) return $this->denyAccess();
        return response()->json($service->data(Auth::user()));
    }
}
