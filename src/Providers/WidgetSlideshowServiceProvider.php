<?php

declare(strict_types=1);

namespace Capell\WidgetSlideshow\Providers;

use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Enums\PresentationLoadingStrategy;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Frontend\Data\Assets\FrontendResourceData;
use Capell\Frontend\Data\Assets\FrontendResourceGroupData;
use Capell\Frontend\Data\Assets\PublicResourceSourceData;
use Capell\Frontend\Support\Assets\FrontendResourceRegistry;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionCapabilitiesData;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionDefinitionData;
use Capell\LayoutBuilder\Enums\WidgetPresentationCapability;
use Capell\LayoutBuilder\Support\WidgetExtensions\WidgetExtensionRegistrar;
use Capell\WidgetSlideshow\Data\SlideshowInputData;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Filament\SlideshowWidget;
use Override;
use Spatie\LaravelPackageTools\Package;

final class WidgetSlideshowServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-widget-slideshow';

    public static string $packageName = 'capell-app/widget-slideshow';

    public static PackageTypeEnum $type = PackageTypeEnum::Package;

    #[Override]
    public function configurePackage(Package $package): void
    {
        $package->name(self::$name)->hasViews()->hasTranslations()->hasAssets();
    }

    #[Override]
    protected function bootInstalledRuntime(): void
    {
        resolve(WidgetExtensionRegistrar::class)->register(new WidgetExtensionDefinitionData(
            key: 'capell-app.slideshow',
            packageName: self::$packageName,
            stateVersion: 1,
            filamentWidget: SlideshowWidget::class,
            inputData: SlideshowInputData::class,
            renderData: SlideshowRenderData::class,
            fallbackView: 'capell-widget-slideshow::widget',
            components: ['blade' => 'capell::widgets.capell-app.slideshow'],
            resourceGroups: ['capell-app.widget-slideshow'],
            defaultResourceLoadingStrategy: PresentationLoadingStrategy::Visible,
            capabilities: new WidgetExtensionCapabilitiesData(
                requiresInstanceIdentity: true,
                presentationCapabilities: [WidgetPresentationCapability::Width, WidgetPresentationCapability::Alignment, WidgetPresentationCapability::LoadingStrategy],
            ),
        ));

        resolve(FrontendResourceRegistry::class)->register(new FrontendResourceGroupData(
            key: 'capell-app.widget-slideshow',
            label: 'Slideshow widget',
            package: self::$packageName,
            resources: [
                FrontendResourceData::style(
                    handle: 'capell-app/widget-slideshow:widget-style',
                    package: self::$packageName,
                    source: new PublicResourceSourceData('/vendor/capell-widget-slideshow/widget.css'),
                    loadingStrategy: PresentationLoadingStrategy::Visible,
                ),
                FrontendResourceData::classicScript(
                    handle: 'capell-app/widget-slideshow:widget-script',
                    package: self::$packageName,
                    source: new PublicResourceSourceData('/vendor/capell-widget-slideshow/widget.js'),
                    loadingStrategy: PresentationLoadingStrategy::Visible,
                ),
            ],
        ));
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }
}
