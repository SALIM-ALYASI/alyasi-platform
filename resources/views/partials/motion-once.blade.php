{{-- حركة فتح الصفحة وظهور العناصر: أول فتح بس، ومرة كل 24 ساعة. باقي الزيارات
     يطلع المحتوى ثابت على طول (class motion-calm على html قبل الرسم). --}}
<script>
    (function () {
        try {
            var key = 'alyasi-motion-at';
            var now = Date.now();
            var last = parseInt(localStorage.getItem(key) || '0', 10);

            if (now - last < 86400000) {
                document.documentElement.classList.add('motion-calm');
            } else {
                localStorage.setItem(key, String(now));
            }
        } catch (error) {
            // التخزين غير متاح (تصفح خاص مثلًا): نترك الحركة الافتراضية.
        }
    })();
</script>
