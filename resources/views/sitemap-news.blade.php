<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach ($articles as $article)
@php
    $arPermalink = $article->permalinks->firstWhere('locale', 'ar');
    $enPermalink = $article->permalinks->firstWhere('locale', 'en');
@endphp
@if ($arPermalink)
<url>
    <loc>{{ route('news.show', $arPermalink->slug) }}</loc>
    <news:news>
        <news:publication>
            <news:name>ALYASI</news:name>
            <news:language>ar</news:language>
        </news:publication>
        <news:publication_date>{{ $article->published_at->toIso8601String() }}</news:publication_date>
        <news:title>{{ $article->title_ar }}</news:title>
    </news:news>
</url>
@endif
@if ($enPermalink)
<url>
    <loc>{{ localized_route('news.show', ['slug' => $enPermalink->slug], 'en') }}</loc>
    <news:news>
        <news:publication>
            <news:name>ALYASI</news:name>
            <news:language>en</news:language>
        </news:publication>
        <news:publication_date>{{ $article->published_at->toIso8601String() }}</news:publication_date>
        <news:title>{{ $article->title_en }}</news:title>
    </news:news>
</url>
@endif
@endforeach
</urlset>
