<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Appointment Confirmed</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f4f6f8; color:#333; }
    .card { max-width:600px; margin:30px auto; background:#fff; border-radius:8px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
    .hdr { font-size:20px; font-weight:700; margin-bottom:12px; color:#059669; }
    .meta { font-size:14px; color:#6b7280; margin-bottom:18px; }
    .row { margin-bottom:12px; }
    .label { font-weight:600; color:#111827; display:block; margin-bottom:4px; }
    .value { color:#374151; }
    .cta { margin-top:20px; text-align:center; }
    .btn { display:inline-block; background:#059669; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; }
    .foot { margin-top:18px; font-size:12px; color:#9ca3af; }
  </style>
</head>
<body>
  <div class="card">
    <div class="hdr">Appointment Confirmed</div>
    <div class="meta">Your appointment is confirmed.</div>

    <div class="row">
      <span class="label">Crew</span>
      <div class="value">{{ $crew?->name ?? 'N/A' }}</div>
    </div>

    <div class="row">
      <span class="label">Department</span>
      <div class="value">{{ $department?->name ?? 'N/A' }}</div>
    </div>

    <div class="row">
      <span class="label">When</span>
      <div class="value">{{ \Carbon\Carbon::parse($appointment->date)->toFormattedDateString() }} at {{ \Carbon\Carbon::parse($appointment->time)->format('g:i A') }}</div>
    </div>

    <div class="cta">
      <a class="btn" href="#">Open Appointment</a>
    </div>

    <div class="foot">Contact the department if you need to reschedule.</div>
  </div>
</body>
</html>
