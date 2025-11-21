<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to InvoiceAI</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .credentials-box {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #495057;
        }
        .credential-item {
            margin: 10px 0;
        }
        .credential-label {
            font-weight: bold;
            color: #6c757d;
        }
        .credential-value {
            font-family: monospace;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to InvoiceAI!</h1>
        <p>Your account has been created</p>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>Your company account for <strong>{{ $company->name }}</strong> has been created and is ready to use.</p>
        
        <div class="credentials-box">
            <h3>Your Login Credentials</h3>
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $user->email }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password:</span>
                <span class="credential-value">{{ $password }}</span>
            </div>
        </div>
        
        <div class="warning-box">
            <strong>Important:</strong> For security reasons, please change your password after your first login.
        </div>
        
        <div class="info-box">
            <strong>Your Plan:</strong> {{ $plan->name }}<br>
            <strong>Invoice Limit:</strong> {{ $plan->invoice_limit == -1 ? 'Unlimited' : $plan->invoice_limit }} invoices/month<br>
            <strong>Access Valid Until:</strong> {{ \Carbon\Carbon::parse($gracePeriodEnd)->format('F j, Y') }}
        </div>
        
        <p>You have been granted complimentary access until the date above. No payment is required during this period.</p>
        
        <center>
            <a href="{{ url('/login') }}" class="button">Login to Your Account</a>
        </center>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>The InvoiceAI Team</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} InvoiceAI. All rights reserved.</p>
    </div>
</body>
</html>
