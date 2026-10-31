New {{ ($enquiry['type'] ?? null) === 'quote' ? 'quote request' : 'enquiry' }} from the {{ config('zenray.name') }} website

Name:          {{ $enquiry['name'] }}
Organization:  {{ ($enquiry['organization'] ?? null) ?: '-' }}
Email:         {{ ($enquiry['email'] ?? null) ?: '-' }}
Phone:         {{ ($enquiry['phone'] ?? null) ?: '-' }}
Interested in: {{ $enquiry['interest_label'] }}

Message:
{{ $enquiry['message'] }}
