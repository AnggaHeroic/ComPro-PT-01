<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityImage;
use App\Models\ActivityLog;
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
            // Terakhir diubah: lima perubahan isi situs terbaru dari riwayat aktivitas
            'recent' => ActivityLog::whereIn('action', ActivityLog::contentActions())
                ->latest('created_at')->latest('id')->limit(5)->get(),
        ]);
    }
}
