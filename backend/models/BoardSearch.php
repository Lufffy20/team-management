<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Board;

class BoardSearch extends Board
{
    public $team_name;
    public $created_by_username;

    public function rules()
    {
        return [
            [['id', 'team_id'], 'integer'],
            [
                [
                    'title',
                    'description',
                    'team_name',
                    'created_by_username',
                    'created_at'
                ],
                'safe'
            ],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = Board::find()->alias('b');

        // ✅ Correct joins with aliases
        $query->joinWith([
            'team t',
            'createdBy u'
        ]);

        // 🔥 IMPORTANT: avoid duplicate rows
        $query->groupBy('b.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // ✅ Sorting
        $dataProvider->sort->attributes['team_name'] = [
            'asc' => ['t.name' => SORT_ASC],
            'desc' => ['t.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['created_by_username'] = [
            'asc' => ['u.username' => SORT_ASC],
            'desc' => ['u.username' => SORT_DESC],
        ];

        // Load filters
        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        /* ===============================
           EXACT FILTERS
        =============================== */

        if ($this->id !== null && $this->id !== '') {
            $query->andWhere(['b.id' => $this->id]);
        }

        if ($this->team_id !== null && $this->team_id !== '') {
            $query->andWhere(['b.team_id' => $this->team_id]);
        }

        /* ===============================
           TEXT SEARCH FILTERS
        =============================== */

        $query->andFilterWhere(['like', 'b.title', $this->title]);
        $query->andFilterWhere(['like', 'b.description', $this->description]);
        $query->andFilterWhere(['like', 't.name', $this->team_name]);
        $query->andFilterWhere(['like', 'u.username', $this->created_by_username]);

        /* ===============================
           DATE FILTER (CRITICAL FIX)
        =============================== */

        if (!empty($this->created_at)) {
            $query->andWhere([
                'DATE(FROM_UNIXTIME(b.created_at))' => $this->created_at
            ]);
        }

        return $dataProvider;
    }
}
