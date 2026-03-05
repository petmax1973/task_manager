<?php

use yii\helpers\Html;
use yii\helpers\Url;
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
            [
                'attribute' => 'project',
                'value' => function ($model) {
                    return $model->getProjectLabel() ?: '-';
                },
            ],
            [
                'attribute' => 'assigned_to',
                'value' => function ($model) {
                    return $model->assigned_to ?: '-';
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                }
            ],
            [
                'attribute' => 'related_tasks',
                'format' => 'raw',
                'value' => function ($model) {
                    $tasks = $model->getRelatedTaskModels();
                    if (empty($tasks)) {
                        return '<span class="not-set">' . Yii::t('app', '(not set)') . '</span>';
                    }
                    $links = [];
                    foreach ($tasks as $task) {
                        $links[] = Html::a(
                            '#' . $task->id . ' - ' . Html::encode($task->title),
                            ['view', 'id' => $task->id],
                            ['class' => 'btn btn-sm btn-info', 'style' => 'margin: 2px;']
                        );
                    }
                    return implode(' ', $links);
                },
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

    <!-- Attachments Section -->
    <div class="task-attachments mt-4">
        <h3><i class="fas fa-paperclip"></i> <?= Yii::t('app', 'Attachments') ?></h3>

        <?php if (!empty($model->attachments)): ?>
        <table class="table table-bordered attachments-table">
            <thead>
                <tr>
                    <th><?= Yii::t('app', 'File Name') ?></th>
                    <th><?= Yii::t('app', 'Size') ?></th>
                    <th><?= Yii::t('app', 'Uploaded At') ?></th>
                    <th style="width: 150px;"><?= Yii::t('app', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->attachments as $attachment): ?>
                <tr>
                    <td>
                        <i class="fas fa-file"></i>
                        <?= Html::encode($attachment->original_name) ?>
                    </td>
                    <td><?= $attachment->getFormattedSize() ?></td>
                    <td><?= Yii::$app->formatter->asDatetime($attachment->created_at) ?></td>
                    <td>
                        <?= Html::a(
                            '<i class="fas fa-download"></i> ' . Yii::t('app', 'Download'),
                            ['download-attachment', 'id' => $attachment->id],
                            ['class' => 'btn btn-sm btn-info']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-trash"></i>',
                            ['delete-attachment', 'id' => $attachment->id],
                            [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete this file?'),
                                    'method' => 'post',
                                ],
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted"><?= Yii::t('app', 'No attachments yet.') ?></p>
        <?php endif; ?>

        <!-- Upload Form -->
        <?= Html::beginForm(['upload-attachment', 'id' => $model->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'mt-3']) ?>
            <div class="form-group">
                <div class="input-group">
                    <div class="custom-file">
                        <?= Html::fileInput('attachments[]', null, [
                            'multiple' => true,
                            'class' => 'custom-file-input',
                            'id' => 'attachmentFiles',
                        ]) ?>
                        <label class="custom-file-label" for="attachmentFiles"><?= Yii::t('app', 'Choose files...') ?></label>
                    </div>
                    <div class="input-group-append">
                        <?= Html::submitButton('<i class="fas fa-upload"></i> ' . Yii::t('app', 'Upload'), ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
        <?= Html::endForm() ?>
    </div>

</div>

<?php
$this->registerJs("
// Update file input label with selected file names
$('#attachmentFiles').on('change', function() {
    var files = this.files;
    var label = $(this).next('.custom-file-label');
    if (files.length > 1) {
        label.text(files.length + ' " . Yii::t('app', 'files selected') . "');
    } else if (files.length === 1) {
        label.text(files[0].name);
    }
});
", \yii\web\View::POS_READY);
?>
