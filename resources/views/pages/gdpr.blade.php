@extends('layouts.public')

@section('title', 'GDPR & Data Safety | TravelAI Nepal')
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">GDPR & Data Safety</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Your data is safe and secure with us. We are fully GDPR compliant.
    </p>
</div>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
        <p class="text-gray-500 text-sm">Last updated: August 2026</p>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-700 text-sm"><i class="fas fa-shield-alt mr-2"></i> We are fully committed to GDPR compliance and data protection.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">1. GDPR Compliance</h2>
            <p class="text-gray-600 mt-2">We are committed to protecting your privacy and complying with the General Data Protection Regulation (GDPR) (EU) 2016/679.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">2. Data Processing</h2>
            <p class="text-gray-600 mt-2">We collect and process data only for purposes you have consented to, such as booking, communication, and service improvement.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">3. Data Storage</h2>
            <p class="text-gray-600 mt-2">Your data is stored on secure servers in data centers that comply with international security standards (ISO 27001).</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">4. Data Subject Rights</h2>
            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                <li><strong>Right to Access:</strong> You can request a copy of your data.</li>
                <li><strong>Right to Rectification:</strong> You can correct inaccurate data.</li>
                <li><strong>Right to Erasure:</strong> You can request deletion of your data.</li>
                <li><strong>Right to Restrict Processing:</strong> You can limit how we use your data.</li>
                <li><strong>Right to Data Portability:</strong> You can request your data in a machine-readable format.</li>
            </ul>
            <p class="text-gray-600 mt-2">Email us at <a href="mailto:support@travelai.com" class="text-blue-600 hover:underline">support@travelai.com</a> to exercise your rights.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">5. Data Breach Notification</h2>
            <p class="text-gray-600 mt-2">In the unlikely event of a data breach, we will notify affected users and relevant authorities within 72 hours, as required by GDPR.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">6. Third-Party Processors</h2>
            <p class="text-gray-600 mt-2">We use trusted third-party processors (Stripe, AWS, etc.) that are also GDPR compliant. All processors sign Data Processing Agreements.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">7. Data Retention</h2>
            <p class="text-gray-600 mt-2">We retain your data only as long as necessary for the purposes outlined, or as required by law. You may request earlier deletion.</p>
        </div>

        <div class="pt-4 border-t">
            <p class="text-sm text-gray-500">For any GDPR-related questions, contact our Data Protection Officer at <a href="mailto:dpo@travelai.com" class="text-blue-600 hover:underline">dpo@travelai.com</a></p>
        </div>
    </div>
</div>

@endsection