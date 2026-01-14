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
            // [
            //     'attribute' => 'assigned_to',
            //     'contentOptions' => function ($model, $key, $index, $column) {
            //         if ($model->status === Task::STATUS_TO_RELEASE) {
            //             return ['style' => 'color: red'];
            //         }
            //         return ['style' => 'color: black'];
            //     },
            // ],
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
                    $html = '<div class="priority-dots" data-task-id="' . $model->id . '" data-priority="' . $model->priority . '">';
                    for ($i = 1; $i <= 5; $i++) {
                        $filled = $i <= $model->priority ? '●' : '○';
                        $html .= '<span class="priority-dot" data-priority="' . $i . '" style="cursor: pointer;">' . $filled . '</span>';
                    }
                    $html .= '</div>';
                    return $html;
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

<?php
$updatePriorityUrl = \yii\helpers\Url::to(['task/update-priority']);
$csrfToken = Yii::$app->request->csrfToken;
$this->registerJs("
$(document).on('click', '.priority-dot', function(e) {
    e.preventDefault();
    
    var dot = $(this);
    var container = dot.closest('.priority-dots');
    var taskId = container.data('task-id');
    var newPriority = dot.data('priority');
    var currentPriority = container.data('priority');
    
    // Don't update if clicking on the same priority
    if (newPriority == currentPriority) {
        return;
    }
    
    // Show loading state
    var originalHtml = container.html();
    container.html('<span style=\"opacity: 0.5;\">⏳</span>');
    
    $.ajax({
        url: '{$updatePriorityUrl}',
        type: 'POST',
        data: {
            id: taskId,
            priority: newPriority,
            _csrf: '{$csrfToken}'
        },
        success: function(response) {
            if (response.success) {
                // Update the UI
                container.data('priority', newPriority);
                var html = '';
                for (var i = 1; i <= 5; i++) {
                    var filled = i <= newPriority ? '●' : '○';
                    html += '<span class=\"priority-dot\" data-priority=\"' + i + '\" style=\"cursor: pointer;\">' + filled + '</span>';
                }
                container.html(html);
            } else {
                container.html(originalHtml);
                alert(response.message || 'Failed to update priority');
            }
        },
        error: function() {
            container.html(originalHtml);
            alert('An error occurred while updating priority');
        }
    });
});

// Add hover effect
$(document).on('mouseenter', '.priority-dot', function() {
    $(this).css('opacity', '0.6');
}).on('mouseleave', '.priority-dot', function() {
    $(this).css('opacity', '1');
});
", \yii\web\View::POS_READY);
?>
