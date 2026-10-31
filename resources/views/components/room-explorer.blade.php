{{--
    "What's in a digital X-ray room?" explorer.
    Every description is always visible in the list; markers on the photo only
    highlight the matching item, so nothing depends on JavaScript or hover.
    Positions are percentages of the room-overview photo.
--}}
@php
    $parts = [
        ['name' => 'Wall stand and detector', 'x' => 17.5, 'y' => 46, 'text' => 'Holds the image detector upright for exams taken while the patient stands, such as chest X-rays.'],
        ['name' => 'X-ray tube head', 'x' => 59, 'y' => 26, 'text' => 'Produces the X-ray beam. It can be moved and angled to line up with the part of the body being imaged.'],
        ['name' => 'Tube stand', 'x' => 51.5, 'y' => 47, 'text' => 'The column that carries the tube head, so it can travel along the room and reach both the table and the wall stand.'],
        ['name' => 'Patient table', 'x' => 64, 'y' => 56, 'text' => 'Supports the patient for exams taken lying down. In many rooms a detector sits in a tray under the tabletop.'],
    ];
@endphp

<div class="grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-12" data-explorer>
    <figure class="lg:col-span-7">
        <div class="relative overflow-hidden rounded-3xl bg-film-700 ring-1 ring-film-600">
            <x-xray name="room-overview" alt="A digital X-ray room with a wall stand on the left, a patient table in the centre and a tube head on a stand behind it"
                     sizes="(min-width: 1280px) 720px, (min-width: 1024px) 58vw, 100vw" class="aspect-[4/3]" />

            @foreach ($parts as $i => $part)
                <button type="button"
                        class="group/marker absolute -translate-x-1/2 -translate-y-1/2 rounded-full p-1.5 focus-visible:outline-offset-1"
                        style="left: {{ $part['x'] }}%; top: {{ $part['y'] }}%"
                        data-explorer-target="{{ $i }}"
                        aria-label="Show part {{ $i + 1 }}: {{ $part['name'] }}"
                        aria-pressed="false">
                    <span class="ping-ring relative isolate flex size-8 items-center justify-center rounded-full bg-film-800 text-sm font-bold text-ink shadow-lg ring-4 ring-white/50 transition duration-200 group-hover/marker:scale-110 group-aria-pressed/marker:scale-110 group-aria-pressed/marker:bg-signal-500 group-aria-pressed/marker:text-navy-950 group-aria-pressed/marker:ring-signal-500/30 sm:size-9">
                        {{ $i + 1 }}
                    </span>
                </button>
            @endforeach
        </div>
        <figcaption class="mt-3 text-sm text-ink-3">
            A general guide to a typical room. Layouts and features vary between systems.
        </figcaption>
    </figure>

    <ol class="space-y-3 lg:col-span-5" aria-label="Parts of a digital X-ray room">
        @foreach ($parts as $i => $part)
            <li>
                <button type="button"
                        class="group/item flex w-full gap-4 rounded-2xl p-4 text-left ring-1 ring-transparent transition duration-200 hover:bg-film-800 hover:ring-film-600 aria-pressed:bg-film-800 aria-pressed:shadow-card aria-pressed:ring-film-600"
                        data-explorer-target="{{ $i }}"
                        aria-pressed="false">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-navy-800 text-sm font-bold text-white transition-colors group-aria-pressed/item:bg-signal-500 group-aria-pressed/item:text-navy-950" aria-hidden="true">{{ $i + 1 }}</span>
                    <span>
                        <span class="block font-bold text-ink">{{ $part['name'] }}</span>
                        <span class="mt-1 block text-[0.9375rem] leading-relaxed text-ink-2">{{ $part['text'] }}</span>
                    </span>
                </button>
            </li>
        @endforeach
    </ol>
</div>
