@extends('emails.layouts.base')

@section('title', 'Verify Your Email')

@section('content')
    <div class="greeting">Verify Your Email Address</div>
    
    <p>Thanks for signing up! Please click the button below to verify your email address and activate your account.</p>
    
    <div class="text-center mb-6">
        <a href="{{ $url }}" class="button button-success">Verify Email Address</a>
    </div>
    
    <div class="info-box">
        <h4>Why verify?</h4>
        <p>Verifying your email helps us ensure the security of your account and allows you to receive important notifications about your invoices.</p>
    </div>
    
    <p class="text-muted text-small">If you did not create an account, no further action is required.</p>
    
    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
    
    <p class="text-muted text-small">If you're having trouble clicking the button, copy and paste this URL into your browser:</p>
    <p class="text-small" style="word-break: break-all; color: #2563eb;">{{ $url }}</p>
@endsection
