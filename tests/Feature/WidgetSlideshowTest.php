<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Support\Assets\FrontendResourceRegistry;
use Capell\Frontend\Support\Security\PublicHtmlSafetyInspector;
use Capell\LayoutBuilder\Support\WidgetExtensions\WidgetExtensionRegistry;
use Capell\WidgetSlideshow\Data\SlideshowInputData;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Health\WidgetSlideshowHealthCheck;
use Capell\WidgetSlideshow\Providers\WidgetSlideshowServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    CapellCore::forcePackageInstalled('capell-app/widget-slideshow');
    $this->app->register(WidgetSlideshowServiceProvider::class);
});

it('registers its typed definition fallback view resources and filament block', function (): void {
    $definition = resolve(WidgetExtensionRegistry::class)->definition('capell-app.slideshow');

    expect($definition)->not->toBeNull()
        ->and($definition?->inputData)->toBe(SlideshowInputData::class)
        ->and($definition?->renderData)->toBe(SlideshowRenderData::class)
        ->and(resolve(FrontendResourceRegistry::class)->has('capell-app.widget-slideshow'))->toBeTrue()
        ->and(WidgetSlideshowHealthCheck::passed())->toBeTrue();
});

it('renders escaped typed slide content without authoring state', function (): void {
    $renderData = SlideshowRenderData::from([
        'label' => 'Featured work',
        'slides' => [[
            'image' => 'slides/one.jpg',
            'alt' => 'A launch photograph',
            'caption' => '<script>alert(1)</script>',
            'ctaLabel' => 'Read more',
            'ctaUrl' => '/work',
        ]],
        'showControls' => true,
        'autoAdvance' => false,
    ]);

    expect($renderData->slides)->toBeInstanceOf(DataCollection::class);

    $html = view('capell-widget-slideshow::widget', ['renderData' => $renderData])->render();

    expect($html)->toContain('data-capell-widget-slideshow')
        ->not->toContain('data-capell-slideshow');

    AssertPublicHtmlContainsNoAuthoringSurfaceAction::run(new Response(
        '<!doctype html><html><body>' . $html . '</body></html>',
        Response::HTTP_OK,
        ['Content-Type' => 'text/html'],
    ));

    expect(resolve(PublicHtmlSafetyInspector::class)->containsAuthoringSurface(
        '<section data-capell-slideshow>Widget</section>',
    ))->toBeTrue();

    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('instance_id')
        ->not->toContain('capell-app/widget-slideshow');
});

it('renders localized control labels rather than hardcoded english or raw translation keys', function (): void {
    $renderData = SlideshowRenderData::from([
        'label' => 'Featured work',
        'slides' => [
            ['image' => 'slides/one.jpg', 'alt' => 'First slide'],
            ['image' => 'slides/two.jpg', 'alt' => 'Second slide'],
        ],
        'showControls' => true,
        'autoAdvance' => true,
    ]);

    $html = view('capell-widget-slideshow::widget', ['renderData' => $renderData])->render();

    // Resolved translations, not the raw `capell-widget-slideshow::widget.*` keys.
    expect($html)
        ->not->toContain('capell-widget-slideshow::widget.')
        ->toContain('aria-label="Previous slide"')
        ->toContain('aria-label="Next slide"')
        ->toContain('aria-label="Pause slideshow"')
        ->toContain('aria-label="1 of 2"')
        ->toContain('data-label-resume="Resume"')
        ->toContain('data-label-resume-slideshow="Resume slideshow"');
});

it('rejects remote slide media and unsafe call to action schemes', function (): void {
    expect(fn (): SlideshowInputData => SlideshowInputData::validateAndCreate([
        'label' => 'Unsafe',
        'slides' => [['image' => 'https://remote.test/image.jpg', 'alt' => 'Remote']],
    ]))->toThrow(ValidationException::class);

    expect(fn (): SlideshowInputData => SlideshowInputData::validateAndCreate([
        'label' => 'Unsafe',
        'slides' => [['image' => 'slides/local.jpg', 'alt' => 'Local', 'ctaUrl' => 'javascript:alert(1)']],
    ]))->toThrow(ValidationException::class);
});
