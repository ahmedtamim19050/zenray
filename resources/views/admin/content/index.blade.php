@php
    $list = $config['list'];
    $hasActive = array_key_exists('is_active', $config['fields']);
    $publicUrl = match ($resource) {
        'equipment-categories', 'products' => route('products.index'),
        'services' => route('services.index'),
        'projects', 'gallery' => route('portfolio.index'),
        'blog-categories' => route('blog.index'),
        default => route('home'),
    };
    $canReorder = $search === '';
@endphp

<x-layouts.dashboard :title="$config['plural']" area="admin">
    <x-slot:subtitle>{{ $config['description'] }}</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="$publicUrl" variant="outline" size="sm" target="_blank" rel="noopener">View on website<span class="sr-only"> (opens in a new tab)</span></x-button>
        <x-button :href="route('admin.content.create', $resource)" size="sm" >
            <span aria-hidden="true">+</span> Add {{ Str::lower($config['singular']) }}
        </x-button>
    </x-slot:actions>

    <x-dash.card :padded="false">
        <x-slot:actions>
            <form method="GET" action="{{ route('admin.content.index', $resource) }}" class="flex gap-2" role="search">
                <label for="content-search" class="sr-only">Search {{ Str::lower($config['plural']) }}</label>
                <input id="content-search" type="search" name="q" value="{{ $search }}" placeholder="Search…"
                       class="h-9 w-48 rounded-full border-0 bg-white px-4 text-sm ring-1 ring-navy-200 focus:ring-2 focus:ring-navy-700 focus:outline-none">
                <button type="submit" class="h-9 rounded-full bg-navy-800 px-4 text-sm font-semibold text-white hover:bg-navy-700">Search</button>
            </form>
        </x-slot:actions>
        <x-slot:title>{{ $items->total() }} {{ Str::plural(Str::lower($config['singular']), $items->total()) }}</x-slot:title>

        @if ($items->isEmpty())
            <x-dash.empty :icon="$config['icon']" :title="$search ? 'Nothing matches your search' : 'Nothing here yet'"
                          :text="$search ? 'Try a different search.' : 'Add the first '.Str::lower($config['singular']).' to show it on the website.'">
                @unless ($search)
                    <x-button :href="route('admin.content.create', $resource)" size="sm">Add {{ Str::lower($config['singular']) }}</x-button>
                @endunless
            </x-dash.empty>
        @else
            <ul class="divide-y divide-navy-50">
                @foreach ($items as $item)
                    @php
                        $title = \App\Support\AdminContent::display($item, $list['title']);
                        $subtitle = \App\Support\AdminContent::display($item, $list['subtitle'] ?? null);
                        $hidden = $hasActive && ! $item->is_active;
                    @endphp
                    <li @class(['flex items-center gap-4 px-4 py-3 transition hover:bg-slate-50 sm:px-6', 'opacity-60' => $hidden])>
                        @if ($canReorder)
                            <div class="flex shrink-0 flex-col">
                                @foreach (['up' => 'rotate-90', 'down' => '-rotate-90'] as $direction => $rotate)
                                    @php($disabled = ($direction === 'up' && $loop->parent->first && $items->onFirstPage()) || ($direction === 'down' && $loop->parent->last && ! $items->hasMorePages()))
                                    <form method="POST" action="{{ route('admin.content.move', [$resource, $item->id]) }}">
                                        @csrf
                                        <input type="hidden" name="direction" value="{{ $direction }}">
                                        <button type="submit" @disabled($disabled) class="flex size-6 items-center justify-center rounded text-slate-400 hover:bg-navy-50 hover:text-navy-800 disabled:invisible">
                                            <x-icon name="chevron-left" class="size-4 {{ $rotate }}" /><span class="sr-only">Move {{ $title }} {{ $direction }}</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif

                        @if (isset($list['image']))
                            <span class="relative size-14 shrink-0 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-navy-100">
                                @if ($item->{$list['image']})
                                    <x-photo :name="$item->{$list['image']}" alt="" sizes="56px" />
                                @else
                                    <x-icon name="expand" class="absolute inset-0 m-auto size-5 text-slate-300" />
                                @endif
                            </span>
                        @elseif (isset($list['icon']))
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-navy-800 text-white"><x-icon :name="$item->{$list['icon']}" class="size-5" /></span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-navy-900">
                                <a href="{{ route('admin.content.edit', [$resource, $item->id]) }}" class="hover:text-signal-700">{{ $title }}</a>
                            </p>
                            @if ($subtitle)
                                <p class="truncate text-sm text-slate-500">{{ Str::ucfirst($subtitle) }}</p>
                            @endif
                        </div>

                        @isset($list['count'])
                            <span class="hidden shrink-0 text-sm text-slate-500 sm:block">{{ $item->{$list['count'][0].'_count'} }} {{ Str::plural($list['count'][1], $item->{$list['count'][0].'_count'}) }}</span>
                        @endisset

                        @if ($hasActive)
                            <span @class([
                                'hidden shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset sm:inline-flex',
                                'bg-emerald-50 text-emerald-700 ring-emerald-200' => ! $hidden,
                                'bg-slate-100 text-slate-600 ring-slate-200' => $hidden,
                            ])>
                                <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                {{ $hidden ? 'Hidden' : 'Visible' }}
                            </span>
                        @endif

                        <div class="flex shrink-0 items-center gap-1">
                            <a href="{{ route('admin.content.edit', [$resource, $item->id]) }}" class="rounded-full px-3 py-1.5 text-sm font-semibold text-navy-800 hover:bg-navy-50">Edit<span class="sr-only"> {{ $title }}</span></a>
                            <form method="POST" action="{{ route('admin.content.destroy', [$resource, $item->id]) }}" data-confirm="Delete “{{ $title }}”? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Delete<span class="sr-only"> {{ $title }}</span></button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if ($items->hasPages())
                <div class="border-t border-navy-100 px-6 py-4">{{ $items->links() }}</div>
            @endif
        @endif
    </x-dash.card>
</x-layouts.dashboard>
