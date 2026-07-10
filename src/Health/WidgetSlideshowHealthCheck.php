<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Health;

use Capell\LayoutBuilder\Health\AbstractWidgetExtensionHealthCheck;
use Capell\WidgetSlideshow\Filament\SlideshowWidget;

final class WidgetSlideshowHealthCheck extends AbstractWidgetExtensionHealthCheck
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.1';
    }

    protected static function definitionKey(): string
    {
        return 'capell-app.slideshow';
    }

    protected static function filamentWidget(): string
    {
        return SlideshowWidget::class;
    }

    protected static function fallbackView(): string
    {
        return 'capell-widget-slideshow::widget';
    }

    protected static function resourceGroups(): array
    {
        return ['capell-app.widget-slideshow'];
    }
}
