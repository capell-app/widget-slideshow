<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Support\Assets\FrontendResourceRegistry;
use Capell\Frontend\Support\Security\PublicHtmlSafetyInspector;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionPayloadBatchData;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionPayloadInputData;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionRenderContextData;
use Capell\LayoutBuilder\Support\WidgetExtensions\WidgetExtensionRegistry;
use Capell\WidgetSlideshow\Actions\ResolveSlideshowPayloadsAction;
use Capell\WidgetSlideshow\Data\SlideshowInputData;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Health\WidgetSlideshowHealthCheck;
use Capell\WidgetSlideshow\Providers\WidgetSlideshowServiceProvider;
use Dom\HTMLDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
        'autoAdvance' => false,
    ]);

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

it('always provides manual navigation for multiple slides for every stored playback setting, ignoring a legacy controls flag', function (bool $controls, bool $auto): void {
    $data = SlideshowRenderData::from(['label' => 'Slides', 'slides' => [['image' => 'one.jpg', 'alt' => 'One'], ['image' => 'two.jpg', 'alt' => 'Two', 'ctaLabel' => 'Visit', 'ctaUrl' => '/visit']], 'showControls' => $controls, 'autoAdvance' => $auto]);
    $html = view('capell-widget-slideshow::widget', ['renderData' => $data])->render();
    $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
    expect($document->querySelectorAll('button[data-slideshow-next]'))->toHaveCount(1)
        ->and($document->querySelectorAll('button[data-slideshow-previous]'))->toHaveCount(1)
        ->and($document->querySelector('a[href="/visit"]'))->not->toBeNull()
        ->and($document->querySelectorAll('button[data-slideshow-toggle]'))->toHaveCount($auto ? 1 : 0);
})->with([[false, false], [false, true], [true, false], [true, true]]);

it('loads the first slide eagerly with high priority and later slides lazily', function (): void {
    $data = SlideshowRenderData::from(['label' => 'Slides', 'slides' => [
        ['image' => 'one.jpg', 'alt' => 'One', 'width' => 1600, 'height' => 900],
        ['image' => 'two.jpg', 'alt' => 'Two'],
    ]]);
    $document = HTMLDocument::createFromString(view('capell-widget-slideshow::widget', ['renderData' => $data])->render(), LIBXML_NOERROR);
    $images = $document->querySelectorAll('img');

    expect($images[0]->getAttribute('loading'))->toBe('eager')
        ->and($images[0]->getAttribute('fetchpriority'))->toBe('high')
        ->and($images[0]->getAttribute('width'))->toBe('1600')
        ->and($images[0]->getAttribute('height'))->toBe('900')
        ->and($images[1]->getAttribute('loading'))->toBe('lazy')
        ->and($images[1]->hasAttribute('fetchpriority'))->toBeFalse()
        ->and($images[1]->hasAttribute('width'))->toBeFalse();
});

it('resolves stored image dimensions into render data and ignores missing or traversing paths', function (): void {
    Storage::fake('public');
    ob_start();
    imagepng(imagecreatetruecolor(320, 180));
    Storage::disk('public')->put('slides/one.png', (string) ob_get_clean());

    $input = SlideshowInputData::validateAndCreate(['label' => 'Slides', 'slides' => [
        ['image' => 'slides/one.png', 'alt' => 'One'],
        ['image' => 'slides/missing.png', 'alt' => 'Missing'],
        ['image' => '../secret.png', 'alt' => 'Traversal'],
    ]]);
    $batch = new WidgetExtensionPayloadBatchData([new WidgetExtensionPayloadInputData('instance', $input)], new WidgetExtensionRenderContextData(null));
    $payload = resolve(ResolveSlideshowPayloadsAction::class)->resolve($batch)['instance'];

    expect($payload->slides[0]->width)->toBe(320)
        ->and($payload->slides[0]->height)->toBe(180)
        ->and($payload->slides[1]->width)->toBeNull()
        ->and($payload->slides[2]->width)->toBeNull();
});

it('serves a srcset for wide stored images and leaves missing images plain, with no authoring state', function (): void {
    Storage::fake('public');
    ob_start();
    imagejpeg(imagecreatetruecolor(1800, 900));
    Storage::disk('public')->put('slides/wide.jpg', (string) ob_get_clean());

    $input = SlideshowInputData::validateAndCreate(['label' => 'Slides', 'slides' => [
        ['image' => 'slides/wide.jpg', 'alt' => 'Wide'],
        ['image' => 'slides/missing.jpg', 'alt' => 'Missing'],
    ]]);
    $batch = new WidgetExtensionPayloadBatchData([new WidgetExtensionPayloadInputData('instance', $input)], new WidgetExtensionRenderContextData(null));
    $payload = resolve(ResolveSlideshowPayloadsAction::class)->resolve($batch)['instance'];
    $html = view('capell-widget-slideshow::widget', ['renderData' => $payload])->render();
    $images = HTMLDocument::createFromString($html, LIBXML_NOERROR)->querySelectorAll('img');

    expect($images[0]->getAttribute('srcset'))->toContain(' 480w')->toContain(' 1200w')->toContain('slides/wide.jpg 1800w')
        ->and($images[0]->getAttribute('sizes'))->toBe('100vw')
        ->and($images[0]->getAttribute('width'))->toBe('1800')
        ->and($images[1]->hasAttribute('srcset'))->toBeFalse()
        ->and($html)->not->toContain('instance')
        ->not->toContain(Storage::disk('public')->path(''));
});
