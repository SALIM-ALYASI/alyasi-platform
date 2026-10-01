<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalysisIngestController extends Controller
{
    /**
     * يستقبل نص رد الإيميل الصباحي (عبر workflow n8n يراقب الوارد) ويحدّث
     * تحليل كل خبر مذكور بمرجع [#id] -- لا يُنشئ أي خبر أو محتوى جديد
     * إطلاقًا، فقط يحدّث analysis_ar/analysis_status للخبر الموجود مسبقًا.
     *
     * الصيغة المتوقعة بالرد (نفس تنسيق SendMorningAnalysisDigest):
     * [#381] عنوان الخبر
     * الرابط: ...
     * تحليلك: نص التحليل هنا، ممكن عدة أسطر
     *
     * [#382] ...
     */
    public function ingestReply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        // ملاحظة: الـlookahead يوقف عند أي سطر يبدأ بـ"[#" (مو بس أرقام) --
        // عشان آخر خبر بالرد يتوقف عند علامة النهاية [#END] اللي يضيفها
        // SendMorningAnalysisDigest، بدل ما يمتد لتوقيع الإيميل أو الاقتباس
        // المرفق تلقائيًا مع الرد.
        preg_match_all(
            '/\[#(\d+)\].*?تحليلك:[ \t]*(.*?)(?=\n\[#|\z)/su',
            $validated['body'],
            $matches,
            PREG_SET_ORDER
        );

        $updated = [];
        $skipped = [];

        foreach ($matches as $match) {
            $id = (int) $match[1];
            $analysis = trim($match[2]);

            if ($analysis === '') {
                continue;
            }

            $article = NewsArticle::find($id);

            if (! $article) {
                $skipped[] = $id;

                continue;
            }

            $article->analysis_ar = $analysis;
            $article->analysis_status = 'ready';
            $article->save();

            $updated[] = $id;
        }

        Log::info('تحديث تحليل بشري عبر رد الإيميل.', [
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}
