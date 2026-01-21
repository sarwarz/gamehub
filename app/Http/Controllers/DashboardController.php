<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\StatsWidgetService;
use App\Services\Dashboard\ViewSalesWidgetService;

class DashboardController extends Controller
{
    public function index(
        StatsWidgetService $stats,
        ViewSalesWidgetService $viewSales
    ) {
        $user = Auth::user();

        return view('dashboard.index', [
            'user'      => $user,
            'stats'     => $stats->data($user),
            'viewSales' => $viewSales->data($user),
        ]);
    }
}
