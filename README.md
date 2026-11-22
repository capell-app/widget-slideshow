# Slideshow Widget

<!-- prettier-ignore-start -->

On the page for a finished job or last month's event, visitors step through the photos at their own pace. Each is shown wide, at a 16 by 9 shape, with its caption underneath and Previous and Next buttons below that. Up to twenty photos share the one spot, and Next on the last photo takes a visitor back to the first.

Editors add a Slideshow block from Layout Builder, the page builder. You name the slideshow, and screen readers read that name out. For every slide you upload an image and describe it in a few words. A caption is optional, and so is a link, whose label and address have to be filled in together.

Tick Advance automatically and the slides move on every five seconds, with a Pause button for visitors. If a visitor's device asks for reduced motion, the slideshow starts paused and they can press Resume.

- **Price and availability:** Check current availability and price on the [Capell Marketplace listing](https://capell.app/marketplace/extensions/widget-slideshow).
- **Licence:** Check the licence terms on the [Capell Marketplace listing](https://capell.app/marketplace/extensions/widget-slideshow).
- **Requires:** `PHP ^8.4`, `capell-app/admin ^1.0`, `capell-app/core ^1.0.79`, `capell-app/frontend ^1.0.79`, `capell-app/layout-builder ^1.0.44`, `spatie/laravel-package-tools ^1.0`.
- **Install:** `composer require capell-app/widget-slideshow:@beta`.

## Quick Start

1. Install the package: `composer require capell-app/widget-slideshow:@beta`.
2. Open a page, click **Layout builder**, **Add widgets**, then choose **Slideshow**.

## For Developers

The typed extension registers its public assets and validates slide media and links while tests protect the authoring boundary.

## Configure

This extension works without configuration. There is nothing to set after install.

## Limits And Pitfalls

- Install the required Capell packages at the versions in `composer.json`: `capell-app/admin ^1.0`, `capell-app/core ^1.0.79`, `capell-app/frontend ^1.0.79`, `capell-app/layout-builder ^1.0.44`.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Custom write integrations must preserve invalidation for `widget-slideshow` cache tags.

## Screens And Workflow

![Slideshow public output](docs/screenshots/slideshow-public-output.png)

![Slideshow fields in Layout Builder](docs/screenshots/slideshow-layout-builder-editor.png)

- Slideshow Widget extension card (marketplace).
- Slideshow fields in Layout Builder (admin).
- Slideshow public output (frontend).
- Slideshow fields in Layout Builder with admin sidebar menu open (admin).

## Install Impact

- Required packages: `capell-app/admin`, `capell-app/core`, `capell-app/frontend`, `capell-app/layout-builder`.
- Admin navigation: no admin page or resource contribution is declared.
- Admin/editor extensions: `content-widget: WidgetSlideshowContribution`.
- Permissions: no package permission declarations or Shield gates detected; host access rules still apply.
- Public routes: none declared.
- Database changes: no package migrations declared.
- Config: no package config files.
- Settings: no package settings declared.
- Queues or schedules: none declared.
- Cache tags: `widget-slideshow`.
- Commands: none declared.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Upgrading

No manual steps between minor versions.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Troubleshooting](#troubleshooting)
- [Developer reference](docs/reference.md)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Layout Builder](../layout-builder/README.md).

<!-- prettier-ignore-end -->
