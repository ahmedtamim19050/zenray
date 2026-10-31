<?php

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\User;

function makePost(array $attributes = []): Post
{
    static $n = 0;
    $n++;

    return Post::create(array_merge([
        'title' => "Post {$n}",
        'slug' => "post-{$n}",
        'body' => "Body of post {$n}.",
        'status' => 'published',
        'published_at' => now()->subDay(),
    ], $attributes));
}

test('the blog lists published posts only', function () {
    makePost(['title' => 'Live article']);
    makePost(['title' => 'Draft article', 'status' => 'draft', 'published_at' => null]);
    makePost(['title' => 'Future article', 'published_at' => now()->addWeek()]);

    $this->get('/blog')->assertOk()->assertSee('Live article')->assertDontSee('Draft article')->assertDontSee('Future article');
});

test('the blog shows an empty state without posts', function () {
    $this->get('/blog')->assertOk()->assertSee('No articles yet');
});

test('posts can be filtered by category and searched', function () {
    $guides = BlogCategory::create(['name' => 'Guides', 'slug' => 'guides']);
    makePost(['title' => 'Choosing a detector', 'blog_category_id' => $guides->id]);
    makePost(['title' => 'Company news']);

    $this->get('/blog?category=guides')->assertSee('Choosing a detector')->assertDontSee('Company news');
    $this->get('/blog?q=detector')->assertSee('Choosing a detector')->assertDontSee('Company news');
});

test('a post page renders markdown and strips raw html', function () {
    $post = makePost(['body' => "## Section\n\nHello **world**\n\n<script>alert(1)</script>"]);

    $this->get("/blog/{$post->slug}")->assertOk()
        ->assertSee('<h2>Section</h2>', false)
        ->assertSee('<strong>world</strong>', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('drafts are hidden from visitors but can be previewed by admins', function () {
    $post = makePost(['status' => 'draft', 'published_at' => null]);

    $this->get("/blog/{$post->slug}")->assertNotFound();
    $this->actingAs(User::factory()->create())->get("/blog/{$post->slug}")->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())->get("/blog/{$post->slug}")->assertOk()->assertSee('only visible to admins');
});

test('the homepage shows the latest posts', function () {
    makePost(['title' => 'Fresh from the blog']);

    $this->get('/')->assertSee('Guides and insights')->assertSee('Fresh from the blog');
});

test('admins can write and publish a post', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/posts/create')->assertOk();

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'How DR detectors work',
        'body' => 'A short introduction.',
        'status' => 'published',
    ])->assertSessionHasNoErrors();

    $post = Post::where('slug', 'how-dr-detectors-work')->firstOrFail();
    expect($post->user_id)->toBe($admin->id)->and($post->isLive())->toBeTrue();

    $this->actingAs($admin)->get("/admin/posts/{$post->slug}/edit")->assertOk();
    $this->get('/blog')->assertSee('How DR detectors work');
});

test('posts can be scheduled and saved as drafts', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/posts', ['title' => 'Later', 'body' => 'Soon.', 'status' => 'published', 'published_at' => now()->addDays(3)->format('Y-m-d\TH:i')]);
    $this->actingAs($admin)->post('/admin/posts', ['title' => 'Unfinished', 'body' => 'Draft.', 'status' => 'draft']);

    expect(Post::where('slug', 'later')->first()->state()[0])->toBe('Scheduled')
        ->and(Post::where('slug', 'unfinished')->first()->published_at)->toBeNull();

    $this->actingAs($admin)->get('/admin/posts?status=scheduled')->assertSee('Later')->assertDontSee('Unfinished');
});

test('posts are validated', function () {
    $this->actingAs(User::factory()->admin()->create())->post('/admin/posts', ['title' => '', 'body' => '', 'status' => 'live'])
        ->assertSessionHasErrors(['status']);

    $this->actingAs(User::factory()->admin()->create())->post('/admin/posts', ['title' => '', 'body' => '', 'status' => 'draft'])
        ->assertSessionHasErrors(['title', 'body']);
});

test('admins can update and delete posts', function () {
    $admin = User::factory()->admin()->create();
    $post = makePost(['title' => 'Old title']);

    $this->actingAs($admin)->put("/admin/posts/{$post->slug}", ['title' => 'New title', 'slug' => $post->slug, 'body' => 'Updated.', 'status' => 'published'])
        ->assertRedirect("/admin/posts/{$post->slug}/edit");
    expect($post->fresh()->title)->toBe('New title');

    $this->actingAs($admin)->delete("/admin/posts/{$post->slug}")->assertRedirect('/admin/posts');
    expect(Post::find($post->id))->toBeNull();
});

test('customers cannot manage posts', function () {
    $this->actingAs(User::factory()->create())->get('/admin/posts')->assertForbidden();
});
