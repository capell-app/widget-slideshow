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
                aria-label="{{ __('capell-widget-slideshow::widget.slide_position', ['position' => $loop->iteration, 'total' => $loop->count]) }}"
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
                aria-label="{{ __('capell-widget-slideshow::widget.previous_slide') }}"
            >
                ‹
            </button>
            <button
                type="button"
                data-slideshow-toggle
                aria-label="{{ __('capell-widget-slideshow::widget.pause_slideshow') }}"
                aria-pressed="false"
                data-label-pause="{{ __('capell-widget-slideshow::widget.pause') }}"
                data-label-resume="{{ __('capell-widget-slideshow::widget.resume') }}"
                data-label-pause-slideshow="{{ __('capell-widget-slideshow::widget.pause_slideshow') }}"
                data-label-resume-slideshow="{{ __('capell-widget-slideshow::widget.resume_slideshow') }}"
            >
                {{ __('capell-widget-slideshow::widget.pause') }}
            </button>
            <button
                type="button"
                data-slideshow-next
                aria-label="{{ __('capell-widget-slideshow::widget.next_slide') }}"
            >
                ›
            </button>
        </div>
    @endif
</section>
