# ALYASI News Ingest Contract v2

The news ingest endpoint accepts only complete publishable news packages.

Required fields:

- `title_en`
- `title_ar`
- `content_en`
- `content_ar`
- `category_slug` — must match an active `news_categories.slug`
- `slug` — lowercase English letters/numbers/hyphens only
- `link`
- `image`
- `source`
- `author`
- `published_at`
- `is_published`

If any required field is missing or invalid, Laravel returns HTTP `422` and the transaction does not create or update a news article.

The supplied `slug` is used for both Arabic and English permalinks. A collision with another item is rejected instead of silently generating a different URL.

Optional ALYASI Analysis fields remain non-blocking.
