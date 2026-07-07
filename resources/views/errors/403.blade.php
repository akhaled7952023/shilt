<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 — غير مصرح</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800&display=swap">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Tajawal', sans-serif;
    direction: rtl;
    background: #1e293b;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.card {
    background: white;
    border-radius: 20px;
    padding: 48px 36px 40px;
    max-width: 440px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
}
.code { font-size: 72px; font-weight: 800; color: #dc2626; line-height: 1; margin-bottom: 6px; }
.title { font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
.msg { font-size: 14px; color: #64748b; line-height: 1.7; margin-bottom: 28px; }
.hint {
    font-size: 13px; color: #94a3b8;
    background: #f8fafc; border-radius: 10px;
    padding: 10px 14px; margin-bottom: 28px;
    border: 1px solid #e2e8f0;
}
.btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: #2563eb; color: white;
    border: none; border-radius: 10px;
    padding: 12px 24px;
    font-family: 'Tajawal', sans-serif;
    font-size: 15px; font-weight: 700;
    text-decoration: none; cursor: pointer;
    transition: background .2s;
}
.btn:hover { background: #1d4ed8; }
</style>
</head>
<body>
<div class="card">
    <div style="font-size:52px;margin-bottom:16px;">🔒</div>
    <div class="code">403</div>
    <div class="title">غير مصرح بالوصول</div>
    <div class="msg">
        {{ $exception?->getMessage() ?: 'ليس لديك صلاحية للوصول إلى هذه الصفحة أو المورد المطلوب.' }}
    </div>
    <div class="hint">
        إذا كنت تعتقد أن هذا خطأ، تواصل مع مشرفك للحصول على الصلاحية المناسبة.
    </div>
    @if(auth('delegate')->check())
        <a href="{{ route('portal.dashboard') }}" class="btn">العودة للرئيسية</a>
    @elseif(auth()->check())
        <a href="{{ url('/dashboard') }}" class="btn">العودة للوحة التحكم</a>
    @else
        <a href="{{ route('portal.login') }}" class="btn">تسجيل الدخول</a>
    @endif
</div>
</body>
</html>
