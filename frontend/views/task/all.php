<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = "All Tasks";
?>

<h3 class="fw-bold mb-4"><?= $this->title ?></h3>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-hover mb-0'],

            'columns' => [
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'value' => fn($m) => "<b>{$m->title}</b>",
                ],

                [
                    'attribute' => 'priority',
                    'format' => 'raw',
                    'value' => function($m){
                        $c=['low'=>'success','medium'=>'warning','high'=>'danger'];
                        return "<span class='badge bg-".$c[$m->priority]."'>".ucfirst($m->priority)."</span>";
                    }
                ],

                [
                    'attribute' => 'due_date',
                    'value' => fn($m)=>$m->due_date ? date("d M Y", strtotime($m->due_date)) : "—",
                ]
            ]
        ]); ?>

    </div>
</div>
