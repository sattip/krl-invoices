<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'InvoiceAI')</title>
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
        
        /* Base */
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
        }
        
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 0;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 32px 40px;
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        
        .logo span {
            font-weight: 300;
        }
        
        /* Content */
        .content {
            padding: 40px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
        }
        
        p {
            margin: 0 0 16px;
            color: #475569;
        }
        
        /* Buttons */
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            margin: 8px 0;
            text-align: center;
        }
        
        .button-secondary {
            background-color: #64748b;
        }
        
        .button-success {
            background-color: #10b981;
        }
        
        .button-warning {
            background-color: #f59e0b;
        }
        
        .button-danger {
            background-color: #ef4444;
        }
        
        /* Info Box */
        .info-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .info-box.success {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
        }
        
        .info-box.warning {
            background-color: #fffbeb;
            border-color: #fde68a;
        }
        
        .info-box.danger {
            background-color: #fef2f2;
            border-color: #fecaca;
        }
        
        .info-box h4 {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .info-box p {
            margin: 0;
            font-size: 14px;
        }
        
        /* Credentials Box */
        .credentials-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .credentials-box h4 {
            margin: 0 0 16px;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .credential-row {
            display: flex;
            margin: 8px 0;
        }
        
        .credential-label {
            font-weight: 600;
            color: #64748b;
            font-size: 13px;
            min-width: 100px;
        }
        
        .credential-value {
            font-family: 'Monaco', 'Menlo', monospace;
            background-color: #e2e8f0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
            color: #1e293b;
        }
        
        /* Details Table */
        .details-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        
        .details-table th,
        .details-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        
        .details-table th {
            font-weight: 600;
            color: #64748b;
            background-color: #f8fafc;
        }
        
        .details-table td {
            color: #1e293b;
        }
        
        /* Footer */
        .footer {
            padding: 24px 40px;
            background-color: #f8fafc;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }
        
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        
        /* Utilities */
        .text-center { text-align: center; }
        .text-muted { color: #64748b; }
        .text-small { font-size: 13px; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        
        /* Responsive */
        @media only screen and (max-width: 620px) {
            .wrapper { padding: 20px 16px; }
            .content { padding: 24px; }
            .header { padding: 24px; }
            .footer { padding: 20px 24px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <a href="{{ url('/') }}" class="logo">Invoice<span>AI</span></a>
            </div>
            
            <!-- Content -->
            <div class="content">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} InvoiceAI. All rights reserved.</p>
                <p style="margin-top: 8px;">
                    <a href="{{ url('/') }}">Visit Website</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
