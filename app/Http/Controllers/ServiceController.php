<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::published()->ordered()->get();

        return view('services.index', compact('categories'));
    }

    public function category(string $category): View
    {
        $category = ServiceCategory::published()
            ->where('slug', $category)
            ->with(['activities' => fn ($query) => $query->published()])
            ->firstOrFail();

        return view('services.category', compact('category'));
    }

    public function detail(string $category, string $activity): View
    {
        $category = ServiceCategory::published()
            ->where('slug', $category)
            ->firstOrFail();

        $activity = $category->activities()
            ->published()
            ->where('slug', $activity)
            ->with('images')
            ->firstOrFail();

        return view('services.detail', [
            'category' => $category,
            'activity' => $activity,
            'gallery' => $activity->images->pluck('image_url')->filter()->values()->all(),
        ]);
    }
}
