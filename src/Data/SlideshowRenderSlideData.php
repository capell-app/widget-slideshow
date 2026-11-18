<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Data;

use Spatie\LaravelData\Data;

final class SlideshowRenderSlideData extends Data
{
    public function __construct(
        public string $image,
        public string $alt,
        public ?string $caption = null,
        public ?string $ctaLabel = null,
        public ?string $ctaUrl = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $srcset = null,
    ) {}
}
