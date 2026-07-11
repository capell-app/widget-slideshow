<?php

declare(strict_types=1);

use Capell\Frontend\Support\Assets\FrontendResourceRegistry;
use Capell\LayoutBuilder\Support\WidgetExtensions\WidgetExtensionRegistry;
use Capell\WidgetSlideshow\Data\SlideshowInputData;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Health\WidgetSlideshowHealthCheck;
use Capell\WidgetSlideshow\Providers\WidgetSlideshowServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;

beforeEach(function (): void {
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

    $html = view('capell-widget-slideshow::widget', compact('renderData'))->render();

    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('instance_id')
        ->not->toContain('capell-app/widget-slideshow');
});

it('rejects remote slide media and unsafe call to action schemes', function (): void {
    expect(fn () => SlideshowInputData::validateAndCreate([
        'label' => 'Unsafe',
        'slides' => [['image' => 'https://remote.test/image.jpg', 'alt' => 'Remote']],
    ]))->toThrow(ValidationException::class);

    expect(fn () => SlideshowInputData::validateAndCreate([
        'label' => 'Unsafe',
        'slides' => [['image' => 'slides/local.jpg', 'alt' => 'Local', 'ctaUrl' => 'javascript:alert(1)']],
    ]))->toThrow(ValidationException::class);
});
