<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Team;

class TeamSearch extends Team
{
    // 🔥 Virtual attributes
    public $created_by_username;
    public $created_at_date;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'description', 'created_by_username', 'created_at_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

   public function search($params, $formName = null)
{
    // 🔥 Explicit table alias for main table
    $query = Team::find()->alias('t');

    // 🔥 Explicit alias for creator relation
    $query->joinWith(['creator u']);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
    ]);

    // 🔥 Sorting (alias fixed)
    $dataProvider->sort->attributes['created_by_username'] = [
        'asc'  => ['u.username' => SORT_ASC],
        'desc' => ['u.username' => SORT_DESC],
    ];

    // 🔥 Load params correctly
    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }

    // 🔹 Exact filter
    $query->andFilterWhere([
        't.id' => $this->id,
    ]);

    // 🔹 Text filters
    $query->andFilterWhere(['like', 't.name', $this->name])
          ->andFilterWhere(['like', 't.description', $this->description])
          ->andFilterWhere(['like', 'u.username', $this->created_by_username]);

    // 🔹 Date filter (ambiguity FIXED)
    if (!empty($this->created_at_date)) {
        $start = strtotime($this->created_at_date . ' 00:00:00');
        $end   = strtotime($this->created_at_date . ' 23:59:59');

        $query->andFilterWhere(['between', 't.created_at', $start, $end]);
    }

    return $dataProvider;
}
}
