<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow;

use Capell\Core\Contracts\Extensions\RegistersExtensionContentWidget;

final class WidgetSlideshowContribution implements RegistersExtensionContentWidget
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^0.0';
    }
}
