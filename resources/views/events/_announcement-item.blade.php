<div class="event-detail__announcement">
    @if (!empty($item['image']))
        <img
            src="{{ asset($item['image']) }}"
            alt="{{ app()->getLocale() === 'en' ? ($item['label_en'] ?? $item['label_ar'] ?? '') : ($item['label_ar'] ?? '') }}"
            class="event-detail__announcement-image"
            loading="lazy"
        >
    @endif
    <div style="flex: 1; min-width: 0;">
        <div class="event-detail__announcement-label">
            {{ app()->getLocale() === 'en' ? ($item['label_en'] ?? $item['label_ar'] ?? '') : ($item['label_ar'] ?? '') }}
        </div>
        @if (!empty($item['note_ar']) || !empty($item['note_en']))
            <div class="event-detail__announcement-note">
                {{ app()->getLocale() === 'en' ? ($item['note_en'] ?? $item['note_ar'] ?? '') : ($item['note_ar'] ?? '') }}
            </div>
        @endif
    </div>
    @if (!empty($item['confidence']))
        <span class="badge">{{ __('events.confidence.'.$item['confidence']) }}</span>
    @endif
</div>
