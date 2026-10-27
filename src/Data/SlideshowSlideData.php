<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Data;

use Spatie\LaravelData\Data;

final class SlideshowSlideData extends Data
{
    public function __construct(
        public string $image,
        public string $alt,
        public ?string $caption = null,
        public ?string $ctaLabel = null,
        public ?string $ctaUrl = null,
    ) {}

    /** @return array<string, list<string>> */
    public static function rules(): array
    {
        return [
            'image' => ['required', 'string', 'max:2048', 'regex:/^(?![a-z][a-z0-9+.-]*:)(?!\/\/)[A-Za-z0-9_\/.-]+$/i'],
            'alt' => ['required', 'string', 'max:180'],
            'caption' => ['nullable', 'string', 'max:300'],
            'ctaLabel' => ['required_with:ctaUrl', 'nullable', 'string', 'max:80'],
            'ctaUrl' => ['required_with:ctaLabel', 'nullable', 'string', 'max:2048', 'regex:/^(?:\/(?!\/)|https?:\/\/)/i'],
        ];
    }
}
