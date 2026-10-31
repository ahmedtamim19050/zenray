<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = BlogCategory::query()->ordered()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->get()
            ->where('posts_count', '>', 0);

        $category = $categories->firstWhere('slug', $request->string('category')->toString());
        $search = trim($request->string('q')->toString());
        $filtered = $category || $search !== '';

        // The featured post leads the first, unfiltered page.
        $featured = $filtered || $request->integer('page', 1) > 1 ? null
            : Post::query()->published()->with('category')->orderByDesc('is_featured')->latest('published_at')->first();

        $posts = Post::query()->published()->with('category')
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
            ->when($category, fn ($q) => $q->where('blog_category_id', $category->id))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('pages.blog.index', compact('categories', 'category', 'search', 'featured', 'posts'));
    }

    public function show(Request $request, Post $post): View
    {
        // Drafts and scheduled posts are visible to admins only, as a preview.
        abort_unless($post->isLive() || $request->user()?->isAdmin(), 404);

        $post->load(['category', 'author']);

        $related = Post::query()->published()->with('category')
            ->whereKeyNot($post->id)
            ->orderByRaw('blog_category_id = ? desc', [$post->blog_category_id ?? 0])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog.show', compact('post', 'related'));
    }
}
