<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    /**
     * زوايا التحليل اللي يولّدها بوت الأخبار (stage_6_analysis) -- نفس
     * القيم المخزّنة بعمود news_articles.angle.
     */
    public const ANGLES = [
        'what_changed' => 'شو تغيّر',
        'who_cares' => 'ليش يهمك',
        'price_reality' => 'حقيقة السعر',
        'what_broke' => 'شو انكسر',
    ];

    /**
     * فهرس "تحليل ALYASI" -- واجهة اكتشاف منفصلة للأخبار اللي عندها تحليل
     * جاهز (analysis_status = ready)، تُبرز زاوية ALYASI بدل الخبر الخام.
     * التحليل نفسه معروض أصلاً داخل صفحة الخبر (news.show#analysis)؛ هذي
     * الصفحة فقط تجمعها بمكان واحد.
     */
    public function index(Request $request): View
    {
        $angles = self::ANGLES;

        $articlesQuery = NewsArticle::query()
            ->with(['category', 'permalinks'])
            ->published()
            ->where('analysis_status', 'ready')
            ->whereNotNull('analysis_ar');

        $selectedAngle = $request->string('angle')->toString();

        if ($selectedAngle !== '' && array_key_exists($selectedAngle, $angles)) {
            $articlesQuery->where('angle', $selectedAngle);
        } else {
            $selectedAngle = null;
        }

        $articles = $articlesQuery
            ->latestPublished()
            ->paginate(12)
            ->withQueryString();

        abort_if_page_out_of_range($articles);

        return view('analysis.index', [
            'articles' => $articles,
            'angles' => $angles,
            'selectedAngle' => $selectedAngle,
        ]);
    }
}
