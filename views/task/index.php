<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Task;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tasks');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Task'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function ($model) {
                    $color = 'black';
                    switch ($model->status) {
                        case Task::STATUS_TO_RELEASE:
                            $color = 'red';
                            break;
                        case Task::STATUS_IN_PROGRESS:
                            $color = 'green';
                            break;
                        case Task::STATUS_SUSPENDED:
                            $color = '#999'; // Light gray
                            break;
                        case Task::STATUS_COMPLETED:
                            $color = 'black';
                            break;
                    }
                    return Html::a(Html::encode($model->title), ['view', 'id' => $model->id], ['style' => 'color: ' . $color]);
                },
            ],
            // Description is intentionally omitted in list view
            [
                'attribute' => 'assigned_to',
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ($model->status === Task::STATUS_TO_RELEASE) {
                        return ['style' => 'color: red'];
                    }
                    return ['style' => 'color: black'];
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                },
                'filter' => \kartik\select2\Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'statusFilter',
                    'data' => Task::getStatusList(),
                    'options' => [
                        'placeholder' => Yii::t('app', 'Select Status'),
                        'multiple' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        //'width' => '100%',
                    ],
                ]),
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ($model->status === Task::STATUS_TO_RELEASE) {
                        return ['style' => 'color: red'];
                    }
                    return ['style' => 'color: black'];
                },
            ],
            [
                'attribute' => 'gitlab_issue',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->gitlab_issue)) {
                        return Html::a(
                            '<i class="glyphicon glyphicon-link"></i> GitLab',
                            $model->gitlab_issue,
                            [
                                'class' => 'btn btn-sm btn-info',
                                'target' => '_blank',
                                'title' => $model->gitlab_issue
                            ]
                        );
                    }
                    return '';
                },
                'filter' => false,
            ],
            [
                'attribute' => 'priority',
                'format' => 'raw',
                'value' => function ($model) {
                     return str_repeat('●', $model->priority) . str_repeat('○', 5 - $model->priority);
                },
                'filter' => [
                    1 => '●○○○○',
                    2 => '●●○○○',
                    3 => '●●●○○',
                    4 => '●●●●○',
                    5 => '●●●●●',
                ],
                'contentOptions' => ['style' => 'color: #d9534f; font-size: 1.2em; letter-spacing: 2px;'], 
            ],
            //'created_at',
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
