<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Filament;

use Capell\Admin\Contracts\Widgets\FilamentWidget;
use Capell\LayoutBuilder\Filament\Components\Forms\WidgetFileUpload;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Override;

final class SlideshowWidget implements FilamentWidget
{
    #[Override]
    public static function getWidgetName(): string
    {
        return 'capell-app.slideshow';
    }

    #[Override]
    public static function make(): Block
    {
        return Block::make(self::getWidgetName())
            ->label(__('capell-widget-slideshow::widget.label'))
            ->schema([
                TextInput::make('label')->label(__('capell-widget-slideshow::widget.accessible_label'))->required()->maxLength(100),
                Repeater::make('slides')->label(__('capell-widget-slideshow::widget.slides'))->minItems(1)->maxItems(20)->schema([
                    WidgetFileUpload::make('image')->label(__('capell-widget-slideshow::widget.image'))->image()->required(),
                    TextInput::make('alt')->label(__('capell-widget-slideshow::widget.alt'))->required()->maxLength(180),
                    TextInput::make('caption')->label(__('capell-widget-slideshow::widget.caption'))->maxLength(300),
                    TextInput::make('ctaLabel')->label(__('capell-widget-slideshow::widget.cta_label'))->maxLength(80)
                        ->requiredWith('ctaUrl')
                        ->validationMessages(['required_with' => __('capell-widget-slideshow::widget.cta_label_required')]),
                    TextInput::make('ctaUrl')->label(__('capell-widget-slideshow::widget.cta_url'))->maxLength(2048)
                        ->requiredWith('ctaLabel')
                        ->regex('/^(?:\/(?!\/)|https?:\/\/)/i')
                        ->validationMessages([
                            'required_with' => __('capell-widget-slideshow::widget.cta_url_required'),
                            'regex' => __('capell-widget-slideshow::widget.cta_url_invalid'),
                        ]),
                ]),
                Toggle::make('showControls')->label(__('capell-widget-slideshow::widget.show_controls'))->helperText(__('capell-widget-slideshow::widget.controls_helper'))->default(true),
                Toggle::make('autoAdvance')->label(__('capell-widget-slideshow::widget.auto_advance'))->default(false),
            ]);
    }
}
