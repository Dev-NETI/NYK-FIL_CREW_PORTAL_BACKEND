<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document Status Update</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(-50px, -50px);
            }
        }

        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo-icon {
            font-size: 40px;
            color: #ffffff;
        }

        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .content {
            padding: 50px 40px;
        }

        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message {
            font-size: 16px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .status-container {
            border-radius: 16px;
            padding: 35px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .status-approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
        }

        .status-rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #ef4444;
        }

        .status-label {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
        }

        .status-label-approved {
            color: #065f46;
        }

        .status-label-rejected {
            color: #991b1b;
        }

        .status-badge {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 15px 0;
            text-align: center;
            width: 100%;
        }

        .badge-approved {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
        }

        .badge-rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }

        .document-info {
            margin: 20px 0;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 14px;
            font-weight: 600;
            min-width: 150px;
        }

        .info-label-approved {
            color: #065f46;
        }

        .info-label-rejected {
            color: #991b1b;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            flex: 1;
        }

        .info-value-approved {
            color: #064e3b;
        }

        .info-value-rejected {
            color: #7f1d1d;
        }

        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .success-box .box-title {
            font-size: 14px;
            font-weight: 700;
            color: #065f46;
            margin-bottom: 8px;
        }

        .success-box .box-text {
            font-size: 14px;
            color: #064e3b;
            line-height: 1.5;
        }

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .warning-box .warning-title {
            font-size: 15px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-box .warning-text {
            font-size: 14px;
            color: #78350f;
            line-height: 1.5;
        }

        .rejection-box {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .rejection-box .box-title {
            font-size: 14px;
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 8px;
        }

        .rejection-box .box-text {
            font-size: 14px;
            color: #7f1d1d;
            line-height: 1.5;
        }

        .footer {
            background: #f9fafb;
            padding: 35px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer-text {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 25px 0;
        }

        .support-text {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 15px;
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .email-wrapper {
                border-radius: 12px;
            }

            .header {
                padding: 35px 25px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 35px 25px;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                margin-bottom: 5px;
            }

            .footer {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <span class="logo-icon">⚓</span>
                </div>
            </div>
            <h1>NYK-FIL Super App</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello {{ $crewName }},
            </div>

            <div class="message">
                Your {{ $documentCategory }} document has been reviewed by the admin team.
                @if ($status === 'approved')
                    We are pleased to inform you that your document has been <strong>approved</strong>.
                @else
                    We regret to inform you that your document submission has been <strong>rejected</strong>.
                @endif
            </div>

            <!-- Status Box -->
            <div class="status-container {{ $status === 'approved' ? 'status-approved' : 'status-rejected' }}">
                <div class="status-label {{ $status === 'approved' ? 'status-label-approved' : 'status-label-rejected' }}">
                    Document Review Result
                </div>

                <div class="status-badge {{ $status === 'approved' ? 'badge-approved' : 'badge-rejected' }}">
                    {{ $status === 'approved' ? '✓ APPROVED' : '✗ REJECTED' }}
                </div>

                <div class="document-info">
                    <div class="info-row">
                        <span class="info-label {{ $status === 'approved' ? 'info-label-approved' : 'info-label-rejected' }}">Document Category:</span>
                        <span class="info-value {{ $status === 'approved' ? 'info-value-approved' : 'info-value-rejected' }}">{{ $documentCategory }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label {{ $status === 'approved' ? 'info-label-approved' : 'info-label-rejected' }}">Document Type:</span>
                        <span class="info-value {{ $status === 'approved' ? 'info-value-approved' : 'info-value-rejected' }}">{{ $documentType }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label {{ $status === 'approved' ? 'info-label-approved' : 'info-label-rejected' }}">Reviewed By:</span>
                        <span class="info-value {{ $status === 'approved' ? 'info-value-approved' : 'info-value-rejected' }}">{{ $reviewerName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label {{ $status === 'approved' ? 'info-label-approved' : 'info-label-rejected' }}">Review Date:</span>
                        <span class="info-value {{ $status === 'approved' ? 'info-value-approved' : 'info-value-rejected' }}">{{ date('F d, Y - h:i A') }}</span>
                    </div>
                    @if (!empty($documentDetails))
                        @foreach ($documentDetails as $key => $value)
                            <div class="info-row">
                                <span class="info-label {{ $status === 'approved' ? 'info-label-approved' : 'info-label-rejected' }}">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="info-value {{ $status === 'approved' ? 'info-value-approved' : 'info-value-rejected' }}">{{ $value }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            @if ($status === 'approved')
                <!-- Approval Success Box -->
                <div class="success-box">
                    <div class="box-title">✓ Next Steps</div>
                    <div class="box-text">
                        Your document has been successfully added to your profile. You can view and manage your documents
                        by logging into the NYK-FIL Crew Portal.
                    </div>
                </div>
            @else
                <!-- Rejection Details Box -->
                @if ($rejectionReason)
                    <div class="rejection-box">
                        <div class="box-title">Reason for Rejection</div>
                        <div class="box-text">
                            {{ $rejectionReason }}
                        </div>
                    </div>
                @endif

                <div class="warning-box">
                    <div class="warning-title">
                        <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        What to do next
                    </div>
                    <div class="warning-text">
                        Please review the rejection reason above and make the necessary corrections to your document.
                        You may resubmit your document after addressing the issues mentioned.
                    </div>
                </div>
            @endif

            <div class="message" style="margin-top: 30px; font-size: 14px; color: #6b7280;">
                This is an automated notification from the NYK-FIL Crew Portal system. Please do not reply to this email.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="divider"></div>

            <div class="support-text" style="margin-top: 20px;">
                © {{ date('Y') }} NYK-FIL Maritime E-Training, Inc. All rights reserved.
            </div>
        </div>
    </div>
</body>

</html>
