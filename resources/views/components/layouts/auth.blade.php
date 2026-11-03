@props(['title', 'heading', 'subheading' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
    <head>
        @include('partials.head', ['pageTitle' => "{$title} | ".config('zenray.name'), 'description' => config('zenray.description'), 'noindex' => true])
    </head>
    <body class="min-h-screen bg-white font-sans text-slate-700 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <main id="main" class="flex flex-col px-6 py-8 sm:px-12 lg:px-16 xl:px-24">
                <a href="{{ route('home') }}" class="w-fit rounded-lg" aria-label="{{ config('zenray.name') }} home">
                    <img src="{{ asset('images/zenray-logo.svg') }}" alt="{{ config('zenray.name') }}" width="486" height="124" class="h-10 w-auto">
                </a>

                <div class="my-auto w-full max-w-md py-12">
                    <h1 class="text-3xl font-bold tracking-[-0.03em] text-navy-900 sm:text-4xl">{{ $heading }}</h1>
                    @if ($subheading) <p class="mt-3 text-slate-600">{{ $subheading }}</p> @endif

                    @if (session('status'))
                        <div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">{{ session('status') }}</div>
                    @endif

                    <div class="mt-8">{{ $slot }}</div>
                </div>

                <p class="text-sm text-slate-500">&copy; {{ date('Y') }} {{ config('zenray.name') }}</p>
            </main>

            <aside class="relative isolate hidden overflow-hidden bg-navy-950 lg:block" aria-hidden="true">
                <x-photo name="c-arm" alt="" sizes="50vw" class="absolute inset-0 -z-20 opacity-60" />
                <div class="absolute inset-0 -z-10 bg-linear-to-t from-navy-950 via-navy-950/70 to-navy-950/20"></div>
                <div class="absolute -top-32 -right-32 -z-10 size-[30rem] rounded-full bg-signal-500/25 blur-[110px]"></div>
                <div class="bg-grid absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_bottom_left,black,transparent_70%)]"></div>

                <div class="flex h-full flex-col justify-end p-12 xl:p-16">
                    <p class="text-xs font-semibold tracking-[0.2em] text-signal-400 uppercase">Customer portal</p>
                    <p class="mt-4 max-w-md text-3xl leading-tight font-bold tracking-tight text-white xl:text-4xl">Track your quote requests and enquiries in one place.</p>
                    <ul class="mt-8 space-y-3 text-navy-100">
                        @foreach (['See the status of every request', 'Keep your contact details up to date', 'Send new requests in seconds'] as $point)
                            <li class="flex items-center gap-3">
                                <span class="flex size-6 items-center justify-center rounded-full bg-signal-500 text-navy-950"><x-icon name="check" class="size-3.5" /></span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </body>
</html>
