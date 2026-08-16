<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ServiceSlugCleaner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceSlugCleanupController extends Controller
{
    /**
     * معاينة فقط — قراءة بدون أي تعديل بقاعدة البيانات.
     */
    public function index(ServiceSlugCleaner $cleaner): View
    {
        $rows = $cleaner->preview();

        return view('admin.service-slug-cleanup.index', [
            'rows' => $rows,
            'fixableCount' => $rows->where('fixable', true)->count(),
            'manualCount' => $rows->where('fixable', false)->count(),
        ]);
    }

    /**
     * التنفيذ الفعلي — الوحيد اللي يكتب على قاعدة البيانات، وفقط بعد
     * ضغط المدير على زر منفصل صريح (مع تأكيد بالمتصفح).
     */
    public function execute(ServiceSlugCleaner $cleaner): RedirectResponse
    {
        $result = $cleaner->execute();

        $message = $result['fixed'] > 0
            ? "تم إصلاح {$result['fixed']} رابط بنجاح، مع حفظ تحويل 301 لكل رابط قديم."
            : 'لا يوجد أي رابط قابل للإصلاح تلقائيًا حاليًا.';

        if ($result['skipped'] > 0) {
            $message .= " تبقّى {$result['skipped']} خدمة تحتاج إضافة عنوان إنجليزي يدويًا.";
        }

        return redirect()
            ->route('admin.service-slug-cleanup.index')
            ->with('success', $message);
    }
}
