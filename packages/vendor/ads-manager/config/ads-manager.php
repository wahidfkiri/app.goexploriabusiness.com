<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ads Manager Configuration
    |--------------------------------------------------------------------------
    */

    'default_currency' => 'TND',

    'ad_formats' => [
        'banner'    => ['width' => 728, 'height' => 90,  'label' => 'Bannière horizontale'],
        'rectangle' => ['width' => 300, 'height' => 250, 'label' => 'Rectangle moyen'],
        'square'    => ['width' => 250, 'height' => 250, 'label' => 'Carré'],
        'skyscraper'=> ['width' => 160, 'height' => 600, 'label' => 'Gratte-ciel'],
        'leaderboard'=> ['width' => 970, 'height' => 90, 'label' => 'Leader board'],
        'interstitial'=> ['width' => 600, 'height' => 500,'label' => 'Interstitiel'],
        'video_landscape' => ['width' => 1920, 'height' => 1080, 'label' => 'Vidéo paysage 16:9', 'types' => ['video']],
        'video_instream' => ['width' => 640, 'height' => 360, 'label' => 'Vidéo in-stream 16:9', 'types' => ['video']],
        'video_vertical' => ['width' => 720, 'height' => 1280, 'label' => 'Vidéo verticale 9:16', 'types' => ['video']],
        'social_story' => ['width' => 1080, 'height' => 1920, 'label' => 'Stories Instagram/Facebook 9:16', 'types' => ['video']],
        'instagram_reels' => ['width' => 1080, 'height' => 1920, 'label' => 'Instagram Reels 9:16', 'types' => ['video']],
        'tiktok_reels' => ['width' => 1080, 'height' => 1920, 'label' => 'TikTok/Reels 9:16', 'types' => ['video']],
        'youtube_shorts' => ['width' => 1080, 'height' => 1920, 'label' => 'YouTube Shorts 9:16', 'types' => ['video']],
        'social_feed_square' => ['width' => 1080, 'height' => 1080, 'label' => 'Feed social carré 1:1', 'types' => ['video', 'image']],
        'social_feed_portrait' => ['width' => 1080, 'height' => 1350, 'label' => 'Feed social portrait 4:5', 'types' => ['video', 'image']],
    ],

    'pricing_models' => [
        'cpm'  => 'CPM — Coût par 1 000 impressions',
        'cpc'  => 'CPC — Coût par clic',
        'cpa'  => 'CPA — Coût par action',
        'flat' => 'Forfait journalier/mensuel',
    ],

    'ad_statuses' => [
        'draft'    => 'Brouillon',
        'pending'  => 'En attente de validation',
        'active'   => 'Active',
        'paused'   => 'Pausée',
        'expired'  => 'Expirée',
        'rejected' => 'Rejetée',
    ],

    'placement_positions' => [
        'header'          => 'En-tête de page',
        'footer'          => 'Pied de page',
        'sidebar_left'    => 'Barre latérale gauche',
        'sidebar_right'   => 'Barre latérale droite',
        'content_top'     => 'Haut du contenu',
        'content_bottom'  => 'Bas du contenu',
        'content_middle'  => 'Milieu du contenu',
        'popup'           => 'Pop-up',
        'interstitial'    => 'Interstitiel',
    ],

    'max_ads_per_zone' => 3,

    'default_cpm_rate'  => 5.00,
    'default_cpc_rate'  => 0.50,

    'tracking_pixel_url' => env('ADS_TRACKING_URL', null),

    'image_disk'   => 'public',
    'image_path'   => 'ads/images',
    'max_file_size'=> 2048, // KB

    'auto_approve'     => false,
    'click_fraud_threshold' => 10, // max clicks par IP par heure

    /*
    |--------------------------------------------------------------------------
    | Widget configuration
    |--------------------------------------------------------------------------
    | widget_base_url : URL publique de base pour le loader.js et les appels
    |   fetch cross-domain. Doit être accessible depuis les sites externes.
    |   Défaut : config('app.url') (souvent l'URL de l'admin).
    |   Si l'admin n'est pas public, surchargez-la via .env :
    |     ADS_WIDGET_BASE_URL=https://api.votresite.com
    */
    'widget_base_url' => env('ADS_WIDGET_BASE_URL'),
    'widget_version'  => '1.0',
];
