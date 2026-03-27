<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Task;
use app\models\Assignee;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="task-form">

    <?php $form = ActiveForm::begin(['id' => 'task-form']); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <!-- Description Tabs -->
    <div class="description-tabs-section">
        <label class="control-label"><?= Yii::t('app', 'Description Tabs') ?></label>
        <div id="description-tabs-container">
            <?php
            $existingTabs = $model->isNewRecord ? [] : $model->descriptionTabs;
            if (empty($existingTabs)) {
                // One empty tab by default for new tasks
                $existingTabs = [null];
            }
            foreach ($existingTabs as $index => $tab):
            ?>
            <div class="description-tab-item card mb-3" data-index="<?= $index ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="tab-label"><?= Yii::t('app', 'Tab {n}', ['n' => $index + 1]) ?></span>
                    <button type="button" class="btn btn-sm btn-danger remove-tab-btn" title="<?= Yii::t('app', 'Remove Tab') ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?= Yii::t('app', 'Tab Title') ?></label>
                        <input type="text" class="form-control"
                               name="TaskDescriptionTab[<?= $index ?>][title]"
                               value="<?= Html::encode($tab ? $tab->title : '') ?>"
                               placeholder="<?= Yii::t('app', 'Tab Title') ?>">
                    </div>
                    <div class="form-group">
                        <label><?= Yii::t('app', 'Content') ?></label>
                        <textarea class="form-control markdown-tab-editor"
                                  name="TaskDescriptionTab[<?= $index ?>][content]"
                                  rows="15"><?= Html::encode($tab ? $tab->content : '') ?></textarea>
                    </div>
                    <?php if ($tab && $tab->id): ?>
                    <input type="hidden" name="TaskDescriptionTab[<?= $index ?>][id]" value="<?= $tab->id ?>">
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-tab-btn" class="btn btn-outline-primary mb-3">
            <i class="fas fa-plus"></i> <?= Yii::t('app', 'Add Tab') ?>
        </button>
    </div>

    <?php
    $tabTitleLabel = Yii::t('app', 'Tab Title');
    $contentLabel = Yii::t('app', 'Content');
    $removeTabTitle = Yii::t('app', 'Remove Tab');
    $tabLabel = Yii::t('app', 'Tab {n}');

    $this->registerJs("
        // Initialize EasyMDE on existing textareas
        var mdeInstances = {};
        function initEasyMDE(textarea) {
            if (typeof EasyMDE === 'undefined') return null;
            var instance = new EasyMDE({
                element: textarea,
                spellChecker: false,
                minHeight: '200px',
                status: ['lines', 'words'],
                toolbar: [
                    'bold', 'italic', 'heading', '|',
                    'unordered-list', 'ordered-list', 'checklist', '|',
                    'code', 'quote', 'link', 'image', 'table', '|',
                    'horizontal-rule', '|',
                    'preview', 'side-by-side', 'fullscreen', '|',
                    'guide'
                ],
            });
            return instance;
        }

        // Init existing editors
        $('#description-tabs-container .markdown-tab-editor').each(function() {
            initEasyMDE(this);
        });

        // Add new tab
        $('#add-tab-btn').on('click', function() {
            var container = $('#description-tabs-container');
            var count = container.find('.description-tab-item').length;
            var label = '$tabLabel'.replace('{n}', count + 1);
            var html = '<div class=\"description-tab-item card mb-3\" data-index=\"' + count + '\">' +
                '<div class=\"card-header d-flex justify-content-between align-items-center\">' +
                    '<span class=\"tab-label\">' + label + '</span>' +
                    '<button type=\"button\" class=\"btn btn-sm btn-danger remove-tab-btn\" title=\"$removeTabTitle\">' +
                        '<i class=\"fas fa-times\"></i>' +
                    '</button>' +
                '</div>' +
                '<div class=\"card-body\">' +
                    '<div class=\"form-group\">' +
                        '<label>$tabTitleLabel</label>' +
                        '<input type=\"text\" class=\"form-control\" name=\"TaskDescriptionTab[' + count + '][title]\" placeholder=\"$tabTitleLabel\">' +
                    '</div>' +
                    '<div class=\"form-group\">' +
                        '<label>$contentLabel</label>' +
                        '<textarea class=\"form-control markdown-tab-editor\" name=\"TaskDescriptionTab[' + count + '][content]\" rows=\"15\"></textarea>' +
                    '</div>' +
                '</div>' +
            '</div>';
            container.append(html);
            var newTextarea = container.find('.description-tab-item:last .markdown-tab-editor')[0];
            initEasyMDE(newTextarea);
            updateRemoveButtons();
        });

        // Remove tab
        $(document).on('click', '.remove-tab-btn', function() {
            var container = $('#description-tabs-container');
            if (container.find('.description-tab-item').length <= 1) return;

            var item = $(this).closest('.description-tab-item');
            // Destroy EasyMDE if present
            var textarea = item.find('.markdown-tab-editor')[0];
            if (textarea && textarea.EasyMDE) {
                textarea.EasyMDE.toTextArea();
            }
            item.remove();
            reindexTabs();
            updateRemoveButtons();
        });

        function reindexTabs() {
            var tabLabelTpl = '$tabLabel';
            $('#description-tabs-container .description-tab-item').each(function(i) {
                $(this).attr('data-index', i);
                $(this).find('.tab-label').text(tabLabelTpl.replace('{n}', i + 1));
                $(this).find('input[name*=\"[title]\"]').attr('name', 'TaskDescriptionTab[' + i + '][title]');
                $(this).find('textarea[name*=\"[content]\"]').attr('name', 'TaskDescriptionTab[' + i + '][content]');
                $(this).find('input[name*=\"[id]\"]').attr('name', 'TaskDescriptionTab[' + i + '][id]');
            });
        }

        function updateRemoveButtons() {
            var items = $('#description-tabs-container .description-tab-item');
            if (items.length <= 1) {
                items.find('.remove-tab-btn').hide();
            } else {
                items.find('.remove-tab-btn').show();
            }
        }

        updateRemoveButtons();
    ", \yii\web\View::POS_READY);
    ?>

    <?= $form->field($model, 'assigned_to')->widget(Select2::class, [
        'data' => Assignee::getList(),
        'options' => [
            'placeholder' => Yii::t('app', 'Select or type assignee...'),
        ],
        'pluginOptions' => [
            'tags' => true,
            'allowClear' => true,
            'tokenSeparators' => [','],
        ],
    ]) ?>

    <?= $form->field($model, 'project')->dropDownList(Task::getProjectList(), ['prompt' => Yii::t('app', 'Select Project...')]) ?>

    <?= $form->field($model, 'related_tasks')->textInput([
        'maxlength' => true,
        'placeholder' => Yii::t('app', 'e.g. 12, 8, 15'),
    ])->hint(Yii::t('app', 'Enter task IDs separated by commas')) ?>

    <?= $form->field($model, 'gitlab_issue')->textInput([
        'maxlength' => true,
        'placeholder' => 'https://gitlab.com/project/Admin/-/issues/1493'
    ]) ?>

    <?= $form->field($model, 'status')->dropDownList(Task::getStatusList(), ['prompt' => '']) ?>

    <?= $form->field($model, 'priority')->dropDownList([
        1 => '1 - ' . Yii::t('app', 'Low'),
        2 => '2',
        3 => '3 - ' . Yii::t('app', 'Medium'),
        4 => '4',
        5 => '5 - ' . Yii::t('app', 'High')
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
