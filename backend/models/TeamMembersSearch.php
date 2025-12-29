<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TeamMembers;

/**
 * TeamMembersSearch represents the model behind the search form of `common\models\TeamMembers`.
 */
class TeamMembersSearch extends TeamMembers
{
    public $team_name;   // ⭐ For searching team by name
    public $username;    // ⭐ For searching user by username

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'team_id', 'user_id'], 'integer'],
            [['role', 'team_name', 'username'], 'safe'],  // ⭐ added safe fields
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
     * Creates data provider instance with search query applied
     */
   public function search($params, $formName = null)
{
    $query = TeamMembers::find();

    // ⭐ JOIN relations
    $query->joinWith(['team', 'user']);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
    ]);

    // ⭐ Sorting
    $dataProvider->sort->attributes['team_name'] = [
        'asc'  => ['team.name' => SORT_ASC],
        'desc' => ['team.name' => SORT_DESC],
    ];

    $dataProvider->sort->attributes['username'] = [
        'asc'  => ['user.username' => SORT_ASC],
        'desc' => ['user.username' => SORT_DESC],
    ];

    $this->load($params, $formName);

    if (!$this->validate()) {
        return $dataProvider;
    }

    // 🔥 ID SEARCH (exact)
    $query->andFilterWhere([
        'team_members.id' => $this->id,
    ]);

    // 🔥 TEXT SEARCH
    $query->andFilterWhere(['like', 'team.name', $this->team_name])
          ->andFilterWhere(['like', 'user.username', $this->username]);

    // 🔥 ROLE FILTER (dropdown → exact match)
    $query->andFilterWhere([
        'team_members.role' => $this->role,
    ]);

    return $dataProvider;
}

}
