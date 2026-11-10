# Slideshow Widget reference

<!-- prettier-ignore-start -->

Developer reference for `capell-app/widget-slideshow`. Start with the [README](../README.md) for what the extension does and how to install it.

## Package Details

Slideshow Widget is an **Available**, **No schema impact** Capell package in the **Capell Essentials** product group. It ships as `capell-app/widget-slideshow` and extends these surfaces: admin, frontend.

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/widget-slideshow`
- Namespace: `Capell\WidgetSlideshow`
- Theme key: not applicable

## Technical Shape

### Service providers

- `Capell\WidgetSlideshow\Providers\WidgetSlideshowServiceProvider`

### Filament classes

- `SlideshowWidget`

### Data objects

- `SlideshowInputData`
- `SlideshowRenderData`
- `SlideshowSlideData`

### Manifest contributions

- `asset: Capell\WidgetSlideshow\WidgetSlideshowContribution`
- `content-widget: Capell\WidgetSlideshow\WidgetSlideshowContribution`

### Health checks

- `Capell\WidgetSlideshow\Health\WidgetSlideshowHealthCheck`

### Blade views

- `packages/widget-slideshow/resources/views/widget.blade.php`

### Cache tags

- `widget-slideshow`


## Data Model

This package has no schema impact. It extends Capell through `asset` contributions and `content-widget` contributions instead of declaring package-owned tables.

<!-- prettier-ignore-end -->
