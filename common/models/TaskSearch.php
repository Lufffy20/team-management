<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use common\models\Task;

/**
 * TaskSearch represents the model behind the search form of `common\models\Task`.
 */
class TaskSearch extends Task
{

    public $assigned_to_name;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'assigned_to', 'assignee_id', 'sort_order', 'created_by', 'created_at', 'updated_at', 'team_id', 'board_id'], 'integer'],
            [['title', 'description', 'status', 'priority', 'due_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Status list for filter dropdown (dynamic)
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
     * Creates data provider instance with search query applied
     */
    public function search($params, $formName = null)
    {
        $query = Task::find();

        $pageSize = $params['per-page'] ?? 10;
        // base filters
        $query->andFilterWhere(['team_id' => $this->team_id]);
        $query->andFilterWhere(['board_id' => $this->board_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
            'pageSize' => $pageSize,
        ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // exact match filters
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

        // text filters
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
              ->andFilterWhere(['status' => $this->status])   // exact match for dropdown
            ->andFilterWhere(['like', 'priority', $this->priority]);

        // Due date filter (date only)
        if (!empty($this->due_date)) {
            $query->andWhere(['DATE(due_date)' => $this->due_date]);
        }

        return $dataProvider;
    }
}
