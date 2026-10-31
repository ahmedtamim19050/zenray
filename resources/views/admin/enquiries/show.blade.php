<x-layouts.dashboard :title="'Enquiry #'.str_pad($enquiry->id, 5, '0', STR_PAD_LEFT)" area="admin">
    <x-slot:subtitle>{{ $enquiry->typeLabel() }} · received {{ $enquiry->created_at->format('j F Y, H:i') }}</x-slot:subtitle>
    <x-slot:actions>
        <x-button :href="route('admin.enquiries.index')" variant="outline" size="sm">← All enquiries</x-button>
        <x-button :href="route('admin.quotes.create', ['enquiry' => $enquiry->id])" size="sm" class="btn-shine"><x-icon name="document" class="size-4" /> Create quote</x-button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-dash.card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-signal-700">{{ $enquiry->interestLabel() }}</p>
                        <h2 class="mt-1 text-xl font-bold text-navy-900">{{ $enquiry->name }}</h2>
                        @if ($enquiry->organization) <p class="text-slate-500">{{ $enquiry->organization }}</p> @endif
                    </div>
                    <x-dash.status :enquiry="$enquiry" />
                </div>
                <div class="mt-6 rounded-2xl bg-slate-50 p-5 ring-1 ring-navy-100">
                    <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Message</p>
                    <p class="mt-2 leading-relaxed whitespace-pre-line text-navy-900">{{ $enquiry->message }}</p>
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    @if ($enquiry->email)
                        <x-button :href="'mailto:'.$enquiry->email.'?subject='.rawurlencode('Re: your '.Str::lower($enquiry->typeLabel()).' to '.config('zenray.name'))" variant="navy" size="sm"><x-icon name="mail" class="size-4" /> Reply by email</x-button>
                    @endif
                    @if ($enquiry->phone)
                        <x-button :href="'tel:'.preg_replace('/[^\d+]/', '', $enquiry->phone)" variant="outline" size="sm"><x-icon name="phone" class="size-4" /> Call {{ $enquiry->phone }}</x-button>
                    @endif
                </div>
            </x-dash.card>

            <x-dash.card title="Update" description="The status is visible to the customer if they have an account. Notes are internal only.">
                <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')
                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-navy-800">Status</legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach (\App\Models\Enquiry::STATUSES as $key => [$label])
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $key }}" class="peer sr-only" @checked(old('status', $enquiry->status) === $key)>
                                    <span class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-navy-200 transition peer-checked:bg-navy-800 peer-checked:text-white peer-checked:ring-navy-800 peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-navy-700 hover:ring-navy-400">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('status') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                    </fieldset>
                    <x-form.input name="admin_note" label="Internal note" type="textarea" rows="4" :value="$enquiry->admin_note" help="Only visible to admins." />
                    <button type="submit" class="inline-flex h-11 items-center rounded-full bg-signal-500 px-6 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">Save changes</button>
                </form>
            </x-dash.card>
        </div>

        <div class="space-y-6">
            <x-dash.card title="Contact details">
                <dl class="space-y-3 text-sm">
                    @foreach (['Email' => $enquiry->email, 'Phone' => $enquiry->phone, 'Organization' => $enquiry->organization, 'Type' => $enquiry->typeLabel(), 'Last updated' => $enquiry->updated_at->diffForHumans()] as $label => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="truncate text-right font-medium text-navy-900">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-dash.card>

            <x-dash.card title="Customer account">
                @if ($enquiry->user)
                    <p class="font-semibold text-navy-900">{{ $enquiry->user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $enquiry->user->email }}</p>
                    <a href="{{ route('admin.customers.show', $enquiry->user) }}" class="mt-4 inline-flex text-sm font-semibold text-navy-800 hover:text-signal-700">View customer →</a>
                @else
                    <p class="text-sm text-slate-500">Sent without signing in, so it is not linked to a customer account.</p>
                @endif
            </x-dash.card>
        </div>
    </div>
</x-layouts.dashboard>
