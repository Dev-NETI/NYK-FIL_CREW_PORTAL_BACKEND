<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Debriefing Form Confirmed - NYK-FIL Crew Portal</title>

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 40px 20px;
      min-height: 100vh;
    }
    .email-wrapper { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden; }
    .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding: 50px 40px; text-align: center; position: relative; overflow: hidden; }
    .header::before { content: ''; position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px); background-size: 50px 50px; animation: float 20s linear infinite; }
    @keyframes float { 0% { transform: translate(0,0); } 100% { transform: translate(-50px,-50px); } }
    .logo-container { position: relative; z-index: 1; margin-bottom: 20px; }
    .logo { width: 80px; height: 80px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 32px rgba(0,0,0,0.1); border: 2px solid rgba(255,255,255,0.3); }
    .logo-icon { font-size: 40px; color: #fff; }
    .header h1 { color:#fff; font-size: 28px; font-weight: 700; position: relative; z-index: 1; text-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .content { padding: 50px 40px; }
    .greeting { font-size: 18px; color:#1f2937; margin-bottom: 12px; font-weight: 700; }
    .message { font-size: 16px; color:#4b5563; line-height: 1.6; margin-bottom: 22px; }
    .app-badge { display:inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 8px 0 20px; }
    .info-box { background:#f0f9ff; border-left: 4px solid #0284c7; border-radius: 8px; padding: 20px; margin: 20px 0; }
    .info-box .info-label { font-size: 12px; font-weight: 700; color:#075985; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
    .info-box .info-value { font-size: 14px; color:#0c4a6e; line-height: 1.6; margin-bottom: 10px; }
    .info-box .info-value:last-child { margin-bottom: 0; }
    .cta { text-align: center; margin-top: 22px; }
    .btn { display:inline-block; background: linear-gradient(135deg, #059669 0%, #047857 100%); color:#fff; padding: 12px 18px; border-radius: 10px; text-decoration:none; font-weight: 700; font-size: 14px; }
    .footer { background:#f9fafb; padding: 35px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
    .divider { height: 1px; background: linear-gradient(90deg, transparent, #e5e7eb, transparent); margin: 10px 0 20px; }
    .support-text { font-size: 12px; color:#9ca3af; }
    @media only screen and (max-width: 600px) {
      body { padding: 20px 10px; }
      .email-wrapper { border-radius: 12px; }
      .header { padding: 35px 25px; }
      .header h1 { font-size: 24px; }
      .content { padding: 35px 25px; }
      .footer { padding: 25px 20px; }
    }
  </style>
</head>

<body>
  <div class="email-wrapper">
    <div class="header">
      <div class="logo-container">
        <div class="logo"><span class="logo-icon">⚓</span></div>
      </div>
      <h1>NYK-FIL Crew Portal</h1>
    </div>

    <div class="content">
      <div class="greeting">Debriefing Form Confirmed</div>

      <div class="message">
        Your debriefing form has been confirmed by the Manning Department. You may now download the official PDF copy.
      </div>

      <div class="app-badge">✅ CONFIRMED</div>

      <div class="info-box">
        <div class="info-label">Form Details</div>

        <div class="info-value"><strong>Crew:</strong> {{ $crew?->name ?? 'N/A' }}</div>
        <div class="info-value"><strong>Rank:</strong> {{ $form?->rank ?? 'N/A' }}</div>
        <div class="info-value"><strong>Vessel:</strong> {{ $form?->embarkation_vessel_name ?? 'N/A' }}</div>
        <div class="info-value">
          <strong>Confirmed At:</strong>
          {{ $form?->confirmed_at ? \Carbon\Carbon::parse($form->confirmed_at)->toDayDateTimeString() : 'N/A' }}
        </div>
      </div>

      <div class="cta">
        <a class="btn" href="{{ $downloadUrl }}">Download PDF</a>
      </div>

      <div class="message" style="margin-top: 24px; font-size: 14px; color: #6b7280;">
        This link is time-limited for security. If it expires, please log in to the Crew Portal to download your confirmed form.
      </div>
    </div>

    <div class="footer">
      <div class="divider"></div>
      <div class="support-text">© {{ date('Y') }} NYK-FIL Maritime E-Training, Inc. All rights reserved.</div>
    </div>
  </div>
</body>

</html>
