<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'teammanagment/css/style.css',
        'css/task-edit.css',
        'teammanagment/css/kanban.css',
        'css/taskcard.css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
        
    ];
    public $js = [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        'teammanagment/js/kanban.js',
        'teammanagment/js/edit-task.js',
        'teammanagment/js/subtask.js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        'teammanagment/js/dashboard.js',
        
        // 'teammanagment/js/draganddrop.js',

        
    ];
     public $jsOptions = [
        'position' => \yii\web\View::POS_END   
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
