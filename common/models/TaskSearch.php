<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * TaskSearch model
 *
 * This model handles filtering and searching of Task records.
 * It is mainly used for grid and list filters.
 */
class TaskSearch extends Task
{
    /**
     * Virtual attribute for assignee name.
     */
    public $assigned_to_name;

    /**
     * Validation rules for search attributes.
     */
    public function rules()
    {
        return [
            // Integer filters
            [
                [
                    'id',
                    'assigned_to',
                    'assignee_id',
                    'sort_order',
                    'created_by',
                    'created_at',
                    'updated_at',
                    'team_id',
                    'board_id'
                ],
                'integer'
            ],

            // Safe (searchable) fields
            [['title', 'description', 'status', 'priority', 'due_date'], 'safe'],
        ];
    }

    /**
     * Scenarios are not used in search model.
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Returns dynamic status list for filter dropdown.
     */
    public static function getStatusList()
    {
        return ArrayHelper::map(
            Task::find()
                ->select('status')
                ->distinct()
                ->where(['not', ['status' => null]])
                ->orderBy('status')
                ->asArray()
                ->all(),
            'status',
            'status'
        );
    }

    /**
     * Creates data provider instance with search query applied.
     */
    public function search($params, $formName = null)
    {
        $query = Task::find();

        // Pagination size
        $pageSize = $params['per-page'] ?? 10;

        // Base filters
        $query->andFilterWhere(['team_id' => $this->team_id]);
        $query->andFilterWhere(['board_id' => $this->board_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        // Load parameters
        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Exact match filters
        $query->andFilterWhere([
            'id' => $this->id,
            'assigned_to' => $this->assigned_to,
            'assignee_id' => $this->assignee_id,
            'sort_order' => $this->sort_order,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'team_id' => $this->team_id,
        ]);

        // Text and dropdown filters
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'priority', $this->priority]);

        // Due date filter (date only)
        if (!empty($this->due_date)) {
            $query->andWhere(['DATE(due_date)' => $this->due_date]);
        }

        return $dataProvider;
    }
}
