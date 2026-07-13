<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $title }}</title>
<style>
/* Reset */
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table,td{mso-table-lspace:0;mso-table-rspace:0}
img{-ms-interpolation-mode:bicubic;border:0;height:auto;line-height:100%;outline:none;text-decoration:none}
body{margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Arial,sans-serif;direction:rtl}

/* Wrapper */
.email-wrapper{width:100%;background:#f1f5f9;padding:28px 0}
.email-card{max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}

/* Header */
.email-header{background:#1e293b;padding:20px 28px}
.email-header-inner{display:flex;align-items:center;gap:14px}
.email-logo{height:38px;border-radius:5px;vertical-align:middle}
.email-brand{color:#94a3b8;font-size:13px;vertical-align:middle;display:inline-block;margin-right:12px}

/* Accent bar */
.accent-bar{height:4px;background:linear-gradient(90deg,#2563eb 0%,#1d4ed8 100%)}

/* Body */
.email-body{padding:36px 32px 28px}
.notif-category{font-size:12px;font-weight:600;color:#2563eb;letter-spacing:.06em;text-transform:uppercase;margin:0 0 10px;opacity:.85}
.notif-title{font-size:20px;font-weight:700;color:#0f172a;margin:0 0 14px;line-height:1.45}
.notif-text{font-size:15px;color:#475569;line-height:1.8;margin:0 0 28px;white-space:pre-line}
.btn-row{text-align:center;margin-bottom:8px}
.btn-link{background:#2563eb;color:#ffffff !important;text-decoration:none;padding:13px 36px;border-radius:7px;font-size:15px;font-weight:600;display:inline-block;letter-spacing:.02em}
.btn-link:hover{background:#1d4ed8}

/* Divider */
.divider{border:none;border-top:1px solid #e2e8f0;margin:0}

/* Footer */
.email-footer{background:#f8fafc;padding:18px 28px;text-align:center}
.email-footer p{margin:0;font-size:12px;color:#94a3b8;line-height:1.6}
.email-footer a{color:#64748b;text-decoration:none}

/* Mobile */
@media only screen and (max-width:620px){
    .email-wrapper{padding:8px 0}
    .email-body{padding:24px 18px 20px}
    .email-header{padding:16px 18px}
    .btn-link{padding:11px 24px;font-size:14px}
}
</style>
</head>
<body>

<div class="email-wrapper">
    <div class="email-card">

        {{-- ── Header ─────────────────────────────────────────────────────── --}}
        <div class="email-header">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="email-logo">
                <span class="email-brand">{{ $companyName }}</span>
            @else
                <span style="color:#e2e8f0;font-size:17px;font-weight:700;">{{ $companyName }}</span>
            @endif
        </div>

        <div class="accent-bar"></div>

        {{-- ── Body ───────────────────────────────────────────────────────── --}}
        <div class="email-body">

            @php
                $categoryLabel = match($category) {
                    \App\Enums\NotificationCategory::SettlementPublished           => 'كشف الراتب',
                    \App\Enums\NotificationCategory::SettlementViewed              => 'التسويات',
                    \App\Enums\NotificationCategory::TicketNew                     => 'تذكرة جديدة',
                    \App\Enums\NotificationCategory::TicketReply                   => 'رد على تذكرة',
                    \App\Enums\NotificationCategory::TicketReopened                => 'إعادة فتح تذكرة',
                    \App\Enums\NotificationCategory::TicketClosed                  => 'إغلاق تذكرة',
                    \App\Enums\NotificationCategory::FinancialRequestApproved      => 'طلب مالي',
                    \App\Enums\NotificationCategory::FinancialRequestRejected      => 'طلب مالي',
                    \App\Enums\NotificationCategory::IqamaExpiring                 => 'انتهاء الإقامة',
                    \App\Enums\NotificationCategory::PassportExpiring              => 'انتهاء الجواز',
                    \App\Enums\NotificationCategory::DrivingLicenseExpiring        => 'انتهاء رخصة القيادة',
                    \App\Enums\NotificationCategory::VehicleRegistrationExpiring   => 'انتهاء تسجيل المركبة',
                    \App\Enums\NotificationCategory::VehicleInsuranceExpiring      => 'انتهاء تأمين المركبة',
                    default                                                        => 'إشعار',
                };
            @endphp

            <div class="notif-category">{{ $categoryLabel }}</div>
            <div class="notif-title">{{ $title }}</div>
            <div class="notif-text">{{ $body }}</div>

            @if(!empty($actionUrl))
                <div class="btn-row">
                    <a href="{{ $actionUrl }}" class="btn-link">فتح في النظام &larr;</a>
                </div>
            @endif

        </div>

        <hr class="divider">

        {{-- ── Footer ─────────────────────────────────────────────────────── --}}
        <div class="email-footer">
            <p>هذه رسالة تلقائية من نظام <strong>{{ $companyName }}</strong> — الرجاء عدم الرد عليها مباشرةً.</p>
            @if(!empty($actionUrl))
                <p style="margin-top:6px"><a href="{{ $actionUrl }}">{{ $actionUrl }}</a></p>
            @endif
        </div>

    </div>
</div>

</body>
</html>
