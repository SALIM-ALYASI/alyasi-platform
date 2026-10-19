<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * طلبات العملاء من منصات التواصل (Grok): الجديد أول بالأولوية، وسالم يحدّث الحالة.
 */
class ServiceLeadController extends Controller
{
    public function index(Request $request): View
    {
        // احتياط لو ما اشتغل model:prune المجدول: الأقدم من 60 يومًا ما يظهر ولا يبقى.
        ServiceLead::query()->where('created_at', '<', now()->subDays(60))->delete();

        $status = $request->string('status')->toString();
        $status = array_key_exists($status, ServiceLead::STATUSES) ? $status : 'new';
        $category = $request->string('category')->toString();
        $category = array_key_exists($category, ServiceLead::CATEGORIES) ? $category : '';
        $scope = $request->string('scope')->toString();
        $scope = array_key_exists($scope, ServiceLead::SCOPES) ? $scope : '';

        $leads = ServiceLead::query()
            ->where('status', $status)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($scope, fn ($query) => $query->where('scope', $scope))
            ->orderByDesc('score')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = ServiceLead::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.service-leads.index', compact('leads', 'status', 'category', 'scope', 'counts'));
    }

    public function updateStatus(Request $request, ServiceLead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'dismissed'])],
        ]);

        $lead->update(['status' => $data['status']]);

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }
}
