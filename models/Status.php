<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "status".
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $color
 * @property int $sort_order
 * @property int $created_at
 */
class Status extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'status';
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
            [['slug', 'name', 'color'], 'required'],
            [['slug'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 20],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
            [['slug'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9_]+$/', 'message' => Yii::t('app', 'Slug can only contain lowercase letters, numbers and underscores.')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug' => Yii::t('app', 'Slug'),
            'name' => Yii::t('app', 'Name'),
            'color' => Yii::t('app', 'Color'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Returns list of statuses as slug => name map.
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(
            self::find()->orderBy('sort_order')->all(),
            'slug',
            'name'
        );
    }

    /**
     * Returns color map as slug => color.
     * @return array
     */
    public static function getColorMap()
    {
        return ArrayHelper::map(
            self::find()->orderBy('sort_order')->all(),
            'slug',
            'color'
        );
    }

    /**
     * Returns ordered slugs for FIELD() sorting.
     * @return array
     */
    public static function getOrderedSlugs()
    {
        return self::find()
            ->select('slug')
            ->orderBy('sort_order')
            ->column();
    }
}
