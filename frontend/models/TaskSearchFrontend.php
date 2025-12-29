<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Task;

/**
 * TaskSearchFrontend model
 *
 * This model is used on the frontend to search and filter tasks.
 * It extends the Task model and provides filtering logic
 * for lists, dashboards, or task views.
 */
class TaskSearchFrontend extends Task
{
    /**
     * Team ID filter (virtual attribute).
     */
    public $team_id;

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
                    'user_id',
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

            // Safe text/date filters
            [['title', 'description', 'status', 'priority', 'due_date'], 'safe'],
        ];
    }

    /**
     * Scenarios are not required for search model.
     */
    public function scenarios()
    {
        // Bypass parent scenarios
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params Request parameters
     * @param string|null $formName Optional form name
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        // Base query
        $query = Task::find();

        // Data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Load parameters into model
        $this->load($params, $formName);

        // If validation fails, return unfiltered data
        if (!$this->validate()) {
            return $dataProvider;
        }

        /* ================= EXACT MATCH FILTERS ================= */

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date,
            'assignee_id' => $this->assignee_id,
            'sort_order' => $this->sort_order,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'team_id' => $this->team_id,
            'board_id' => $this->board_id,
        ]);

        /* ================= TEXT SEARCH FILTERS ================= */

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'priority', $this->priority]);

        return $dataProvider;
    }
}
