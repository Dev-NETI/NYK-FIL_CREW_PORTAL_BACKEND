<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Your One-Time Password</title>
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

        .otp-container {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #3b82f6;
            border-radius: 16px;
            padding: 35px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
        }

        .otp-label {
            font-size: 14px;
            color: #1e40af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .otp-code {
            font-size: 48px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 12px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(30, 58, 138, 0.1);
        }

        .otp-expiry {
            font-size: 13px;
            color: #3b82f6;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .clock-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
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

        .company-name {
            font-weight: 700;
            color: #2563eb;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .support-text {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 15px;
        }

        .support-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .support-link:hover {
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 25px 0;
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

            .otp-code {
                font-size: 36px;
                letter-spacing: 8px;
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
                Hello {{ $userName }},
            </div>

            <div class="message">
                We received a request to sign in to your <strong>NYK-FIL Crew Portal</strong> account. Please use the
                One-Time Password (OTP) below to complete your login:
            </div>

            <!-- OTP Box -->
            <div class="otp-container">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">
                    <svg class="clock-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Expires in <strong>{{ $expiryMinutes }} minutes</strong></span>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <div class="warning-title">
                    <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Security Notice
                </div>
                <div class="warning-text">
                    If you didn't request this code, please ignore this email. Never share your OTP with anyone. Our
                    staff will never ask you for this code.
                </div>
            </div>

            <div class="message" style="margin-top: 30px; font-size: 14px; color: #6b7280;">
                This is an automated message. Please do not reply to this email.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            {{-- <div class="company-name">NYK-FIL Ship Management, Inc.</div> --}}


            <div class="divider"></div>

            {{-- <div class="support-text">
                Need help? Contact us at <a href="mailto:support@nykfil.com" class="support-link">support@nykfil.com</a>
            </div> --}}

            <div class="support-text" style="margin-top: 20px;">
                © {{ date('Y') }} NYK-FIL Maritime E-Traning, Inc. All rights reserved.
            </div>
        </div>
    </div>
</body>

</html>
