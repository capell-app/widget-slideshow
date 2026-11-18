<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class SlideshowRenderData extends Data
{
    /** @param DataCollection<int, SlideshowRenderSlideData> $slides */
    public function __construct(
        public string $label,
        #[DataCollectionOf(SlideshowRenderSlideData::class)]
        public DataCollection $slides,
        public bool $autoAdvance = false,
    ) {}
}
