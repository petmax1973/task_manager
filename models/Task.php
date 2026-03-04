<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\models\Assignee;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $assigned_to
 * @property string $status
 * @property int $priority
 * @property string|null $gitlab_issue
 * @property int $created_at
 * @property int $updated_at
 */
class Task extends ActiveRecord
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TO_RELEASE = 'to_release';
    const STATUS_COMPLETED = 'completed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['description'], 'string'],
            [['title', 'assigned_to'], 'string', 'max' => 255],
            [['gitlab_issue'], 'string', 'max' => 500],
            [['gitlab_issue'], 'url'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [
                self::STATUS_IN_PROGRESS,
                self::STATUS_IN_REVIEW,
                self::STATUS_SUSPENDED,
                self::STATUS_TO_RELEASE,
                self::STATUS_COMPLETED
            ]],
            [['status'], 'default', 'value' => self::STATUS_IN_PROGRESS],
            [['priority'], 'integer', 'min' => 1, 'max' => 5],
            [['priority'], 'default', 'value' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'assigned_to' => Yii::t('app', 'Assigned To'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'priority' => Yii::t('app', 'Priority'),
            'gitlab_issue' => Yii::t('app', 'GitLab Issue'),
        ];
    }

    /**
     * Get list of available statuses
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_IN_PROGRESS => Yii::t('app', 'In Progress'),
            self::STATUS_IN_REVIEW => Yii::t('app', 'In Review'),
            self::STATUS_SUSPENDED => Yii::t('app', 'Suspended'),
            self::STATUS_TO_RELEASE => Yii::t('app', 'To Release'),
            self::STATUS_COMPLETED => Yii::t('app', 'Completed'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!empty($this->assigned_to)) {
            Assignee::ensureExists($this->assigned_to);
        }
    }

    /**
     * Get status label
     * @return string
     */
    public function getStatusLabel()
    {
        $statuses = self::getStatusList();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : $this->status;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(TaskAttachment::class, ['task_id' => 'id']);
    }
}
