<?php

declare(strict_types=1);

use Capell\Admin\Filament\Components\Forms\Editor\ContentBuilder;
use Capell\Admin\Support\Widgets\WidgetDiscovery;
use Capell\Core\Facades\CapellCore;
use Capell\LayoutBuilder\Models\Widget;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Filament\SlideshowWidget;
use Capell\WidgetSlideshow\Providers\WidgetSlideshowServiceProvider;
use Dom\HTMLDocument;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/** @param array<string, mixed>|null $state */
function slideshowBuilderForm(Widget $widget, ?array $state = null): Schema
{
    $host = new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;

        /** @var array<string, mixed> */
        public array $data = [];
    };

    // Match the persisted attribute so Core can authorise reopened upload paths.
    return Schema::make($host)->model($widget)->statePath('data')->components([
        ContentBuilder::make('meta.content')->blocks([SlideshowWidget::make()]),
    ])->fill(['meta' => $state ?? $widget->meta ?? []]);
}

beforeEach(function (): void {
    CapellCore::forcePackageInstalled('capell-app/widget-slideshow');
    $this->app->register(WidgetSlideshowServiceProvider::class);
    resolve(WidgetDiscovery::class)->registerAuthoritative(SlideshowWidget::class);
    config()->set('filament.default_filesystem_disk', 'public');
    Storage::fake('public');
    UploadedFile::fake()->image('one.jpg')->storeAs('slides', 'one.jpg', 'public');
});

it('validates incomplete stored buttons without deleting them and allows repair', function (string $label, string $url, string $field): void {
    $state = ['content' => [['type' => SlideshowWidget::getWidgetName(), 'data' => [
        'label' => 'Gallery', 'slides' => [['image' => 'slides/one.jpg', 'alt' => 'First slide', 'ctaLabel' => $label, 'ctaUrl' => $url]],
    ]]]];
    $widget = Widget::factory()->create(['meta' => $state]);
    $form = slideshowBuilderForm($widget->refresh());

    try {
        $widget->update($form->getState());
        $this->fail('Expected an error on the incomplete CTA field.');
    } catch (ValidationException $exception) {
        expect(collect(array_keys($exception->errors()))->contains(static fn (string $path): bool => str_ends_with($path, '.' . $field)))->toBeTrue();
    }
    expect($widget->refresh()->meta)->toBe($state);
    $state['content'][0]['data']['slides'][0]['ctaLabel'] = 'Explore';
    $state['content'][0]['data']['slides'][0]['ctaUrl'] = '/work';
    $widget->update(slideshowBuilderForm($widget, $state)->getState());
    expect(data_get($widget->refresh()->meta, 'content.0.data.slides.0.ctaUrl'))->toBe('/work');
})->with([
    'label only' => ['Explore', '', 'ctaUrl'],
    'URL only' => ['', '/work', 'ctaLabel'],
    'unsafe scheme' => ['Explore', 'javascript:alert(1)', 'ctaUrl'],
]);

it('saves reopens and renders complete or empty buttons', function (string $label, string $url): void {
    $state = ['content' => [['type' => SlideshowWidget::getWidgetName(), 'data' => [
        'label' => 'Gallery', 'slides' => [['image' => 'slides/one.jpg', 'alt' => 'First slide', 'ctaLabel' => $label, 'ctaUrl' => $url]],
    ]]]];
    $widget = Widget::factory()->create(['meta' => $state]);
    $widget->update(slideshowBuilderForm($widget)->getState());
    $form = slideshowBuilderForm($widget->refresh());
    $image = collect($form->getFlatFields())->first(static fn (Field $field): bool => $field instanceof FileUpload);
    $this->assertInstanceOf(FileUpload::class, $image);
    expect($image->getState())->toBe('slides/one.jpg')
        ->and($image->getUploadedFiles())->toHaveCount(1);
    $widget->update($form->getState());
    expect(data_get($widget->refresh()->meta, 'content.0.data.slides.0.image'))->toBe('slides/one.jpg');
    $payload = data_get($widget->refresh()->meta, 'content.0.data');
    $this->assertIsArray($payload);
    $document = HTMLDocument::createFromString(view('capell-widget-slideshow::widget', ['renderData' => SlideshowRenderData::from($payload)])->render(), LIBXML_NOERROR);
    expect($document->querySelectorAll('article a'))->toHaveCount($url === '' ? 0 : 1);
    if ($url !== '') {
        expect($document->querySelector('article a')?->getAttribute('href'))->toBe($url)
            ->and($document->querySelector('article a')?->textContent)->toBe($label);
    }
})->with([['', ''], ['Explore', '/work'], ['Explore', 'https://example.com/work']]);

it('rejects an image path not owned by the reopened widget', function (): void {
    $saved = ['content' => [['type' => SlideshowWidget::getWidgetName(), 'data' => [
        'label' => 'Gallery', 'slides' => [['image' => 'slides/one.jpg', 'alt' => 'First slide']],
    ]]]];
    $widget = Widget::factory()->create(['meta' => $saved]);
    $form = slideshowBuilderForm($widget);
    $image = collect($form->getFlatFields())->first(static fn (Field $field): bool => $field instanceof FileUpload);
    $this->assertInstanceOf(FileUpload::class, $image);
    expect($image->getUploadedFiles())->toHaveCount(1);
    UploadedFile::fake()->image('unrelated.jpg')->storeAs('slides', 'unrelated.jpg', 'public');
    $image->state('slides/unrelated.jpg');

    try {
        $widget->update($form->getState());
        $this->fail('Expected upload validation to reject an unrelated image.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey((string) $image->getStatePath());
    }

    expect($widget->refresh()->meta)->toBe($saved);
});
