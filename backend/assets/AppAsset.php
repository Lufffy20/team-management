<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'assets/vendor/css/core.css',
        'assets/vendor/css/pages/page-auth.css',
        'assets/vendor/css/pages/page-icons.css',
        'assets/vendor/css/pages/page-misc.css',
        'assets/vendor/fonts/boxicons.css',
        'assets/vendor/css/theme-default.css',
        'assets/css/demo.css',
        'assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css',
        'assets/vendor/libs/apex-charts/apex-charts.css',
        'assets/img/favicon/favicon.ico',
        'https://fonts.gstatic.com',
        'https://fonts.googleapis.com',
        'https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
    ];
    public $js = [
        'assets/vendor/js/helpers.js',
        'assets/js/config.js',
        'assets/vendor/libs/popper/popper.js',
        'assets/vendor/js/bootstrap.js',
        'assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
        'assets/vendor/js/menu.js',
        'assets/vendor/libs/apex-charts/apexcharts.js',
        'assets/js/main.js',
        'assets/js/dashboards-analytics.js',
        'https://buttons.github.io/buttons.js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        'js/dashboard.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
