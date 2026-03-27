<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "task_description_tab".
 *
 * @property int $id
 * @property int $task_id
 * @property string $title
 * @property string|null $content
 * @property int $sort_order
 *
 * @property Task $task
 */
class TaskDescriptionTab extends ActiveRecord
{
    public static function tableName()
    {
        return 'task_description_tab';
    }

    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'sort_order'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['title'], 'default', 'value' => ''],
            [['content'], 'string'],
            [['sort_order'], 'default', 'value' => 0],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'task_id' => Yii::t('app', 'Task'),
            'title' => Yii::t('app', 'Tab Title'),
            'content' => Yii::t('app', 'Content'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
