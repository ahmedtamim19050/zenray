<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Support\ContentForm;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public const FILTERS = ['published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Drafts'];

    /**
     * Editable fields (status and publish date are handled separately).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return [
            'title' => ['label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:180']],
            'slug' => ['label' => 'URL name', 'type' => 'slug', 'from' => 'title', 'help' => 'Leave empty to create it from the title.'],
            'excerpt' => ['label' => 'Excerpt', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:400'], 'help' => 'A short summary for the blog list. If empty, the start of the post is used.'],
            'body' => ['label' => 'Post', 'type' => 'markdown', 'rules' => ['required', 'string', 'max:100000']],
            'photo' => ['label' => 'Cover image', 'type' => 'image'],
            'alt' => ['label' => 'Cover image description (alt text)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            'blog_category_id' => ['label' => 'Category', 'type' => 'select', 'options' => fn () => BlogCategory::query()->ordered()->pluck('name', 'id')->all(), 'rules' => ['nullable', 'exists:blog_categories,id']],
            'meta_description' => ['label' => 'Search engine description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300'], 'help' => 'Optional. Defaults to the excerpt.'],
            'is_featured' => ['label' => 'Feature at the top of the blog', 'type' => 'checkbox', 'rules' => ['boolean'], 'default' => false],
        ];
    }

    public function index(Request $request): View
    {
        $filter = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $posts = Post::query()
            ->with(['category', 'author'])
            ->when($filter === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($filter === 'published', fn ($q) => $q->published())
            ->when($filter === 'scheduled', fn ($q) => $q->where('status', 'published')->where('published_at', '>', now()))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderByRaw('published_at is null desc')
            ->latest('published_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'filter' => array_key_exists($filter, self::FILTERS) ? $filter : '',
            'search' => $search,
            'counts' => [
                '' => Post::query()->count(),
                'published' => Post::query()->published()->count(),
                'scheduled' => Post::query()->where('status', 'published')->where('published_at', '>', now())->count(),
                'draft' => Post::query()->where('status', 'draft')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post(['status' => 'draft']), 'fields' => self::fields()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = $this->save($request, new Post(['user_id' => $request->user()->id]));

        return to_route('admin.posts.edit', $post)->with('status', $post->isLive() ? 'Post published.' : 'Post saved.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post, 'fields' => self::fields()]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post = $this->save($request, $post);

        return to_route('admin.posts.edit', $post)->with('status', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();
        Photo::forget($post->photo);

        return to_route('admin.posts.index')->with('status', 'Post deleted.');
    }

    private function save(Request $request, Post $post): Post
    {
        $publishing = $request->validate([
            'status' => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at' => ['nullable', 'date'],
        ]);

        ContentForm::fill($request, $post, self::fields(), 'blog');

        $post->status = $publishing['status'];
        $post->published_at = filled($publishing['published_at'] ?? null)
            ? Carbon::parse($publishing['published_at'])
            : ($post->status === 'published' ? ($post->published_at ?? now()) : null);
        $post->save();

        return $post;
    }
}
