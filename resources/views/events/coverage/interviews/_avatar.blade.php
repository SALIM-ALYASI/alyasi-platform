{{-- صورة الضيف، أو أول حرف من اسمه لو ما عندنا صورته بعد --}}
<span class="interview-avatar" style="--size: {{ $size }}px" aria-hidden="true">
    @if ($guest['photo'])
        <img src="{{ $guest['photo'] }}" alt="" width="{{ $size }}" height="{{ $size }}" loading="lazy">
    @else
        <span>{{ $guest['initials'] }}</span>
    @endif
</span>
