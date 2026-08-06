<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedVisitor;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * عرض قائمة التقييمات.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $reviews = Review::query()
            ->with('reviewable')
            ->when(
                in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                in_array($type, ['work', 'service'], true),
                fn ($query) => $query->where('reviewable_type', $type)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = Review::query()->pending()->count();

        return view('admin.reviews.index', compact(
            'reviews',
            'pendingCount',
            'status',
            'type'
        ));
    }

    /**
     * اعتماد التقييم ونشره للعامة.
     */
    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => 'approved']);

        return back()->with('success', 'تم اعتماد التقييم ونشره.');
    }

    /**
     * رفض التقييم وإخفاؤه.
     */
    public function reject(Review $review): RedirectResponse
    {
        $review->update(['status' => 'rejected']);

        return back()->with('success', 'تم رفض التقييم.');
    }

    /**
     * حذف التقييم نهائيًا.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'تم حذف التقييم.');
    }

    /**
     * حظر صاحب التقييم (بمعرّف الجهاز والـ IP) ورفض جميع تقييماته.
     */
    public function block(Review $review): RedirectResponse
    {
        BlockedVisitor::query()->create([
            'device_token' => $review->device_token,
            'ip_address' => $review->ip_address,
            'reason' => 'حظر من صفحة التقييمات.',
        ]);

        Review::query()
            ->matching($review->device_token, $review->ip_address)
            ->update(['status' => 'rejected']);

        return back()->with('success', 'تم حظر الزائر ورفض تقييماته.');
    }
}
