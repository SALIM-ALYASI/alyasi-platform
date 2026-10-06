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
    .meter { display:none; margin:0 0 14px; }
    .meter canvas { display:block; width:100%; height:56px; border-radius:12px; background:#071426; }
    .meter-note { min-height:22px; margin-top:6px; text-align:center; font-size:14px; color:#8fa1b8; }
    .meter-note.warn { color:#f0b429; }
    button { width:100%; min-height:64px; border:0; border-radius:18px; font-size:20px; font-weight:700; cursor:pointer; }
    #record { background:#fff; color:#0B1F3A; }
    #record.recording { background:#d93434; color:#fff; }
    #record:disabled { opacity:.55; cursor:not-allowed; }
    .status { margin-top:18px; padding:14px 16px; border-radius:14px; background:#071426; border:1px solid #263a56; min-height:52px; line-height:1.6; word-break:break-word; }
    .hint { font-size:13px; color:#8fa1b8; text-align:center; margin-top:14px; line-height:1.6; }
  </style>
</head>
<body>
  <main class="card">
    <h1>🎙️ صحفي المؤتمر</h1>
    <p>سجّل الجلسة أو المقابلة. بعد الإيقاف يرتفع الصوت تلقائيًا ويتحول إلى نص على الماك.</p>


    <div id="timer" class="timer">00:00</div>
    <div id="meter" class="meter">
      <canvas id="meter-canvas" width="560" height="56"></canvas>
      <div id="meter-note" class="meter-note"></div>
    </div>
    <button id="record">ابدأ التسجيل</button>
    <div id="status" class="status">جاهز للتسجيل.</div>
    <div class="hint">لا تقفل الشاشة أثناء التسجيل، واترك الصفحة مفتوحة حتى تظهر رسالة نجاح الرفع.</div>
  </main>

<script>
const recordButton = document.getElementById('record');
const statusBox = document.getElementById('status');
const timerBox = document.getElementById('timer');
const meterBox = document.getElementById('meter');
const meterCanvas = document.getElementById('meter-canvas');
const meterNote = document.getElementById('meter-note');


let recorder = null;
let stream = null;
// جزء كل 30 ثانية: يحدّ الضياع عند الانقطاع بدون طلبات كثيرة على الاستضافة.
const CHUNK_MS = 30000;
let upload = null;
const uploads = [];
let startedAt = null;
let timerHandle = null;
let audioContext = null;
let meterFrame = null;
let boostSource = null;

// تقوية الاستقبال: الميكروفون ← تضخيم +12 dB ← ضاغط يرفع الكلام الهادي ويمنع
// التشويه، والتسجيل والمؤشر ياخذون الصوت بعد التقوية. يرجع للميكروفون الخام
// إذا المتصفح ما يدعم.
// iOS يشغّل محرك الصوت فقط داخل ضغطة المستخدم نفسها -- بعد نافذة إذن
// الميكروفون يبقى موقوف ويطلع التسجيل فاضي، فنشغّله أول ما ينضغط الزر.
function wakeAudioContext() {
  try {
    audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
    if (audioContext.state !== 'running') audioContext.resume();
  } catch (_) {}
}

function boostedStream() {
  // محرك موقوف = تسجيل فاضي؛ نسجّل الميكروفون مباشرة بدون تقوية.
  if (!audioContext || audioContext.state !== 'running') {
    return { recordStream: stream, tap: null };
  }
  try {
    boostSource = audioContext.createMediaStreamSource(stream);
    const gain = audioContext.createGain();
    gain.gain.value = 4;
    const compressor = audioContext.createDynamicsCompressor();
    compressor.threshold.value = -24;
    compressor.knee.value = 30;
    compressor.ratio.value = 6;
    compressor.attack.value = 0.003;
    compressor.release.value = 0.25;
    const destination = audioContext.createMediaStreamDestination();
    boostSource.connect(gain).connect(compressor).connect(destination);
    return { recordStream: destination.stream, tap: compressor };
  } catch (_) {
    return { recordStream: stream, tap: null };
  }
}

// مؤشر الصوت: أعمدة تتحرك مع مستوى الصوت المسجّل، وتنبيه إذا ما وصل صوت.
function startMeter(tap) {
  if (!tap) return;

  const analyser = audioContext.createAnalyser();
  analyser.fftSize = 1024;
  tap.connect(analyser);
  const samples = new Float32Array(analyser.fftSize);
  const ctx = meterCanvas.getContext('2d');
  const bars = [];
  const barCount = 70;
  let quietSince = Date.now();

  meterBox.style.display = 'block';

  const draw = () => {
    analyser.getFloatTimeDomainData(samples);
    let sum = 0;
    for (const v of samples) sum += v * v;
    const rms = Math.sqrt(sum / samples.length);
    // من حوالي -60 dB (صمت) إلى -10 dB (كلام قريب).
    const db = 20 * Math.log10(rms || 1e-8);
    const level = Math.min(1, Math.max(0, (db + 60) / 50));

    bars.push(level);
    if (bars.length > barCount) bars.shift();

    const w = meterCanvas.width, h = meterCanvas.height, step = w / barCount;
    ctx.clearRect(0, 0, w, h);
    bars.forEach((v, i) => {
      const bh = Math.max(2, v * (h - 8));
      ctx.fillStyle = v > 0.85 ? '#d93434' : v > 0.25 ? '#4fd18b' : '#4fa3ff';
      ctx.fillRect(w - (bars.length - i) * step + 1, (h - bh) / 2, step - 2, bh);
    });

    if (level > 0.25) quietSince = Date.now();
    const quietFor = (Date.now() - quietSince) / 1000;
    if (quietFor > 4) {
      meterNote.textContent = '⚠️ لا يصل صوت واضح — قرّب الجوال من المتحدث';
      meterNote.className = 'meter-note warn';
    } else {
      meterNote.textContent = level > 0.85 ? 'الصوت عالٍ جدًا' : '🎙️ الصوت يصل';
      meterNote.className = 'meter-note';
    }

    meterFrame = requestAnimationFrame(draw);
  };
  draw();
}

function stopMeter() {
  if (meterFrame) cancelAnimationFrame(meterFrame);
  meterFrame = null;
  meterBox.style.display = 'none';
}
let wakeLock = null;

function setStatus(text) { statusBox.textContent = text; }

function formatTime(ms) {
  const total = Math.floor(ms / 1000);
  const hours = Math.floor(total / 3600);
  const min = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
  const sec = String(total % 60).padStart(2, '0');
  return (hours ? hours + ':' : '') + min + ':' + sec;
}

function chooseMimeType() {
  // MP4/AAC أولًا: WebM من Safari على iOS يطلع بتوقيتات مكسورة والصوت يتقطع
  // عند فكّه، فيتحول إلى كلام بلا معنى.
  const candidates = ['audio/mp4', 'audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus'];
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
  // يمسح بقايا رمز الدخول القديم (الصفحة صارت بدون رمز).
  try { localStorage.removeItem('cj_session'); localStorage.removeItem('cj_user_token'); } catch (_) {}

  if (!navigator.mediaDevices || !window.MediaRecorder) {
    setStatus('❌ هذا المتصفح لا يدعم التسجيل. استخدم Safari أو Chrome بإصدار حديث.');
    return;
  }

  // الميكروفون يبقى مفتوح طول ما الصفحة مفتوحة -- Safari يطلب الإذن من جديد
  // كل مرة ينقفل.
  // iOS يكتم المسار بصمت بعد الرجوع من الخلفية أو مكالمة، ويبقى "live" لكنه
  // يسجّل صمت -- نعيد فتح الميكروفون إذا كان مكتوم. إعدادات الميكروفون
  // الافتراضية: إطفاء معالجة الصوت في iOS ينزّل المستوى لحد الصمت (−73 dB).
  const usable = stream && stream.getAudioTracks().some(t => t.readyState === 'live' && !t.muted && t.enabled);
  if (!usable) {
    if (stream) stream.getTracks().forEach(t => t.stop());
    stream = await navigator.mediaDevices.getUserMedia({ audio: true });
  }
  stream.getAudioTracks().forEach(track => {
    track.onmute = () => {
      if (recorder && recorder.state === 'recording') {
        setStatus('⚠️ النظام أوقف الميكروفون — أوقف التسجيل وابدأ من جديد');
      }
    };
    track.onended = track.onmute;
  });

  const { recordStream, tap } = boostedStream();
  const mimeType = chooseMimeType();
  recorder = mimeType ? new MediaRecorder(recordStream, { mimeType }) : new MediaRecorder(recordStream);

  const type = recorder.mimeType || mimeType || 'audio/mp4';
  upload = { relayId: null, key: null, type, ext: extensionFor(type), parts: [], nextSeq: 0, sent: 0, stopped: false, done: false, pumping: false };
  uploads.push(upload);

  recorder.ondataavailable = e => {
    if (e.data && e.data.size > 0) {
      upload.parts.push({ seq: upload.nextSeq++, blob: e.data });
      pumpUpload(upload);
    }
  };
  recorder.onstop = () => {
    upload.stopped = true;
    recorder = null;
    pumpUpload(upload);
  };

  recorder.start(CHUNK_MS);
  startedAt = Date.now();
  timerBox.textContent = '00:00';
  timerHandle = setInterval(() => { timerBox.textContent = formatTime(Date.now() - startedAt); }, 500);
  keepScreenAwake();

  recordButton.textContent = 'إيقاف وإرسال';
  recordButton.classList.add('recording');
  setStatus('🔴 جارٍ التسجيل...');
  startMeter(tap);
}

function stopRecording() {
  if (!recorder || recorder.state === 'inactive') return;
  recorder.stop();
  clearInterval(timerHandle);
  stopMeter();
  if (boostSource) { boostSource.disconnect(); boostSource = null; }
  releaseScreen();
  recordButton.textContent = 'ابدأ تسجيل جديد';
  recordButton.classList.remove('recording');
  setStatus('⬆️ انتهى التسجيل. جارٍ رفع آخر جزء...');
}

async function postJson(url, body) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
  const data = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(data.message || ('HTTP ' + response.status));
  return data;
}

async function sendPart(u, part) {
  const form = new FormData();
  form.append('key', u.key);
  form.append('seq', String(part.seq));
  form.append('chunk', part.blob, 'part-' + part.seq + '.' + u.ext);
  const response = await fetch('/api/cj/recordings/' + u.relayId + '/chunk', {
    method: 'POST', headers: { 'Accept': 'application/json' }, body: form
  });
  if (!response.ok) throw new Error('HTTP ' + response.status);
}

const sleep = ms => new Promise(r => setTimeout(r, ms));

// يرفع الأجزاء بالترتيب ويعيد المحاولة بلا توقف لين ترجع الشبكة؛ أي جزء ما
// وصل يبقى في الصفحة. بعد الإيقاف يقفل التسجيل على السيرفر.
async function pumpUpload(u) {
  if (u.pumping || u.done) return;
  u.pumping = true;
  let delay = 2000;
  // تسجيل أقدم يكمل رفعه بالخلفية بدون ما يغطي رسائل التسجيل الحالي.
  const show = text => { if (u === upload) setStatus(text); };

  while (!u.done && (u.parts.length || u.stopped)) {
    if (u.stopped && u.nextSeq === 0) {
      u.done = true;
      show('❌ التسجيل طلع فاضي — ما التقط الجهاز صوت. حدّث الصفحة وجرّب مرة ثانية.');
      break;
    }
    try {
      if (!u.relayId) {
        const started = await postJson('/api/cj/recordings/start', { extension: u.ext, mime_type: u.type });
        u.relayId = started.relay_id;
        u.key = started.upload_key;
      }
      if (u.parts.length) {
        await sendPart(u, u.parts[0]);
        u.parts.shift();
        u.sent++;
        if (recorder && recorder.state === 'recording') {
          show('🔴 جارٍ التسجيل — محفوظ على السيرفر حتى الدقيقة ' + Math.round(u.sent * CHUNK_MS / 60000 * 10) / 10);
        }
      } else {
        const finished = await postJson('/api/cj/recordings/' + u.relayId + '/finish', { key: u.key, chunks: u.nextSeq });
        u.done = true;
        show('✅ تم حفظ التسجيل: ' + finished.relay_id);
        if (u === upload) pollStatus(finished.relay_id);
      }
      delay = 2000;
    } catch (error) {
      show('⏳ ما وصل الإنترنت — ' + (u.parts.length || 1) + ' جزء بانتظار الرفع، لا تقفل الصفحة. يعيد المحاولة تلقائيًا.');
      await sleep(delay);
      delay = Math.min(delay * 2, 30000);
    }
  }
  u.pumping = false;
}

async function pollStatus(relayId) {
  const labels = {
    processing: '🔵 جارٍ تنقية وتجهيز الصوت على سيرفر البيت',
    ready: '🟡 التسجيل جاهز وبانتظار الماك',
    transcribing: '🤖 الماك استلم التسجيل ويحوّله إلى نص',
    completed: '🟢 اكتمل التحويل وحُفظ النص',
    failed: '❌ فشلت معالجة التسجيل'
  };

  // كل 10 ثواني لمدة ساعتين -- الاستضافة تحظر الـ IP عند كثرة الطلبات،
  // وتسجيل مؤتمر طويل ياخذ وقت في التحويل.
  const mine = upload;
  for (let i = 0; i < 720; i++) {
    await new Promise(r => setTimeout(r, 10000));
    if (upload !== mine) return;
    try {
      const response = await fetch('/api/cj/recordings/' + relayId + '/status', {
        headers: { 'Accept': 'application/json' }
      });
      if (!response.ok) continue;
      const data = await response.json();
      if (data.status === 'recording' || data.status === 'received') {
        setStatus('📥 وصل التسجيل للموقع وبانتظار سيرفر البيت — ' + relayId);
        continue;
      }
      setStatus((labels[data.job_status] || data.job_status) + ' — ' + (data.job_id || relayId));
      if (data.job_status === 'completed' || data.job_status === 'failed') return;
    } catch (_) {}
  }
}

recordButton.addEventListener('click', async () => {
  wakeAudioContext();
  try {
    if (recorder && recorder.state !== 'inactive') stopRecording();
    else await startRecording();
  } catch (error) {
    releaseScreen();
    setStatus('❌ تعذر بدء التسجيل: ' + error.message);
  }
});

window.addEventListener('beforeunload', e => {
  if ((recorder && recorder.state !== 'inactive') || uploads.some(u => !u.done)) {
    e.preventDefault();
    e.returnValue = '';
  }
});
</script>
</body>
</html>
