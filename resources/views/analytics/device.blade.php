<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>استبعاد هذا الجهاز من الإحصائيات</title>
    <style>
        :root { --bg: #f6f7f9; --card: #fff; --text: #14161a; --muted: #5d6470; --ok: #0f8a5f; --warn: #b4560b; --btn: #14161a; --btn-text: #fff; --line: #e3e6ea; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0f1114; --card: #181b20; --text: #eef0f3; --muted: #9aa3ae; --ok: #3ccf91; --warn: #f0a35a; --btn: #eef0f3; --btn-text: #0f1114; --line: #2a2f37; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 16px; background: var(--bg); color: var(--text); font-family: -apple-system, "SF Arabic", "Segoe UI", Tahoma, sans-serif; }
        .card { width: 100%; max-width: 420px; background: var(--card); border: 1px solid var(--line); border-radius: 18px; padding: 28px 22px; text-align: center; }
        .state { font-size: 20px; font-weight: 700; margin: 6px 0 10px; }
        .state.is-excluded { color: var(--ok); }
        .state.is-counted { color: var(--warn); }
        p { color: var(--muted); line-height: 1.8; margin: 0 0 20px; font-size: 15px; }
        a.btn { display: block; padding: 14px; border-radius: 12px; background: var(--btn); color: var(--btn-text); text-decoration: none; font-weight: 700; }
        a.link { display: inline-block; margin-top: 16px; color: var(--muted); font-size: 14px; }
        .flash { font-size: 14px; color: var(--muted); margin-bottom: 4px; }
    </style>
</head>
<body>
    <main class="card">
        @if ($changed)
            <div class="flash">تم الحفظ على هذا الجهاز ✓</div>
        @endif

        @if ($excluded)
            <div class="state is-excluded">هذا الجهاز مستبعد ✓</div>
            <p>زياراتك من هذا المتصفح تنحفظ كـ«سالم» وما تدخل أرقام الزوار ولا التفاعل. إذا مسحت بيانات المتصفح، افتح الرابط مرة ثانية.</p>
            <a class="btn" href="{{ route('analytics.device', ['key' => $key, 'do' => 'include']) }}">إلغاء الاستبعاد (احسب زياراتي)</a>
        @else
            <div class="state is-counted">هذا الجهاز ينحسب كزائر</div>
            <p>اضغط الزر عشان زياراتك من هذا المتصفح ما تنحسب بالإحصائيات. افتح نفس الرابط من كل جهاز ومتصفح تستخدمه.</p>
            <a class="btn" href="{{ route('analytics.device', ['key' => $key, 'do' => 'exclude']) }}">استبعد هذا الجهاز</a>
        @endif

        <a class="link" href="{{ url('/') }}">الرجوع للموقع</a>
    </main>
</body>
</html>
