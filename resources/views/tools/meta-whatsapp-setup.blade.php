<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>إعداد توكن واتساب — الياسي للبرمجيات</title>
<style>
body {
    font-family: Arial;
    background: #111;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.box {
    width: 380px;
    max-width: 92vw;
    background: #1d1d1d;
    padding: 30px;
    border-radius: 18px;
}
label {
    display: block;
    font-size: 13px;
    color: #aaa;
    margin-top: 14px;
}
input {
    width: 100%;
    box-sizing: border-box;
    padding: 14px;
    margin-top: 6px;
    border-radius: 10px;
    border: 1px solid #555;
    background: #111;
    color: white;
    font-size: 15px;
}
button {
    width: 100%;
    padding: 14px;
    border: 0;
    border-radius: 10px;
    background: #03a9d9;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
}
.success {
    background: #1b3d2a;
    border: 1px solid #2f7a4f;
    color: #8fe3b0;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 16px;
    font-size: 14px;
}
</style>
</head>
<body>
<div class="box">
<h2>توكن واتساب — الياسي للبرمجيات</h2>

@if (session('success'))
<div class="success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('tools.meta-whatsapp-setup.store') }}">
@csrf
<label>Phone Number ID</label>
<input type="text" name="phone_id" placeholder="623727094163898" required autocomplete="off">
<label>Access Token</label>
<input type="password" name="meta_token" placeholder="META_TOKEN" required autocomplete="off">
<button type="submit">حفظ</button>
</form>
</div>
</body>
</html>
