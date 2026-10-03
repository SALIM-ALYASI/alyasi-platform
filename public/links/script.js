const LINKS = [
  { name: 'واتساب',      en: 'WhatsApp',        handle: 'تواصل مباشر',        enHandle: 'Direct chat',    icon: 'fa-brands fa-whatsapp',    url: 'https://wa.me/qr/2CWYHXAZ25JYO1',                  b: '#25D366', f: '#fff' },
  { name: 'منصة الياسي', en: 'ALYASI Platform', handle: 'alyasi.dev',                                     logo: true,                       url: 'https://alyasi.dev',                              b: '#1F6FD1', f: '#fff' },
  { name: 'موقعنا',      en: 'Our Location',    handle: 'بدية، عُمان',          enHandle: 'Bidiyah, Oman',  icon: 'fa-solid fa-location-dot', url: 'https://alyasi.dev/Mylocation',                   b: '#EA4335', f: '#fff' },
  { name: 'إكس',         en: 'X',               handle: '@ALYASI_MBRMJ',                                  icon: 'fa-brands fa-x-twitter',   url: 'https://x.com/ALYASI_MBRMJ',                       b: '#000',    f: '#fff' },
  { name: 'إنستغرام',    en: 'Instagram',       handle: '@alyasi_mbrmj',                                  icon: 'fa-brands fa-instagram',   url: 'https://www.instagram.com/alyasi_mbrmj',           b: 'linear-gradient(45deg,#f9ce34,#ee2a7b,#6228d7)', f: '#fff' },
  { name: 'فيسبوك',      en: 'Facebook',        handle: 'ALYASI',                                         icon: 'fa-brands fa-facebook-f',  url: 'https://www.facebook.com/share/1MEuuTpQD4/',       b: '#1877F2', f: '#fff' },
  { name: 'لينكدإن',     en: 'LinkedIn',        handle: 'سالم الحجري',          enHandle: 'Salem Al Hajri', icon: 'fa-brands fa-linkedin-in', url: 'https://www.linkedin.com/in/سالم-الحجري-b735a721b', b: '#0A66C2', f: '#fff' },
  { name: 'يوتيوب',      en: 'YouTube',         handle: '@alyasi_mbrmj',                                  icon: 'fa-brands fa-youtube',     url: 'https://youtube.com/@alyasi_mbrmj',                b: '#FF0000', f: '#fff' },
  { name: 'تيك توك',     en: 'TikTok',          handle: '@alyasi_mbrmj',                                  icon: 'fa-brands fa-tiktok',      url: 'https://www.tiktok.com/@alyasi_mbrmj',             b: '#010101', f: '#fff' },
  { name: 'سناب شات',    en: 'Snapchat',        handle: 'ALYASI',                                         icon: 'fa-brands fa-snapchat',    url: 'https://snapchat.com/t/pyHBRyl9',                  b: '#FFFC00', f: '#000' },
];

// الترجمة حسب لغة الجهاز (html[lang] يتحدد بسكربت الـhead قبل الرسم).
const IS_EN = document.documentElement.lang === 'en';
if (IS_EN) {
  LINKS.forEach((l) => {
    l.name = l.en;
    l.handle = l.enHandle || l.handle;
  });
}

const $ = (s) => document.querySelector(s);
const $$ = (s) => [...document.querySelectorAll(s)];
const root = document.documentElement;

// بناء الروابط
$('#links').innerHTML = LINKS.map((l) => `
  <a class="link" href="${l.url}" target="_blank" rel="noopener" style="--b:${l.b};--f:${l.f}">
    <span class="ic">${l.logo
      ? '<img class="ic-logo ic-logo--light" src="/images/logo/logo-navy-icon.png" alt=""><img class="ic-logo ic-logo--dark" src="/images/logo/logo-white-trimmed.png" alt="">'
      : `<i class="${l.icon}"></i>`}</span>
    <span class="txt">
      <span class="name">${l.name}</span>
      <span class="handle" dir="ltr">${l.handle}</span>
    </span>
    <span class="arr"><i class="fa-solid ${IS_EN ? 'fa-arrow-right' : 'fa-arrow-left'}"></i></span>
  </a>`).join('');

if (IS_EN) {
  document.title = 'ALYASI — My Links';
  $('#pageTitle').innerHTML = 'Salem Al Hajri <span>· Follow me everywhere</span>';
  $('#themeBtn').setAttribute('aria-label', 'Toggle light and dark mode');
  $('#replayBtn').setAttribute('aria-label', 'Replay animation');
}

// الوضع الفاتح / الغامق
function applyTheme(t) {
  root.setAttribute('data-theme', t);
  $('#logoImg').src = t === 'dark' ? '/links/logo-dark.png' : '/links/logo-light.png';
  $('#themeBtn i').className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}
applyTheme(root.getAttribute('data-theme') || 'light');
$('#themeBtn').addEventListener('click', () => {
  const t = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  try { localStorage.setItem('alyasi-theme', t); } catch (e) {}
  applyTheme(t);
});

// حركة الدخول
function play() {
  if (!document.body.animate || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const spring = 'cubic-bezier(.3,1.35,.5,1)';
  const out = 'cubic-bezier(.2,.8,.2,1)';

  $$('.blob').forEach((el, i) => el.animate(
    [{ transform: 'scale(.3)', opacity: 0 }, { transform: 'scale(1)' }],
    { duration: 1400, easing: out, delay: i * 150, fill: 'backwards' }));

  $$('.logo').forEach((el) => el.animate(
    [{ transform: 'scale(.6) rotate(-8deg)', opacity: 0, filter: 'blur(8px)' },
     { transform: 'scale(1) rotate(0)', opacity: 1, filter: 'blur(0)' }],
    { duration: 800, easing: spring, delay: 100, fill: 'backwards' }));

  $$('.ring').forEach((el, i) => el.animate(
    [{ transform: 'scale(1)', opacity: .9 }, { transform: 'scale(1.18)', opacity: 0 }],
    { duration: 1600, delay: 500 + i * 450, iterations: 2, easing: 'ease-out', fill: 'both' }));

  $$('.anim-up').forEach((el, i) => el.animate(
    [{ transform: 'translateY(18px)', opacity: 0 }, { transform: 'none', opacity: 1 }],
    { duration: 600, delay: 350 + i * 450, easing: out, fill: 'backwards' }));

  $$('.link').forEach((el, i) => el.animate(
    [{ transform: `translateX(${IS_EN ? 60 : -60}px) scale(.94)`, opacity: 0 }, { transform: 'none', opacity: 1 }],
    { duration: 750, delay: 550 + i * 180, easing: spring, fill: 'backwards' }));
}
$('#replayBtn').addEventListener('click', play);
play();
