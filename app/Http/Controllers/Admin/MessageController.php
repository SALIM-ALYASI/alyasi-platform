<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * عرض قائمة رسائل التواصل.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $messages = ContactMessage::query()
            ->when(
                in_array($status, ['new', 'read', 'replied'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $newCount = ContactMessage::query()->new()->count();

        return view('admin.messages.index', compact(
            'messages',
            'newCount',
            'status'
        ));
    }

    /**
     * عرض رسالة واحدة وتحديدها كمقروءة تلقائيًا.
     */
    public function show(ContactMessage $message): View
    {
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
        }

        return view('admin.messages.show', compact('message'));
    }

    /**
     * تحديد الرسالة كـ"تم الرد عليها".
     */
    public function markReplied(ContactMessage $message): RedirectResponse
    {
        $message->update(['status' => 'replied']);

        return back()->with('success', 'تم تحديد الرسالة كمردود عليها.');
    }

    /**
     * حذف الرسالة نهائيًا.
     */
    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('success', 'تم حذف الرسالة.');
    }
}
