<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Task;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = Yii::t('app', 'Tasks');
// Remove breadcrumb - we don't want Home / Tasks navigation
?>
<div class="task-index">

    <h1><?= Html::a(Html::encode($this->title), ['index'], ['style' => 'text-decoration: none; color: inherit;']) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Task'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(['id' => 'task-grid-pjax']) ?>
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
                        case Task::STATUS_IN_REVIEW:
                            $color = '#FF8C00'; // Orange
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
                'format' => 'raw',
                'value' => function ($model) {
                    $options = Task::getStatusList();
                    $dropdownOptions = '';
                    
                    // Mappa dei colori per gli stati
                    $colors = [
                        Task::STATUS_TO_RELEASE => 'red',
                        Task::STATUS_IN_PROGRESS => 'green', 
                        Task::STATUS_IN_REVIEW => '#FF8C00',
                        Task::STATUS_SUSPENDED => '#999',
                        Task::STATUS_COMPLETED => 'black',
                    ];
                    
                    $currentColor = isset($colors[$model->status]) ? $colors[$model->status] : 'black';
                    
                    foreach ($options as $key => $value) {
                        $selected = ($model->status == $key) ? ' selected' : '';
                        $dropdownOptions .= '<option value="' . Html::encode($key) . '"' . $selected . '>' . Html::encode($value) . '</option>';
                    }
                    
                    return '<select class="form-control status-dropdown" data-id="' . $model->id . '" style="border: none; background: transparent; font-weight: bold; color: ' . $currentColor . '; width: 100%; min-width: 120px;">' 
                        . $dropdownOptions . '</select>';
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
                    $color = 'black';
                    switch ($model->status) {
                        case Task::STATUS_TO_RELEASE:
                            $color = 'red';
                            break;
                        case Task::STATUS_IN_PROGRESS:
                            $color = 'green';
                            break;
                        case Task::STATUS_IN_REVIEW:
                            $color = '#FF8C00'; // Orange
                            break;
                        case Task::STATUS_SUSPENDED:
                            $color = '#999'; // Light gray
                            break;
                        case Task::STATUS_COMPLETED:
                            $color = 'black';
                            break;
                    }
                    return ['style' => 'color: ' . $color];
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

// Status dropdown change handler
$('.status-dropdown').on('change', function() {
    var taskId = $(this).data('id');
    var newStatus = $(this).val();
    var dropdown = $(this);
    
    // Mappa dei colori
    var statusColors = {
        'to_release': 'red',
        'in_progress': 'green',
        'in_review': '#FF8C00',
        'suspended': '#999',
        'completed': 'black'
    };
    
    // Aggiorna immediatamente il colore
    dropdown.css('color', statusColors[newStatus] || 'black');
    
    $.ajax({
        url: '" . Url::to(['task/change-status']) . "',
        type: 'POST',
        data: {
            id: taskId,
            status: newStatus,
            '" . Yii::$app->request->csrfParam . "': '" . Yii::$app->request->csrfToken . "'
        },
        success: function(response) {
            if (response.success) {
                // Get current URL parameters to maintain sorting and filtering
                var urlParams = new URLSearchParams(window.location.search);
                
                // Ensure default sorting is applied if none exists
                if (!urlParams.get('sort')) {
                    urlParams.set('sort', 'status,priority');
                }
                
                // Reload the page with current parameters to trigger reordering
                var newUrl = window.location.pathname + '?' + urlParams.toString();
                window.location.href = newUrl;
            } else {
                alert('Errore nell\\'aggiornamento dello stato: ' + (response.message || 'Unknown error'));
                // Revert dropdown to original value and color if needed
                var originalValue = dropdown.data('original-value');
                dropdown.val(originalValue);
                dropdown.css('color', statusColors[originalValue] || 'black');
            }
        },
        error: function() {
            alert('Errore di connessione durante l\\'aggiornamento dello stato');
            var originalValue = dropdown.data('original-value');
            dropdown.val(originalValue);
            dropdown.css('color', statusColors[originalValue] || 'black');
        }
    });
});

// Store original values for dropdowns
$(document).on('focus', '.status-dropdown', function() {
    $(this).data('original-value', $(this).val());
});
", \yii\web\View::POS_READY);
?>
