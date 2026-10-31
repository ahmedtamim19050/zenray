<x-layouts.dashboard title="Blog posts" area="admin">
    <x-slot:subtitle>Articles published on the website blog.</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('blog.index')" variant="outline" size="sm" target="_blank" rel="noopener">View blog<span class="sr-only"> (opens in a new tab)</span></x-button>
        <x-button :href="route('admin.posts.create')" size="sm" class="btn-shine"><span aria-hidden="true">+</span> New post</x-button>
    </x-slot:actions>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter by status">
            <ul class="flex w-max gap-1 rounded-full bg-white p-1 ring-1 ring-navy-100">
                @foreach (['' => 'All'] + \App\Http\Controllers\Admin\PostController::FILTERS as $key => $label)
                    @php($active = $filter === $key)
                    <li>
                        <a href="{{ route('admin.posts.index', array_filter(['status' => $key, 'q' => $search])) }}" @if ($active) aria-current="page" @endif
                           @class(['inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition', 'bg-navy-800 text-white' => $active, 'text-slate-600 hover:text-navy-900' => ! $active])>
                            {{ $label }}
                            <span @class(['rounded-full px-1.5 font-mono text-xs', 'bg-signal-500 text-navy-950' => $active, 'bg-slate-100 text-slate-600' => ! $active])>{{ $counts[$key] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="GET" action="{{ route('admin.posts.index') }}" class="flex gap-2" role="search">
            @if ($filter) <input type="hidden" name="status" value="{{ $filter }}"> @endif
            <label for="post-search" class="sr-only">Search posts</label>
            <input id="post-search" type="search" name="q" value="{{ $search }}" placeholder="Search titles…"
                   class="h-10 w-full rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none lg:w-72">
            <button type="submit" class="h-10 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
        </form>
    </div>

    <x-dash.card :padded="false">
        @if ($posts->isEmpty())
            <x-dash.empty icon="document" :title="$search || $filter ? 'No posts found' : 'No posts yet'"
                          :text="$search || $filter ? 'Try a different filter or search.' : 'Write your first article to start the blog.'">
                @unless ($search || $filter)
                    <x-button :href="route('admin.posts.create')" size="sm">Write a post</x-button>
                @endunless
            </x-dash.empty>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Post</th>
                            <th scope="col" class="px-6 py-3">Category</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-50">
                        @foreach ($posts as $post)
                            @php([$stateLabel, $stateClasses] = $post->state())
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <span class="relative size-12 shrink-0 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-navy-100">
                                            @if ($post->photo) <x-photo :name="$post->photo" alt="" sizes="48px" /> @else <x-icon name="document" class="absolute inset-0 m-auto size-5 text-slate-300" /> @endif
                                        </span>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-navy-900 hover:text-signal-700">{{ $post->title }}</a>
                                            <p class="text-slate-500">
                                                {{ $post->author?->name ?? 'Unknown author' }}
                                                @if ($post->is_featured) · <span class="font-semibold text-signal-700">Featured</span> @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $post->category?->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $post->published_at?->format('j M Y') ?? 'Not set' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap ring-1 ring-inset {{ $stateClasses }}">
                                        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ $stateLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('blog.show', $post) }}" target="_blank" rel="noopener" class="rounded-full px-3 py-1.5 font-semibold text-slate-600 hover:bg-navy-50 hover:text-navy-900">{{ $post->isLive() ? 'View' : 'Preview' }}<span class="sr-only"> {{ $post->title }} (opens in a new tab)</span></a>
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="rounded-full px-3 py-1.5 font-semibold text-navy-800 hover:bg-navy-50">Edit<span class="sr-only"> {{ $post->title }}</span></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($posts->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $posts->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
