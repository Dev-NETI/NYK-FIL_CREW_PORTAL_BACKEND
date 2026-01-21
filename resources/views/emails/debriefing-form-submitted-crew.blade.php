<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Debriefing Form Submitted - NYK-FIL Crew Portal</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 40px 20px;
      min-height: 100vh;
    }
    .email-wrapper { max-width:600px; margin:0 auto; background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.15); overflow:hidden; }
    .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding:50px 40px; text-align:center; position:relative; overflow:hidden; }
    .logo { width:80px; height:80px; background:rgba(255,255,255,0.2); border-radius:20px; display:inline-flex; align-items:center; justify-content:center; border:2px solid rgba(255,255,255,0.3); margin-bottom:20px; }
    .logo-icon { font-size:40px; color:#fff; }
    .header h1 { color:#fff; font-size:28px; font-weight:700; }
    .content { padding:50px 40px; }
    .greeting { font-size:18px; color:#1f2937; margin-bottom:12px; font-weight:700; }
    .message { font-size:16px; color:#4b5563; line-height:1.6; margin-bottom:22px; }
    .app-badge { display:inline-block; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color:#fff; padding:8px 16px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin:8px 0 20px; }
    .info-box { background:#f0f9ff; border-left:4px solid #0284c7; border-radius:8px; padding:20px; margin:20px 0; }
    .info-label { font-size:12px; font-weight:700; color:#075985; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px; }
    .info-value { font-size:14px; color:#0c4a6e; line-height:1.6; margin-bottom:10px; }
    .info-value:last-child { margin-bottom:0; }
    .footer { background:#f9fafb; padding:35px 40px; text-align:center; border-top:1px solid #e5e7eb; }
    .divider { height:1px; background:linear-gradient(90deg, transparent, #e5e7eb, transparent); margin:10px 0 20px; }
    .support-text { font-size:12px; color:#9ca3af; }
    @media only screen and (max-width: 600px) {
      body { padding:20px 10px; }
      .email-wrapper { border-radius:12px; }
      .header { padding:35px 25px; }
      .header h1 { font-size:24px; }
      .content { padding:35px 25px; }
      .footer { padding:25px 20px; }
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="header">
      <div class="logo"><span class="logo-icon">⚓</span></div>
      <h1>NYK-FIL Crew Portal</h1>
    </div>

    <div class="content">
      <div class="greeting">Debriefing Form Submitted</div>

      <div class="message">
        We have received your debriefing form submission. The Manning Department will review it.
        You will receive another email once the form is confirmed and the official PDF is ready for download.
      </div>

      <div class="app-badge">📨 SUBMITTED</div>

      <div class="info-box">
        <div class="info-label">Submission Details</div>
        <div class="info-value"><strong>Form #:</strong> {{ $form->id }}</div>
        <div class="info-value"><strong>Crew:</strong> {{ $crew?->name ?? 'N/A' }}</div>
        <div class="info-value"><strong>Rank:</strong> {{ $form?->rank ?? 'N/A' }}</div>
        <div class="info-value">
          <strong>Submitted At:</strong>
          {{ $form?->submitted_at ? \Carbon\Carbon::parse($form->submitted_at)->toDayDateTimeString() : 'N/A' }}
        </div>
      </div>

      <div class="message" style="font-size: 14px; color:#6b7280;">
        If you need to update something, please contact the Manning Department.
      </div>
    </div>

    <div class="footer">
      <div class="divider"></div>
      <div class="support-text">© {{ date('Y') }} NYK-FIL Maritime E-Training, Inc. All rights reserved.</div>
    </div>
  </div>
</body>
</html>
