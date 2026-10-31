<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the public homepage. Content is managed from the admin dashboard
     * and loaded into config('zenray.*') by App\Support\SiteContent.
     */
    public function __invoke(): View
    {
        $products = config('zenray.demo_products', []);

        return view('pages.home', [
            'categories' => config('zenray.product_categories', []),
            'products' => $products,
            'productsAreExamples' => collect($products)->contains(fn ($p) => $p['example'] ?? true),
            'services' => config('zenray.services', []),
            'projects' => array_slice(config('zenray.projects', []), 0, 3),
            'gallery' => config('zenray.gallery', []),
            'posts' => Post::query()->published()->with('category')->latest('published_at')->limit(3)->get(),
        ]);
    }
}
