<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedVisitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlockedVisitorController extends Controller
{
    /**
     * عرض قائمة الزوار المحظورين.
     */
    public function index(): View
    {
        $blockedVisitors = BlockedVisitor::query()
            ->latest()
            ->paginate(20);

        return view('admin.blocked-visitors.index', compact('blockedVisitors'));
    }

    /**
     * رفع الحظر عن الزائر.
     */
    public function destroy(BlockedVisitor $blockedVisitor): RedirectResponse
    {
        $blockedVisitor->delete();

        return back()->with('success', 'تم رفع الحظر عن الزائر.');
    }
}
