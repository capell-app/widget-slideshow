@php /** @var \Capell\WidgetSlideshow\Data\SlideshowRenderData $renderData */ @endphp
<section
    class="capell-slideshow"
    aria-roledescription="carousel"
    aria-label="{{ $renderData->label }}"
    data-capell-slideshow
    data-auto-advance="{{ $renderData->autoAdvance ? 'true' : 'false' }}"
>
    <div class="capell-slideshow__track">
        @foreach ($renderData->slides as $slide)
            <article
                class="capell-slideshow__slide"
                aria-roledescription="slide"
                aria-label="{{ $loop->iteration }} of {{ $loop->count }}"
                @if (! $loop->first) hidden @endif
            >
                <img
                    src="{{ asset('storage/' . ltrim($slide->image, '/')) }}"
                    alt="{{ $slide->alt }}"
                    loading="lazy"
                />
                @if ($slide->caption || ($slide->ctaLabel && $slide->ctaUrl))
                    <div class="capell-slideshow__caption">
                        @if ($slide->caption)
                            <p>{{ $slide->caption }}</p>
                        @endif
                        @if ($slide->ctaLabel && $slide->ctaUrl)
                            <a
                                href="{{ $slide->ctaUrl }}"
                                >{{ $slide->ctaLabel }}</a
                            >
                        @endif
                    </div>
                @endif
            </article>
        @endforeach
    </div>
    @if ($renderData->showControls && $renderData->slides->count() > 1)
        <div class="capell-slideshow__controls">
            <button
                type="button"
                data-slideshow-previous
                aria-label="Previous slide"
            >
                ‹
            </button>
            <button
                type="button"
                data-slideshow-toggle
                aria-label="Pause slideshow"
                aria-pressed="false"
            >
                Pause
            </button>
            <button
                type="button"
                data-slideshow-next
                aria-label="Next slide"
            >
                ›
            </button>
        </div>
    @endif
</section>
