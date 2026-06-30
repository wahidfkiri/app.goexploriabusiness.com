<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\GrapesJSServiceProvider::class,
    Vendor\Administration\AdministrationServiceProvider::class,
    Vendor\Customer\CustomerServiceProvider::class,
    Vendor\Ecommerce\EcommerceServiceProvider::class,
    Vendor\Editor\EditorServiceProvider::class,
    Vendor\Etablissement\EtablissementServiceProvider::class,
    Vendor\Gemini\GeminiServiceProvider::class,
    Vendor\GeoMap\GeoMapServiceProvider::class,
    Vendor\MailMarketing\MailMarketingServiceProvider::class,
    Vendor\MapMarker\MapMarkerServiceProvider::class,
    Vendor\Plugins\PluginsServiceProvider::class,
    Vendor\Project\ProjectServiceProvider::class,
    Vendor\Setting\SettingServiceProvider::class,
    Vendor\Template\TemplateServiceProvider::class,
    Vendor\Theme\ThemeServiceProvider::class,

    Vendor\LocationDataEngine\Providers\LocationDataEngineServiceProvider::class,
    Vendor\MapsDataEngine\Providers\MapsDataEngineServiceProvider::class,
    Vendor\Website\WebsiteServiceProvider::class,
    Vendor\Chatbot\InternalChatServiceProvider::class,
    Vendor\AdsManager\AdsManagerServiceProvider::class,
    Vendor\ModeEmploi\ModeEmploiServiceProvider::class,
];

if (class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
