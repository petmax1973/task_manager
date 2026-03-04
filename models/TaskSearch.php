<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * TaskSearch represents the model behind the search form of `app\models\Task`.
 */
class TaskSearch extends Task
{
    public $statusFilter;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description', 'assigned_to', 'status'], 'safe'],
            [['statusFilter'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Task::find()->with('attachments');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'status' => [
                        'asc' => new Expression("FIELD(status, 'to_release', 'in_progress', 'in_review', 'suspended', 'completed')"),
                        'desc' => new Expression("FIELD(status, 'completed', 'suspended', 'in_review', 'in_progress', 'to_release')"),
                    ],
                    'priority',
                    'title',
                    'created_at',
                    'updated_at',
                    'assigned_to',
                ],
                'defaultOrder' => [
                    'status' => SORT_ASC, 
                    'priority' => SORT_DESC, 
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filter by ID
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        // Search by title (LIKE)
        $query->andFilterWhere(['like', 'title', $this->title]);

        // Search by description (LIKE)
        $query->andFilterWhere(['like', 'description', $this->description]);

        // Search by assigned_to
        $query->andFilterWhere(['like', 'assigned_to', $this->assigned_to]);

        // Filter by status (single or multiple)
        if (!empty($this->statusFilter) && is_array($this->statusFilter)) {
            $query->andFilterWhere(['in', 'status', $this->statusFilter]);
        } elseif (!empty($this->status)) {
            $query->andFilterWhere(['status' => $this->status]);
        }

        return $dataProvider;
    }
}
