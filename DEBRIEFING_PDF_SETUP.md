# Debriefing Form PDF Generation — Setup & Operations Guide

## Overview

When an admin **confirms** a debriefing form, the system queues a background job that:

1. Fills an Excel template (`debriefing_template.xlsx`) with form data
2. Converts it to a PDF using mPDF
3. Saves the PDF to `storage/app/debriefing_pdfs/`
4. Emails the crew a signed download link (valid 7 days)

PDF generation is **asynchronous** — it runs via Laravel's queue system. The portal UI polls every 4 seconds and updates the badge from `Generating…` → `Ready` when done.

---

## Required: Queue Worker

**PDF generation will not work unless a queue worker is running.**

```bash
cd CREW_PORTAL_BACKEND
php artisan queue:listen --verbose
```

Keep this terminal open alongside the Laravel server and Next.js dev server.

### Full Local Dev Setup (3 terminals)

| Terminal | Directory                      | Command                              |
| -------- | ------------------------------ | ------------------------------------ |
| 1        | `CREW_PORTAL_BACKEND`          | `composer dev`                       |
| 2        | `CREW_PORTAL_BACKEND`          | `php artisan queue:listen --verbose` |
| 3        | `NYK-FIL_CREW_PORTAL_FRONTEND` | `npm run dev`                        |

---

## Prerequisites Checklist

### 1. Excel Template

The PDF is generated from an Excel template. Verify it exists:

```bash
ls storage/app/private/templates/debriefing_template.xlsx
```

The template must have a sheet named **"DBF"**. Cell mappings:

| Data                    | Cell     |
| ----------------------- | -------- |
| Rank                    | D10      |
| Crew name               | D12      |
| Vessel (principal)      | L10, L11 |
| Embarkation vessel      | D16      |
| Embarkation place       | D18      |
| Embarkation date        | L16      |
| Disembarkation date     | D22      |
| Disembarkation place    | D24      |
| Manila arrival          | L22      |
| Address / phone / email | D28–K36  |
| Medical section         | E43–F58  |
| Comments Q1–Q6          | A64–A87  |

### 2. PDF Output Directory

```bash
# Must exist and be writable
ls storage/app/debriefing_pdfs/

# Create if missing
mkdir -p storage/app/debriefing_pdfs
```

### 3. PHP Extensions & Libraries

mPDF requires the following PHP extensions:

```bash
php -m | grep -E "gd|mbstring|zip"
```

All three (`gd`, `mbstring`, `zip`) must be listed.

Verify PhpSpreadsheet/mPDF is installed:

```bash
composer show | grep -E "phpoffice|mpdf"
```

### 4. Queue Database Table

```bash
php artisan migrate --status | grep jobs
```

The `jobs` and `failed_jobs` tables must exist. If not:

```bash
php artisan queue:table
php artisan migrate
```

### 5. Mail / SMTP Settings (`.env`)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="NYK-FIL Crew Portal"
```

> For Gmail, use an **App Password** (not your account password). Go to Google Account → Security → 2-Step Verification → App passwords.

### 6. App URL (for signed PDF download links)

```env
APP_URL=http://localhost:8000
```

This must be set correctly or the signed email links will be invalid.

---

## PDF Status Flow

```
[Admin clicks Confirm]
        │
        ▼
  pdf_status = pending        ← set by AdminDebriefingFormController
        │
        ▼  (queue worker picks up job)
  pdf_status = generating     ← set by GenerateDebriefingPdfJob::handle()
        │
   ┌────┴────┐
   ▼         ▼
 ready      failed            ← set on success / exception
```

### Status badges in the UI

| Badge                 | Meaning                                         |
| --------------------- | ----------------------------------------------- |
| `Generating…` (amber) | Job is queued or running                        |
| `Ready` (green)       | PDF generated, preview/download available       |
| `Failed` (red)        | Job threw an exception; error shown below badge |

---

## Troubleshooting

### PDF stuck at "Generating…"

**Cause:** Queue worker is not running.
**Fix:** Start `php artisan queue:listen --verbose` in a separate terminal.

### PDF status stays "Generating…" even with worker running

**Cause:** The job may have already failed internally (status not updated to `failed`).
**Fix:** Check failed jobs:

```bash
php artisan queue:failed
```

If entries appear, inspect the error message, fix the root cause, then:

```bash
php artisan queue:flush   # clear failed jobs
```

Then use the **Regenerate PDF** button (refresh icon) in the debriefing table.

### "Failed" badge with error message

The error is stored in `debriefing_forms.pdf_error` and shown below the badge. Common causes:

- Template file missing or wrong sheet name
- `storage/app/debriefing_pdfs/` directory not writable
- PHP extension missing (`gd`, `mbstring`)
- mPDF not installed

### Email not received by crew

- Verify SMTP credentials in `.env`
- Check `debriefing_forms.pdf_emailed_at` — if set, email was sent (check spam)
- If `pdf_emailed_at` is null but status is `ready`, the email step failed silently — check `storage/logs/laravel.log`

### Signed PDF download link expired

Links are valid for **7 days**. After expiry, the crew cannot download via the emailed link.
Admin can still preview/download directly from the admin portal (no expiry on admin side).

---

## Key Files

| File                                                         | Purpose                                            |
| ------------------------------------------------------------ | -------------------------------------------------- |
| `app/Jobs/GenerateDebriefingPdfJob.php`                      | Queue job — orchestrates generation + email        |
| `app/Services/DebriefingPdfService.php`                      | Fills Excel template, converts to PDF via mPDF     |
| `app/Http/Controllers/Api/AdminDebriefingFormController.php` | `confirm()` and `regeneratePdf()` dispatch the job |
| `app/Http/Controllers/Api/DebriefingPdfLinkController.php`   | Handles signed URL PDF downloads                   |
| `app/Mail/DebriefingFormConfirmedMail.php`                   | Email class sent to crew after generation          |
| `storage/app/private/templates/debriefing_template.xlsx`     | Excel template (sheet: "DBF")                      |
| `storage/app/debriefing_pdfs/`                               | Generated PDF output directory                     |
| `config/queue.php`                                           | Queue driver config (`database`)                   |
| `.env` → `QUEUE_CONNECTION`                                  | Must be `database`                                 |

---

## Regenerating a PDF

If a PDF fails or needs to be regenerated:

1. Go to **Admin → Debriefing Forms**
2. Find the confirmed form (filter by "Confirmed")
3. Click the **refresh icon** (Regenerate PDF button — only visible when status is `failed`)
4. Ensure the queue worker is running — the job will be re-queued immediately

Alternatively, via artisan (resets pdf fields and re-dispatches):

```bash
# There is no direct artisan command — use the UI regenerate button
# or manually dispatch via tinker:
php artisan tinker
>>> \App\Jobs\GenerateDebriefingPdfJob::dispatch(FORM_ID);
```
