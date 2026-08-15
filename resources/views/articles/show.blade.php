{{-- =========================================================
     Article Schema (JSON-LD)
     ملاحظة: تقسيم @context يمنع Blade من تفسيره كـ Directive
========================================================= --}}
@section('schema')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Article',

    'headline' => $article->title,

    'description' => $article->meta_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->excerpt ?: $article->content),
            160
        ),

    'image' => [
        media_url($article->featured_image)
    ],

    'datePublished' => optional(
        $article->published_at
    )->toIso8601String(),

    'dateModified' => optional(
        $article->updated_at
    )->toIso8601String(),

    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('articles.show', $article),
    ],

    'publisher' => [
        '@type' => 'Organization',
        'name' => 'ALYASI',
        'url' => url('/'),
    ],

], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection