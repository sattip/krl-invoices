@extends('emails.layouts.base')

@section('title', 'Reset Your Password')

@section('content')
    <div class="greeting">Reset Your Password</div>
    
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <div class="text-center mb-6">
        <a href="{{ $url }}" class="button">Reset Password</a>
    </div>
    
    <div class="info-box warning">
        <h4>Link Expires Soon</h4>
        <p>This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
    </div>
    
    <p class="text-muted text-small">If you did not request a password reset, no further action is required. Your account is secure.</p>
    
    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
    
    <p class="text-muted text-small">If you're having trouble clicking the button, copy and paste this URL into your browser:</p>
    <p class="text-small" style="word-break: break-all; color: #2563eb;">{{ $url }}</p>
@endsection
