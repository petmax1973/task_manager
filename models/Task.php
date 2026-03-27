<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\models\Assignee;
use app\models\Project;
use app\models\Status;

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
    const STATUS_ACTIVE = 'active';
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
            [['status'], 'in', 'range' => array_keys(Status::getList())],
            [['status'], 'default', 'value' => self::STATUS_IN_PROGRESS],
            [['priority'], 'integer', 'min' => 1, 'max' => 5],
            [['priority'], 'default', 'value' => 1],
            [['project'], 'string', 'max' => 50],
            [['project'], 'in', 'range' => array_keys(Project::getList())],
            [['related_tasks'], 'string', 'max' => 255],
            [['related_tasks'], 'validateRelatedTasks'],
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
            'project' => Yii::t('app', 'Project'),
            'related_tasks' => Yii::t('app', 'Related Tasks'),
        ];
    }

    /**
     * Get list of available statuses
     * @return array
     */
    public static function getStatusList()
    {
        return Status::getList();
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
     * Get list of available projects
     * @return array
     */
    public static function getProjectList()
    {
        return Project::getList();
    }

    /**
     * Get project label
     * @return string
     */
    public function getProjectLabel()
    {
        $projects = self::getProjectList();
        return isset($projects[$this->project]) ? $projects[$this->project] : '';
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
    /**
     * Validates that related_tasks contains valid, existing task IDs
     */
    public function validateRelatedTasks($attribute)
    {
        if (empty($this->$attribute)) {
            return;
        }
        $ids = array_map('trim', explode(',', $this->$attribute));
        foreach ($ids as $id) {
            if (!ctype_digit($id)) {
                $this->addError($attribute, Yii::t('app', 'Related Tasks must contain only numeric IDs separated by commas.'));
                return;
            }
            if ((int)$id === $this->id) {
                $this->addError($attribute, Yii::t('app', 'A task cannot reference itself.'));
                return;
            }
        }
        $ids = array_map('intval', $ids);
        $existingCount = static::find()->where(['id' => $ids])->count();
        if ($existingCount != count($ids)) {
            $this->addError($attribute, Yii::t('app', 'One or more referenced tasks do not exist.'));
        }
    }

    /**
     * Returns related task models
     * @return Task[]
     */
    public function getRelatedTaskModels()
    {
        if (empty($this->related_tasks)) {
            return [];
        }
        $ids = array_map('intval', array_map('trim', explode(',', $this->related_tasks)));
        return static::find()->where(['id' => $ids])->all();
    }

    public function getAttachments()
    {
        return $this->hasMany(TaskAttachment::class, ['task_id' => 'id']);
    }

    public function getDescriptionTabs()
    {
        return $this->hasMany(TaskDescriptionTab::class, ['task_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Parses the related_tasks string into an array of integer IDs.
     * @return int[]
     */
    public function parseRelatedIds()
    {
        if (empty($this->related_tasks)) {
            return [];
        }
        return array_map('intval', array_filter(array_map('trim', explode(',', $this->related_tasks)), 'strlen'));
    }

    /**
     * Adds a related task ID to this task's related_tasks field (avoids duplicates).
     * @param int $id
     */
    public function addRelatedTaskId($id)
    {
        $ids = $this->parseRelatedIds();
        if (!in_array((int)$id, $ids)) {
            $ids[] = (int)$id;
            $this->related_tasks = implode(', ', $ids);
            $this->save(false);
        }
    }

    /**
     * Removes a related task ID from this task's related_tasks field.
     * @param int $id
     */
    public function removeRelatedTaskId($id)
    {
        $ids = $this->parseRelatedIds();
        $ids = array_filter($ids, function ($v) use ($id) {
            return $v !== (int)$id;
        });
        $this->related_tasks = empty($ids) ? null : implode(', ', $ids);
        $this->save(false);
    }

    /**
     * Syncs bidirectional relationships after save.
     * Adds this task's ID to newly related tasks and removes it from unlinked tasks.
     * @param int[] $oldIds IDs before the update
     */
    public function syncRelatedTasks($oldIds = [])
    {
        $newIds = $this->parseRelatedIds();

        $added = array_diff($newIds, $oldIds);
        $removed = array_diff($oldIds, $newIds);

        foreach ($added as $relatedId) {
            $relatedTask = static::findOne($relatedId);
            if ($relatedTask) {
                $relatedTask->addRelatedTaskId($this->id);
            }
        }

        foreach ($removed as $relatedId) {
            $relatedTask = static::findOne($relatedId);
            if ($relatedTask) {
                $relatedTask->removeRelatedTaskId($this->id);
            }
        }
    }

    /**
     * Removes this task's ID from all related tasks (used before delete).
     */
    public function unlinkAllRelatedTasks()
    {
        $ids = $this->parseRelatedIds();
        foreach ($ids as $relatedId) {
            $relatedTask = static::findOne($relatedId);
            if ($relatedTask) {
                $relatedTask->removeRelatedTaskId($this->id);
            }
        }
    }
}
