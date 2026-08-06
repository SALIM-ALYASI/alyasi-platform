<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedVisitor;
use App\Models\CommunityComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityCommentController extends Controller
{
    /**
     * عرض قائمة تعليقات المجتمع.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $comments = CommunityComment::query()
            ->with('communityPost')
            ->when(
                in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = CommunityComment::query()->pending()->count();

        return view('admin.community-comments.index', compact(
            'comments',
            'pendingCount',
            'status'
        ));
    }

    /**
     * اعتماد التعليق ونشره للعامة.
     */
    public function approve(CommunityComment $communityComment): RedirectResponse
    {
        $communityComment->update(['status' => 'approved']);

        return back()->with('success', 'تم اعتماد التعليق ونشره.');
    }

    /**
     * رفض التعليق وإخفاؤه.
     */
    public function reject(CommunityComment $communityComment): RedirectResponse
    {
        $communityComment->update(['status' => 'rejected']);

        return back()->with('success', 'تم رفض التعليق.');
    }

    /**
     * حذف التعليق نهائيًا.
     */
    public function destroy(CommunityComment $communityComment): RedirectResponse
    {
        $communityComment->delete();

        return back()->with('success', 'تم حذف التعليق.');
    }

    /**
     * حظر صاحب التعليق (بمعرّف الجهاز والـ IP) ورفض جميع تعليقاته.
     */
    public function block(CommunityComment $communityComment): RedirectResponse
    {
        BlockedVisitor::query()->create([
            'device_token' => $communityComment->device_token,
            'ip_address' => $communityComment->ip_address,
            'reason' => 'حظر من صفحة تعليقات المجتمع.',
        ]);

        CommunityComment::query()
            ->matching(
                $communityComment->device_token,
                $communityComment->ip_address
            )
            ->update(['status' => 'rejected']);

        return back()->with('success', 'تم حظر الزائر ورفض تعليقاته.');
    }
}
