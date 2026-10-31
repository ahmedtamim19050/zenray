@php
    $company = config('zenray.name');
    $contact = config('zenray.contact');
@endphp

<x-legal-page title="Terms and Conditions" description="Terms for using the {{ $company }} website." updated="{{ now()->parse('2026-10-29')->format('j F Y') }}">
    <p>These terms apply to your use of the {{ $company }} website. By using the website, you agree to them.</p>

    <h2>Information on this website</h2>
    <p>The information on this website is provided for general guidance about medical imaging equipment and our services. It is not medical advice, and it is not a complete specification of any product. Product details, availability and pricing are confirmed only in a written quotation.</p>
    <p>Photos and example listings show the types of equipment we can help with. They do not represent specific stock.</p>

    <h2>Quotations and orders</h2>
    <p>Sending an enquiry or quote request does not create a contract. Any supply of equipment or services is subject to a written quotation and the terms agreed with you at that time.</p>

    <h2>Your account</h2>
    <ul>
        <li>You are responsible for keeping your password confidential and for activity on your account.</li>
        <li>Please keep your account details accurate and up to date.</li>
        <li>We may suspend accounts that are used to misuse the website.</li>
    </ul>

    <h2>Acceptable use</h2>
    <p>Please do not misuse the website, for example by attempting to gain unauthorized access, interfering with its operation, or sending unsolicited or harmful content through our forms.</p>

    <h2>Intellectual property</h2>
    <p>The {{ $company }} name, logo and website content belong to us or are used with permission. Some photographs are used under open licences; see <a href="{{ route('credits') }}">Image credits</a>.</p>

    <h2>Links to other websites</h2>
    <p>We are not responsible for the content of external websites linked from this site.</p>

    <h2>Liability</h2>
    <p>We work to keep the website accurate and available, but we cannot guarantee that it will always be error-free or uninterrupted. To the extent permitted by law, we are not liable for losses arising from use of the website.</p>

    <h2>Changes to these terms</h2>
    <p>We may update these terms from time to time. The date at the top of this page shows when they were last changed.</p>

    <h2>Contact us</h2>
    <p>
        Questions about these terms?
        @if ($contact['email'])
            Email <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>.
        @else
            <a href="{{ route('contact') }}">Contact us</a>.
        @endif
    </p>
</x-legal-page>
