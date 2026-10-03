<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <meta name="theme-color" content="#0B1F3A">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="صحفي المؤتمر">
  <title>صحفي المؤتمر</title>
  <style>
    :root { color-scheme: dark; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; background:#071426; color:#fff; display:grid; place-items:center; padding:24px 16px; }
    .card { width:min(560px,100%); background:#0B1F3A; border:1px solid #263a56; border-radius:28px; padding:24px; box-shadow:0 20px 60px #0007; }
    h1 { margin:0 0 8px; font-size:28px; }
    p { color:#b9c4d3; line-height:1.7; }
    label { display:block; margin:18px 0 8px; color:#d7dfeb; font-size:14px; }
    input { width:100%; border:1px solid #344b6c; background:#071426; color:#fff; padding:14px 16px; border-radius:14px; font-size:16px; }
    .timer { font-variant-numeric:tabular-nums; text-align:center; font-size:42px; margin:28px 0 14px; letter-spacing:2px; }
    button { width:100%; min-height:64px; border:0; border-radius:18px; font-size:20px; font-weight:700; cursor:pointer; }
    #record { background:#fff; color:#0B1F3A; }
    #record.recording { background:#d93434; color:#fff; }
    #record:disabled { opacity:.55; cursor:not-allowed; }
    #retry { display:none; margin-top:12px; background:#f0b429; color:#0B1F3A; min-height:52px; font-size:17px; }
    .progress { height:8px; background:#071426; border-radius:8px; overflow:hidden; margin-top:14px; display:none; }
    .progress > div { height:100%; width:0; background:#4fa3ff; transition:width .2s; }
    .status { margin-top:18px; padding:14px 16px; border-radius:14px; background:#071426; border:1px solid #263a56; min-height:52px; line-height:1.6; word-break:break-word; }
    .hint { font-size:13px; color:#8fa1b8; text-align:center; margin-top:14px; line-height:1.6; }
  </style>
</head>
<body>
  <main class="card">
    <h1>🎙️ صحفي المؤتمر</h1>
    <p>سجّل الجلسة أو المقابلة. بعد الإيقاف يرتفع الصوت تلقائيًا ويتحول إلى نص على الماك.</p>

    <label for="token">رمز الدخول</label>
    <input id="token" type="password" autocomplete="off" placeholder="USER_API_TOKEN">

    <div id="timer" class="timer">00:00</div>
    <button id="record">ابدأ التسجيل</button>
    <button id="retry">إعادة محاولة الرفع</button>
    <div id="progress" class="progress"><div></div></div>
    <div id="status" class="status">جاهز للتسجيل.</div>
    <div class="hint">لا تقفل الشاشة أثناء التسجيل، واترك الصفحة مفتوحة حتى تظهر رسالة نجاح الرفع.</div>
  </main>

<script>
const tokenInput = document.getElementById('token');
const recordButton = document.getElementById('record');
const retryButton = document.getElementById('retry');
const statusBox = document.getElementById('status');
const timerBox = document.getElementById('timer');
const progressBox = document.getElementById('progress');
const progressBar = progressBox.firstElementChild;

try { tokenInput.value = localStorage.getItem('cj_user_token') || ''; } catch (_) {}

let recorder = null;
let stream = null;
let chunks = [];
let startedAt = null;
let timerHandle = null;
let wakeLock = null;
let pendingUpload = null;

function setStatus(text) { statusBox.textContent = text; }

function formatTime(ms) {
  const total = Math.floor(ms / 1000);
  const hours = Math.floor(total / 3600);
  const min = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
  const sec = String(total % 60).padStart(2, '0');
  return (hours ? hours + ':' : '') + min + ':' + sec;
}

function chooseMimeType() {
  const candidates = ['audio/webm;codecs=opus', 'audio/mp4', 'audio/webm', 'audio/ogg;codecs=opus'];
  return candidates.find(type => MediaRecorder.isTypeSupported(type)) || '';
}

function extensionFor(type) {
  if (type.includes('mp4')) return 'm4a';
  if (type.includes('ogg')) return 'ogg';
  return 'webm';
}

async function keepScreenAwake() {
  try { if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen'); } catch (_) {}
}

function releaseScreen() {
  if (wakeLock) { wakeLock.release().catch(() => {}); wakeLock = null; }
}

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible' && recorder && recorder.state === 'recording') keepScreenAwake();
});

async function startRecording() {
  const token = tokenInput.value.trim();
  if (!token) {
    setStatus('أدخل رمز الدخول أولًا.');
    return;
  }
  try { localStorage.setItem('cj_user_token', token); } catch (_) {}

  if (!navigator.mediaDevices || !window.MediaRecorder) {
    setStatus('❌ هذا المتصفح لا يدعم التسجيل. استخدم Safari أو Chrome بإصدار حديث.');
    return;
  }

  stream = await navigator.mediaDevices.getUserMedia({
    audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true }
  });

  const mimeType = chooseMimeType();
  recorder = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);
  chunks = [];

  recorder.ondataavailable = e => {
    if (e.data && e.data.size > 0) chunks.push(e.data);
  };

  recorder.onstop = finishRecording;
  recorder.start(1000);
  startedAt = Date.now();
  timerBox.textContent = '00:00';
  timerHandle = setInterval(() => { timerBox.textContent = formatTime(Date.now() - startedAt); }, 500);
  keepScreenAwake();

  retryButton.style.display = 'none';
  recordButton.textContent = 'إيقاف وإرسال';
  recordButton.classList.add('recording');
  setStatus('🔴 جارٍ التسجيل...');
}

function stopRecording() {
  if (!recorder || recorder.state === 'inactive') return;
  recorder.stop();
  if (stream) stream.getTracks().forEach(t => t.stop());
  clearInterval(timerHandle);
  releaseScreen();
  recordButton.disabled = true;
}

function finishRecording() {
  const type = recorder.mimeType || 'audio/webm';
  pendingUpload = {
    blob: new Blob(chunks, { type }),
    name: 'conference-' + Date.now() + '.' + extensionFor(type)
  };
  recorder = null;
  chunks = [];
  uploadRecording();
}

function sendForm(form, token) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/cj/recordings');
    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.onprogress = e => {
      if (e.lengthComputable) progressBar.style.width = Math.round(e.loaded / e.total * 100) + '%';
    };
    xhr.onload = () => {
      let data = {};
      try { data = JSON.parse(xhr.responseText); } catch (_) {}
      if (xhr.status >= 200 && xhr.status < 300) resolve(data);
      else reject(new Error(data.message || ('HTTP ' + xhr.status)));
    };
    xhr.onerror = () => reject(new Error('تعذر الاتصال بالسيرفر'));
    xhr.send(form);
  });
}

async function uploadRecording() {
  if (!pendingUpload) return;
  recordButton.disabled = true;
  retryButton.style.display = 'none';
  progressBar.style.width = '0';
  progressBox.style.display = 'block';
  setStatus('⬆️ انتهى التسجيل. جارٍ رفع الملف...');

  try {
    const form = new FormData();
    form.append('audio', pendingUpload.blob, pendingUpload.name);
    const data = await sendForm(form, tokenInput.value.trim());

    pendingUpload = null;
    setStatus('✅ تم حفظ التسجيل: ' + data.relay_id);
    pollStatus(data.relay_id);
  } catch (error) {
    setStatus('❌ فشل الرفع: ' + error.message + ' — التسجيل محفوظ في الصفحة، اضغط إعادة المحاولة.');
    retryButton.style.display = 'block';
  } finally {
    progressBox.style.display = 'none';
    recordButton.disabled = false;
    recordButton.textContent = 'ابدأ تسجيل جديد';
    recordButton.classList.remove('recording');
  }
}

async function pollStatus(relayId) {
  const token = tokenInput.value.trim();
  const labels = {
    processing: '🔵 جارٍ تنقية وتجهيز الصوت على سيرفر البيت',
    ready: '🟡 التسجيل جاهز وبانتظار الماك',
    transcribing: '🤖 الماك استلم التسجيل ويحوّله إلى نص',
    completed: '🟢 اكتمل التحويل وحُفظ النص',
    failed: '❌ فشلت معالجة التسجيل'
  };

  // حتى ساعتين -- تسجيل مؤتمر طويل ياخذ وقت في التحويل.
  for (let i = 0; i < 1440; i++) {
    await new Promise(r => setTimeout(r, 5000));
    try {
      const response = await fetch('/api/cj/recordings/' + relayId + '/status', {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
      });
      if (!response.ok) continue;
      const data = await response.json();
      if (data.status === 'received') {
        setStatus('📥 وصل التسجيل للموقع وبانتظار سيرفر البيت — ' + relayId);
        continue;
      }
      setStatus((labels[data.job_status] || data.job_status) + ' — ' + (data.job_id || relayId));
      if (data.job_status === 'completed' || data.job_status === 'failed') return;
    } catch (_) {}
  }
}

recordButton.addEventListener('click', async () => {
  try {
    if (recorder && recorder.state !== 'inactive') stopRecording();
    else await startRecording();
  } catch (error) {
    releaseScreen();
    setStatus('❌ تعذر بدء التسجيل: ' + error.message);
  }
});

retryButton.addEventListener('click', uploadRecording);

window.addEventListener('beforeunload', e => {
  if ((recorder && recorder.state !== 'inactive') || pendingUpload) {
    e.preventDefault();
    e.returnValue = '';
  }
});
</script>
</body>
</html>
