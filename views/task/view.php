<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Task;

/* @var $this yii\web\View */
/* @var $model app\models\Task */

$this->title = $model->title;
// Remove breadcrumb - we don't want Home / Tasks navigation
\yii\web\YiiAsset::register($this);
?>
<div class="task-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'visible' => true,
            ],
            //'assigned_to',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                }
            ],
            [
                'attribute' => 'gitlab_issue',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->gitlab_issue)) {
                        return Html::a(
                            '<i class="glyphicon glyphicon-link"></i> ' . Yii::t('app', 'Open GitLab Issue'),
                            $model->gitlab_issue,
                            [
                                'class' => 'btn btn-info',
                                'target' => '_blank'
                            ]
                        );
                    }
                    return '<span class="not-set">' . Yii::t('app', '(not set)') . '</span>';
                }
            ],
            [
                'attribute' => 'priority',
                'format' => 'raw',
                'value' => function ($model) {
                     return '<span style="color: #d9534f; font-size: 1.2em; letter-spacing: 2px;">' 
                        . str_repeat('●', $model->priority) . str_repeat('○', 5 - $model->priority) 
                        . '</span>';
                }
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
