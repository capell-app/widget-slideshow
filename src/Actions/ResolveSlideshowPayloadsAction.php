<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Actions;

use Capell\Core\Actions\Media\ResolveResponsiveImageAction;
use Capell\LayoutBuilder\Contracts\WidgetExtensions\WidgetExtensionBatchPayloadResolver;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionPayloadBatchData;
use Capell\WidgetSlideshow\Data\SlideshowInputData;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Data\SlideshowRenderSlideData;
use Override;

final class ResolveSlideshowPayloadsAction implements WidgetExtensionBatchPayloadResolver
{
    /** @return array<string, SlideshowRenderData> */
    #[Override]
    public function resolve(WidgetExtensionPayloadBatchData $batch): array
    {
        $payloads = [];

        foreach ($batch->items as $item) {
            if (! $item->input instanceof SlideshowInputData) {
                continue;
            }

            $slides = [];
            foreach ($item->input->slides as $slide) {
                $size = ResolveResponsiveImageAction::run($slide->image);
                $slides[] = new SlideshowRenderSlideData(
                    image: $slide->image,
                    alt: $slide->alt,
                    caption: $slide->caption,
                    ctaLabel: $slide->ctaLabel,
                    ctaUrl: $slide->ctaUrl,
                    width: $size?->width,
                    height: $size?->height,
                    srcset: $size?->srcset,
                );
            }

            $payloads[$item->instanceId] = SlideshowRenderData::from([
                'label' => $item->input->label,
                'slides' => $slides,
                'autoAdvance' => $item->input->autoAdvance,
            ]);
        }

        return $payloads;
    }
}
