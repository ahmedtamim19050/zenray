@php
    $editing = $post->exists;
    $main = ['title', 'slug', 'excerpt', 'body'];
    $publishAt = old('published_at', $post->published_at?->format('Y-m-d\TH:i'));
    [$stateLabel, $stateClasses] = $post->state();
@endphp

<x-layouts.dashboard :title="$editing ? 'Edit post' : 'New post'" area="admin">
    <x-slot:subtitle>
        <a href="{{ route('admin.posts.index') }}" class="font-semibold text-navy-800 hover:text-signal-700">← All posts</a>
    </x-slot:subtitle>
    @if ($editing)
        <x-slot:actions>
            <x-button :href="route('blog.show', $post)" variant="outline" size="sm" target="_blank" rel="noopener">
                {{ $post->isLive() ? 'View on website' : 'Preview' }}<span class="sr-only"> (opens in a new tab)</span>
            </x-button>
        </x-slot:actions>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200" role="alert">
            <p class="font-semibold">Please check the highlighted {{ Str::plural('field', $errors->count()) }} below.</p>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" novalidate id="post-form"
          action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
          class="grid items-start gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-dash.card class="xl:col-span-2">
            <div class="grid gap-6">
                @foreach ($main as $name)
                    <x-admin.field :name="$name" :field="$fields[$name]" :model="$post" />
                @endforeach
            </div>
        </x-dash.card>

        <div class="grid gap-6 xl:sticky xl:top-24">
            <x-dash.card title="Publishing">
                <div class="grid gap-5">
                    @if ($editing)
                        <p class="flex items-center justify-between text-sm text-slate-600">
                            Current status
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $stateClasses }}">
                                <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ $stateLabel }}
                            </span>
                        </p>
                    @endif

                    <fieldset>
                        <legend class="mb-1.5 text-sm font-semibold text-navy-800">Visibility</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (\App\Models\Post::STATUSES as $key => [$label])
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-navy-200 transition has-checked:bg-navy-800 has-checked:text-white has-checked:ring-navy-800">
                                    <input type="radio" name="status" value="{{ $key }}" @checked(old('status', $post->status) === $key) class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div>
                        <label for="field-published_at" class="mb-1.5 block text-sm font-semibold text-navy-800">Publish date</label>
                        <input type="datetime-local" id="field-published_at" name="published_at" value="{{ $publishAt }}" aria-describedby="published-at-help"
                               class="block w-full rounded-xl border-0 bg-white px-4 py-3 text-sm text-navy-900 shadow-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none">
                        <p id="published-at-help" class="mt-1.5 text-sm text-slate-500">Leave empty to publish now. A future date schedules the post.</p>
                        @error('published_at') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <x-admin.field name="is_featured" :field="$fields['is_featured']" :model="$post" />

                    <button type="submit" class="btn-shine inline-flex h-11 items-center justify-center rounded-full bg-signal-500 px-6 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">
                        {{ $editing ? 'Save post' : 'Create post' }}
                    </button>
                </div>
            </x-dash.card>

            <x-dash.card title="Details">
                <div class="grid gap-5">
                    <x-admin.field name="blog_category_id" :field="$fields['blog_category_id']" :model="$post" />
                    <p class="-mt-3 text-sm">
                        <a href="{{ route('admin.content.index', 'blog-categories') }}" class="font-semibold text-navy-800 underline decoration-navy-200 underline-offset-4 hover:decoration-navy-800">Manage categories</a>
                    </p>
                    <x-admin.field name="photo" :field="$fields['photo']" :model="$post" />
                    <x-admin.field name="alt" :field="$fields['alt']" :model="$post" />
                    <x-admin.field name="meta_description" :field="$fields['meta_description']" :model="$post" />
                </div>
            </x-dash.card>
        </div>
    </form>

    @if ($editing)
        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="mt-6" data-confirm="Delete “{{ $post->title }}”? This cannot be undone.">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-red-700 underline underline-offset-4 hover:text-red-800">Delete this post</button>
        </form>
    @endif
</x-layouts.dashboard>
