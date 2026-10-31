@php
    $company = config('zenray.name');
    $contact = config('zenray.contact');
@endphp

<x-legal-page title="Privacy Policy" description="How {{ $company }} collects and uses personal information on this website." updated="{{ now()->parse('2026-10-29')->format('j F Y') }}">
    <p>This policy explains what personal information {{ $company }} (“we”, “us”) collects through this website, why we collect it, and how you can contact us about it.</p>

    <h2>Information we collect</h2>
    <h3>When you send an enquiry or quote request</h3>
    <ul>
        <li>Your name and, if you provide it, your organization or facility</li>
        <li>Your email address and/or phone number</li>
        <li>The equipment or service you are interested in, and your message</li>
    </ul>
    <h3>When you create an account</h3>
    <ul>
        <li>Your name, email address, and optionally your organization and phone number</li>
        <li>Your password, which is stored in encrypted (hashed) form and cannot be read by our staff</li>
    </ul>
    <h3>Technical information</h3>
    <p>Like most websites, our servers record basic technical information such as IP addresses and request times, which helps keep the site secure and working.</p>

    <h2>How we use your information</h2>
    <ul>
        <li>To respond to your enquiries and prepare quotations</li>
        <li>To provide and manage your customer account, including showing the status of your requests</li>
        <li>To keep the website secure and prevent misuse</li>
    </ul>
    <p>We do not sell your personal information.</p>

    <h2>Cookies</h2>
    <p>This website uses essential cookies only: a session cookie that keeps you signed in and remembers form information between pages, and a security cookie that protects forms against misuse. These cookies are needed for the website to work.</p>

    <h2>Who we share information with</h2>
    <p>We share information only with service providers who help us run the website and our business, such as hosting and email providers, and only as needed to provide those services. We may also disclose information where required by law.</p>

    <h2>How long we keep information</h2>
    <p>We keep enquiry and account information for as long as needed to respond to you, provide our services and meet our legal obligations. You can ask us to delete your account at any time.</p>

    <h2>Your choices and rights</h2>
    <p>You can view and update your account details from your dashboard. You can also contact us to ask for a copy of your information, to correct it, or to have it deleted, subject to any legal requirements.</p>

    <h2>Contact us</h2>
    <p>
        If you have questions about this policy or your information, please
        @if ($contact['email'])
            email <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
        @else
            <a href="{{ route('contact') }}">contact us</a>
        @endif
        @if ($contact['address']) or write to us at {{ $contact['address'] }}@endif.
    </p>
</x-legal-page>
