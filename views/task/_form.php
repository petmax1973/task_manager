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

    <?= $form->field($model, 'description')->textarea(['rows' => 26]) ?>

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
