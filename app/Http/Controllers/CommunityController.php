<?php

namespace App\Http\Controllers;

use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * صفحة المجتمع الرئيسية.
     */
    public function index(Request $request): View
    {
        $categories = CommunityCategory::query()
            ->active()
            ->ordered()
            ->get();

        $postsQuery = CommunityPost::query()
            ->with('category')
            ->active()
            ->published()
            ->where('type', '!=', 'event');

        if ($request->filled('category')) {
            $postsQuery->whereHas('category', function ($query) use ($request) {
                $query->where('slug', $request->string('category')->toString());
            });
        }

        if ($request->filled('type')) {
            $postsQuery->where('type', $request->string('type')->toString());
        }

        $featuredPosts = CommunityPost::query()
            ->with('category')
            ->active()
            ->published()
            ->where('type', '!=', 'event')
            ->featured()
            ->ordered()
            ->limit(3)
            ->get();

        $posts = $postsQuery
            ->ordered()
            ->paginate(9)
            ->withQueryString();

        abort_if_page_out_of_range($posts);

        return view('community.index', compact(
            'categories',
            'featuredPosts',
            'posts'
        ));
    }

    /**
     * صفحة تفاصيل محتوى المجتمع.
     */
    public function show(CommunityPost $communityPost): View|RedirectResponse
    {
        // الرابط القديم كان يمثل مؤتمر Apple كفعالية داخل المجتمع.
        // بعد فصل مركز المؤتمرات عن المجتمع نحافظ عليه بتحويل 301 دائم.
        if ($communityPost->slug === 'motmr-abl-surprise-and-shine-iphone-18-september-2026') {
            return redirect()->route('event_editions.show', ['slug' => 'apple'], 301);
        }

        if (! $communityPost->is_active) {
            abort(404);
        }

        if ($communityPost->status !== 'published') {
            abort(404);
        }

        if (
            ! $communityPost->published_at
            || $communityPost->published_at->greaterThan(now())
        ) {
            abort(404);
        }

        $communityPost->load([
            'category',
            'approvedComments',
        ]);

        $relatedPosts = CommunityPost::query()
            ->with('category')
            ->active()
            ->published()
            ->whereKeyNot($communityPost->getKey())
            ->when(
                $communityPost->community_category_id,
                function ($query) use ($communityPost) {
                    $query->where(
                        'community_category_id',
                        $communityPost->community_category_id
                    );
                }
            )
            ->ordered()
            ->limit(3)
            ->get();

        return view('community.show', compact(
            'communityPost',
            'relatedPosts'
        ));
    }
}
