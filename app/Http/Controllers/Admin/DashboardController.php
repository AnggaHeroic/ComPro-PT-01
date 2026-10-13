<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'categoryCount' => ServiceCategory::count(),
            'categoryPublished' => ServiceCategory::published()->count(),
            'activityCount' => BusinessActivity::count(),
            'activityPublished' => BusinessActivity::published()->count(),
        ]);
    }
}
