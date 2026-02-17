<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document Submission Notification</title>
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

        .document-container {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #3b82f6;
            border-radius: 16px;
            padding: 35px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
        }

        .document-label {
            font-size: 14px;
            color: #1e40af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
        }

        .document-info {
            margin: 15px 0;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #bfdbfe;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 14px;
            color: #1e40af;
            font-weight: 600;
            min-width: 150px;
        }

        .info-value {
            font-size: 14px;
            color: #1e3a8a;
            font-weight: 500;
            flex: 1;
        }

        .action-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .action-created {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
        }

        .action-updated {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
        }

        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-box .info-title {
            font-size: 14px;
            font-weight: 700;
            color: #075985;
            margin-bottom: 8px;
        }

        .info-box .info-text {
            font-size: 14px;
            color: #0c4a6e;
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
                Hello Admin,
            </div>

            <div class="message">
                A crew member has <strong>{{ $action === 'created' ? 'submitted a new' : 'requested to update an existing' }}</strong>
                {{ $documentCategory }} document that requires your review and approval.
            </div>

            <!-- Document Details Box -->
            <div class="document-container">
                <div class="document-label">Document Submission Details</div>

                <div class="document-info">
                    <div class="info-row">
                        <span class="info-label">Crew Member:</span>
                        <span class="info-value">{{ $crewName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Crew ID:</span>
                        <span class="info-value">{{ $crewId }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Document Category:</span>
                        <span class="info-value">{{ $documentCategory }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Document Type:</span>
                        <span class="info-value">{{ $documentType }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Action:</span>
                        <span class="info-value">
                            <span class="action-badge {{ $action === 'created' ? 'action-created' : 'action-updated' }}">
                                {{ $action === 'created' ? 'New Document' : 'Update Request' }}
                            </span>
                        </span>
                    </div>
                    @if (!empty($documentDetails))
                        @foreach ($documentDetails as $key => $value)
                            <div class="info-row">
                                <span class="info-label">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="info-value">{{ $value }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-title">Action Required</div>
                <div class="info-text">
                    Please log in to the NYK-FIL Crew Portal admin panel to review and process this document submission.
                    Navigate to the document approvals section to approve or reject this request.
                </div>
            </div>

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
