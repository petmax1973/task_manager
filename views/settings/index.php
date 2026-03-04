<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $projects app\models\Project[] */
/* @var $statuses app\models\Status[] */
/* @var $assignees app\models\Assignee[] */

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settings-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <!-- Projects -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><strong><?= Yii::t('app', 'Projects') ?></strong></div>
                <div class="card-body">
                    <table class="table table-sm" id="projects-table">
                        <thead><tr><th><?= Yii::t('app', 'Slug') ?></th><th><?= Yii::t('app', 'Name') ?></th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr data-id="<?= $project->id ?>">
                                <td><?= Html::encode($project->slug) ?></td>
                                <td><?= Html::encode($project->name) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-project" data-id="<?= $project->id ?>" title="<?= Yii::t('app', 'Delete') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr>
                    <form id="add-project-form" class="form-inline">
                        <input type="text" class="form-control form-control-sm mr-1 mb-1" name="slug" placeholder="<?= Yii::t('app', 'Slug') ?>" required style="width: 100px;">
                        <input type="text" class="form-control form-control-sm mr-1 mb-1" name="name" placeholder="<?= Yii::t('app', 'Name') ?>" required style="width: 120px;">
                        <button type="submit" class="btn btn-sm btn-success mb-1"><i class="fas fa-plus"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assignees -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header"><strong><?= Yii::t('app', 'Assignees') ?></strong></div>
                <div class="card-body">
                    <table class="table table-sm" id="assignees-table">
                        <thead><tr><th><?= Yii::t('app', 'Name') ?></th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($assignees as $assignee): ?>
                            <tr data-id="<?= $assignee->id ?>">
                                <td><?= Html::encode($assignee->name) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-assignee" data-id="<?= $assignee->id ?>" title="<?= Yii::t('app', 'Delete') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr>
                    <form id="add-assignee-form" class="form-inline">
                        <input type="text" class="form-control form-control-sm mr-1 mb-1" name="name" placeholder="<?= Yii::t('app', 'Name') ?>" required style="width: 160px;">
                        <button type="submit" class="btn btn-sm btn-success mb-1"><i class="fas fa-plus"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statuses -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header"><strong><?= Yii::t('app', 'Statuses') ?></strong></div>
                <div class="card-body">
                    <table class="table table-sm" id="statuses-table">
                        <thead><tr>
                            <th><?= Yii::t('app', 'Slug') ?></th>
                            <th><?= Yii::t('app', 'Name') ?></th>
                            <th><?= Yii::t('app', 'Color') ?></th>
                            <th><?= Yii::t('app', 'Sort Order') ?></th>
                            <th></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($statuses as $status): ?>
                            <tr data-id="<?= $status->id ?>">
                                <td><?= Html::encode($status->slug) ?></td>
                                <td><span style="color: <?= Html::encode($status->color) ?>; font-weight: bold;"><?= Html::encode($status->name) ?></span></td>
                                <td><span class="color-swatch" style="display:inline-block;width:20px;height:20px;background:<?= Html::encode($status->color) ?>;border:1px solid #ccc;vertical-align:middle;"></span> <?= Html::encode($status->color) ?></td>
                                <td><?= $status->sort_order ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-status" data-id="<?= $status->id ?>" title="<?= Yii::t('app', 'Delete') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr>
                    <form id="add-status-form" class="form-inline">
                        <input type="text" class="form-control form-control-sm mr-1 mb-1" name="slug" placeholder="<?= Yii::t('app', 'Slug') ?>" required style="width: 90px;">
                        <input type="text" class="form-control form-control-sm mr-1 mb-1" name="name" placeholder="<?= Yii::t('app', 'Name') ?>" required style="width: 100px;">
                        <input type="color" class="form-control form-control-sm mr-1 mb-1" name="color" value="#000000" title="<?= Yii::t('app', 'Color') ?>" style="width: 40px; padding: 2px;">
                        <input type="number" class="form-control form-control-sm mr-1 mb-1" name="sort_order" placeholder="<?= Yii::t('app', 'Sort Order') ?>" value="0" style="width: 60px;">
                        <button type="submit" class="btn btn-sm btn-success mb-1"><i class="fas fa-plus"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$addProjectUrl = Url::to(['settings/add-project']);
$deleteProjectUrl = Url::to(['settings/delete-project']);
$addStatusUrl = Url::to(['settings/add-status']);
$deleteStatusUrl = Url::to(['settings/delete-status']);
$addAssigneeUrl = Url::to(['settings/add-assignee']);
$deleteAssigneeUrl = Url::to(['settings/delete-assignee']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$confirmDeleteMsg = Yii::t('app', 'Are you sure you want to delete this item?');
$errorPrefix = Yii::t('app', 'Error');

$this->registerJs("
// --- Projects ---
$('#add-project-form').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
        url: '{$addProjectUrl}',
        type: 'POST',
        data: form.serialize() + '&{$csrfParam}={$csrfToken}',
        success: function(r) {
            if (r.success) {
                var row = '<tr data-id=\"' + r.id + '\"><td>' + $('<span>').text(r.slug).html() + '</td><td>' + $('<span>').text(r.name).html() + '</td><td><button class=\"btn btn-sm btn-danger delete-project\" data-id=\"' + r.id + '\"><i class=\"fas fa-trash\"></i></button></td></tr>';
                $('#projects-table tbody').append(row);
                form[0].reset();
            } else {
                alert('{$errorPrefix}: ' + Object.values(r.errors).join(', '));
            }
        }
    });
});

$(document).on('click', '.delete-project', function() {
    if (!confirm('{$confirmDeleteMsg}')) return;
    var btn = $(this);
    $.ajax({
        url: '{$deleteProjectUrl}?id=' + btn.data('id'),
        type: 'POST',
        data: '{$csrfParam}={$csrfToken}',
        success: function(r) {
            if (r.success) btn.closest('tr').remove();
            else alert(r.message);
        }
    });
});

// --- Statuses ---
$('#add-status-form').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
        url: '{$addStatusUrl}',
        type: 'POST',
        data: form.serialize() + '&{$csrfParam}={$csrfToken}',
        success: function(r) {
            if (r.success) {
                var nameHtml = '<span style=\"color:' + r.color + ';font-weight:bold;\">' + $('<span>').text(r.name).html() + '</span>';
                var colorHtml = '<span class=\"color-swatch\" style=\"display:inline-block;width:20px;height:20px;background:' + r.color + ';border:1px solid #ccc;vertical-align:middle;\"></span> ' + $('<span>').text(r.color).html();
                var row = '<tr data-id=\"' + r.id + '\"><td>' + $('<span>').text(r.slug).html() + '</td><td>' + nameHtml + '</td><td>' + colorHtml + '</td><td>' + r.sort_order + '</td><td><button class=\"btn btn-sm btn-danger delete-status\" data-id=\"' + r.id + '\"><i class=\"fas fa-trash\"></i></button></td></tr>';
                $('#statuses-table tbody').append(row);
                form[0].reset();
            } else {
                alert('{$errorPrefix}: ' + Object.values(r.errors).join(', '));
            }
        }
    });
});

$(document).on('click', '.delete-status', function() {
    if (!confirm('{$confirmDeleteMsg}')) return;
    var btn = $(this);
    $.ajax({
        url: '{$deleteStatusUrl}?id=' + btn.data('id'),
        type: 'POST',
        data: '{$csrfParam}={$csrfToken}',
        success: function(r) {
            if (r.success) btn.closest('tr').remove();
            else alert(r.message);
        }
    });
});

// --- Assignees ---
$('#add-assignee-form').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
        url: '{$addAssigneeUrl}',
        type: 'POST',
        data: form.serialize() + '&{$csrfParam}={$csrfToken}',
        success: function(r) {
            if (r.success) {
                var row = '<tr data-id=\"' + r.id + '\"><td>' + $('<span>').text(r.name).html() + '</td><td><button class=\"btn btn-sm btn-danger delete-assignee\" data-id=\"' + r.id + '\"><i class=\"fas fa-trash\"></i></button></td></tr>';
                $('#assignees-table tbody').append(row);
                form[0].reset();
            } else {
                alert('{$errorPrefix}: ' + Object.values(r.errors).join(', '));
            }
        }
    });
});

$(document).on('click', '.delete-assignee', function() {
    if (!confirm('{$confirmDeleteMsg}')) return;
    var btn = $(this);
    $.ajax({
        url: '{$deleteAssigneeUrl}?id=' + btn.data('id'),
        type: 'POST',
        data: '{$csrfParam}={$csrfToken}',
        success: function(r) {
            if (r.success) btn.closest('tr').remove();
            else alert(r.message);
        }
    });
});
", \yii\web\View::POS_READY);
?>
