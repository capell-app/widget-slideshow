<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class SlideshowRenderData extends Data
{
    /** @param DataCollection<int, SlideshowSlideData> $slides */
    public function __construct(
        public string $label,
        #[DataCollectionOf(SlideshowSlideData::class)]
        public DataCollection $slides,
        public bool $showControls = true,
        public bool $autoAdvance = false,
    ) {}
}
