<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Support\PageContents;
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
            'photoCount' => ActivityImage::count(),
            'activitiesWithGallery' => BusinessActivity::has('images')->count(),
            // Kelola konten halaman
            'pages' => PageContents::definitions(),
        ]);
    }
}
