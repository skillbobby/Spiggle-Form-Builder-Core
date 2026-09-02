@php
    $type = $block['type'] ?? 'paragraph';
    $meta = $block['meta'] ?? [];
    $span = max(1, min(12, (int) ($block['column_span'] ?? 12)));
    $preview = (bool) ($preview ?? false);
    $formModel = $formModel ?? null;
    $bleed = (bool) ($bleed ?? false);
@endphp
<div @class([
    'fb-content',
    'fb-content-'.$type,
    'fb-span-'.$span,
    'fb-chrome-block-bleed' => $bleed,
    'fb-content-preview' => $preview,
])>    @switch($type)
        @case('heading')
            @php $level = max(1, min(4, (int) ($meta['level'] ?? 2))); @endphp
            <{{ 'h'.$level }} class="fb-content-heading" style="text-align: {{ $meta['alignment'] ?? 'left' }}">
                {!! \Spiggle\FormBuilder\Support\ContentBlockCatalog::sanitizeHtml((string) ($meta['text'] ?? 'Heading')) !!}
            </{{ 'h'.$level }}>
            @break

        @case('paragraph')
            <div class="fb-content-paragraph" style="text-align: {{ $meta['alignment'] ?? 'left' }}">{!! \Spiggle\FormBuilder\Support\ContentBlockCatalog::sanitizeHtml((string) ($meta['text'] ?? '')) !!}</div>
            @break

        @case('divider')
            <hr class="fb-content-divider">
            @break

        @case('spacer')
            <div class="fb-content-spacer" style="height: {{ $meta['height'] ?? '24px' }}"></div>
            @break

        @case('banner')
            @php
                $bannerImg = \Spiggle\FormBuilder\Support\StorageUrl::resolve($meta['image_url'] ?? null);
                $bannerHeight = $meta['height'] ?? '160px';
                $bannerBg = $bannerImg
                    ? 'transparent'
                    : ($preview ? ($meta['background'] ?? '#ecfdf5') : '#f3f4f6');
                $bannerCaption = (string) ($meta['caption'] ?? '');
            @endphp
            <div @class([
                'fb-content-banner',
                'has-image' => (bool) $bannerImg,
                'is-designer' => $preview,
            ]) style="width: 100%; min-width: 100%; height: {{ $bannerHeight }}; min-height: {{ $bannerHeight }};{{ $bannerBg !== 'transparent' ? ' background: '.$bannerBg.';' : '' }}">
                @if ($bannerImg)
                    <img src="{{ $bannerImg }}" alt="{{ $meta['alt'] ?? '' }}" class="fb-content-banner-img" loading="lazy">
                @else
                    <span @class(['fb-content-banner-placeholder', 'fb-hidden' => ! $preview]) aria-hidden="true">Banner image</span>
                @endif
                <span @class(['fb-content-banner-caption', 'fb-hidden' => $bannerCaption === ''])>{{ $bannerCaption }}</span>
            </div>
            @break

        @case('image')
            @php
                $imageSrc = \Spiggle\FormBuilder\Support\StorageUrl::resolve($meta['image_url'] ?? null);
                $imageCaption = (string) ($meta['caption'] ?? '');
            @endphp
            <figure class="fb-content-image {{ $imageSrc ? 'has-image' : '' }}" style="text-align: {{ $meta['alignment'] ?? 'center' }}">
                @if ($imageSrc)
                    <img src="{{ $imageSrc }}" alt="{{ $meta['alt'] ?? '' }}" class="fb-content-image-img" style="max-height: {{ $meta['max_height'] ?? '320px' }}" loading="lazy">
                @else
                    <div class="fb-content-image-placeholder">{{ $preview ? 'Upload an image' : 'Image' }}</div>
                @endif
                <figcaption @class(['fb-hidden' => $imageCaption === ''])>{{ $imageCaption }}</figcaption>
            </figure>
            @break

        @case('video')
            @php $videoUrl = trim((string) ($meta['url'] ?? '')); @endphp
            <div class="fb-content-video" style="aspect-ratio: {{ $meta['aspect_ratio'] ?? '16/9' }}">
                @if ($videoUrl !== '')
                    @if (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be'))
                        @php
                            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]+)/', $videoUrl, $m);
                            $embed = 'https://www.youtube.com/embed/'.($m[1] ?? '');
                        @endphp
                        <iframe src="{{ $embed }}" title="Video" allowfullscreen loading="lazy"></iframe>
                    @else
                        <video src="{{ $videoUrl }}" controls></video>
                    @endif
                @else
                    <div class="fb-content-video-placeholder">Video URL</div>
                @endif
            </div>
            @break

        @case('footer')
            <p class="fb-content-footer {{ ($meta['muted'] ?? true) ? 'muted' : '' }}" style="text-align: {{ $meta['alignment'] ?? 'center' }}">
                {!! \Spiggle\FormBuilder\Support\ContentBlockCatalog::sanitizeHtml((string) ($meta['text'] ?? '')) !!}
            </p>
            @break

        @case('button')
            @if (! empty($meta['url']))
                <a href="{{ $meta['url'] }}" class="fb-content-btn" target="_blank" rel="noopener">{{ $meta['text'] ?? 'Learn more' }}</a>
            @else
                <span class="fb-content-btn">{{ $meta['text'] ?? 'Learn more' }}</span>
            @endif
            @break

        @case('social_links')
            <div class="fb-content-social">
                @foreach ($meta['links'] ?? [] as $link)
                    @if (! empty($link['url']))
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ ucfirst($link['platform'] ?? 'link') }}</a>
                    @endif
                @endforeach
            </div>
            @break

        @case('button_group')
            <div class="fb-content-btn-group">
                @foreach ($meta['buttons'] ?? [] as $btn)
                    <div class="fb-content-btn-card">
                        <strong>{{ $btn['title'] ?? '' }}</strong>
                        @if (! empty($btn['url']))
                            <a href="{{ $btn['url'] }}" class="fb-content-btn" target="_blank" rel="noopener">{{ $btn['text'] ?? 'Open' }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
            @break

        @case('html')
            <div class="fb-content-html">{!! \Spiggle\FormBuilder\Support\ContentBlockCatalog::sanitizeHtml((string) ($meta['html'] ?? '')) !!}</div>
            @break

        @case('section')
            @php
                $sectionStyles = \Spiggle\FormBuilder\Support\ContentBlockCatalog::sectionStyles($meta);
                $sectionStyleStr = collect($sectionStyles)->map(fn ($value, $key) => $key.': '.$value)->implode('; ');
                $children = $block['children'] ?? [];
            @endphp
            <div class="fb-section-container" style="{{ $sectionStyleStr }}">
                <div @class(['fb-section-header', 'fb-hidden' => empty($meta['show_title'])])>
                    <h3 class="fb-section-title">{{ $meta['title'] ?? 'Section' }}</h3>
                </div>
                <hr @class(['fb-section-divider', 'fb-hidden' => empty($meta['show_divider']) || empty($meta['show_title'])])>
                <div class="fb-grid fb-section-body">
                    @foreach ($children as $child)
                        @if (\Spiggle\FormBuilder\Support\ContentBlockCatalog::isContent($child))
                            @include('form-builder::components.content-block', [
                                'block' => $child,
                                'preview' => $preview,
                                'formModel' => $formModel ?? null,
                            ])
                        @elseif (! $preview && isset($formModel))
                            @include('form-builder::components.field', [
                                'field' => $child,
                                'formModel' => $formModel,
                            ])
                        @endif
                    @endforeach
                </div>
            </div>
            @break
    @endswitch
</div>
