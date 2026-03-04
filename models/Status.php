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
 * @property string|null $name_it
 * @property string|null $name_en
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
            [['slug', 'color'], 'required'],
            [['slug'], 'string', 'max' => 20],
            [['name_it', 'name_en'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 20],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
            [['slug'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9_]+$/', 'message' => Yii::t('app', 'Slug can only contain lowercase letters, numbers and underscores.')],
            [['name_it', 'name_en'], 'validateAtLeastOneName'],
        ];
    }

    /**
     * At least one of name_it or name_en must be provided.
     */
    public function validateAtLeastOneName($attribute, $params)
    {
        if (empty($this->name_it) && empty($this->name_en)) {
            $this->addError($attribute, Yii::t('app', 'At least one name (IT or EN) is required.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug' => Yii::t('app', 'Slug'),
            'name_it' => Yii::t('app', 'Name (IT)'),
            'name_en' => Yii::t('app', 'Name (EN)'),
            'color' => Yii::t('app', 'Color'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Returns the localized name based on the current app language.
     * Falls back to the other language if the current one is empty.
     * @return string
     */
    public function getLocalizedName()
    {
        if (strpos(Yii::$app->language, 'it') === 0) {
            return !empty($this->name_it) ? $this->name_it : $this->name_en;
        }
        return !empty($this->name_en) ? $this->name_en : $this->name_it;
    }

    /**
     * Returns list of statuses as slug => localizedName map.
     * @return array
     */
    public static function getList()
    {
        $models = self::find()->orderBy('sort_order')->all();
        $list = [];
        foreach ($models as $model) {
            $list[$model->slug] = $model->getLocalizedName();
        }
        return $list;
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
