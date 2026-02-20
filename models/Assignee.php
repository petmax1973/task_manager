<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "assignee".
 *
 * @property int $id
 * @property string $name
 * @property int $created_at
 */
class Assignee extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assignee';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'Name'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Returns list of assignees as name => name map.
     * Uses name as both key and value because task.assigned_to stores the name string.
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(
            self::find()->orderBy('name')->all(),
            'name',
            'name'
        );
    }

    /**
     * Ensures an assignee with the given name exists in the table.
     * Creates a new record if the name is not found.
     * @param string $name
     */
    public static function ensureExists($name)
    {
        $name = trim($name);
        if (empty($name)) {
            return;
        }

        if (!self::find()->where(['name' => $name])->exists()) {
            $model = new self();
            $model->name = $name;
            $model->save();
        }
    }
}
