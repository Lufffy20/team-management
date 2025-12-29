<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TeamMembers;

/**
 * TeamMembersSearch model
 *
 * This model handles searching and filtering of team members
 * in the backend panel. It supports filtering by team name,
 * username, role, and IDs.
 */
class TeamMembersSearch extends TeamMembers
{
    /**
     * Virtual attribute for team name search.
     */
    public $team_name;

    /**
     * Virtual attribute for username search.
     */
    public $username;

    /**
     * Validation rules for search attributes.
     */
    public function rules()
    {
        return [
            // Integer filters
            [['id', 'team_id', 'user_id'], 'integer'],

            // Safe (searchable) fields
            [['role', 'team_name', 'username'], 'safe'],
        ];
    }

    /**
     * Scenarios are not required for search model.
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array       $params
     * @param string|null $formName
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        // Base query
        $query = TeamMembers::find();

        // Join related tables for searching
        $query->joinWith(['team', 'user']);

        // Data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        /**
         * Custom sorting for virtual attributes.
         */
        $dataProvider->sort->attributes['team_name'] = [
            'asc'  => ['team.name' => SORT_ASC],
            'desc' => ['team.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['username'] = [
            'asc'  => ['user.username' => SORT_ASC],
            'desc' => ['user.username' => SORT_DESC],
        ];

        // Load request parameters
        $this->load($params, $formName);

        // If validation fails, return unfiltered results
        if (!$this->validate()) {
            return $dataProvider;
        }

        /* ===============================
           EXACT MATCH FILTERS
        =============================== */

        // Filter by team member ID
        $query->andFilterWhere([
            'team_members.id' => $this->id,
        ]);

        /* ===============================
           TEXT SEARCH FILTERS
        =============================== */

        // Search by team name and username
        $query->andFilterWhere(['like', 'team.name', $this->team_name])
              ->andFilterWhere(['like', 'user.username', $this->username]);

        /* ===============================
           ROLE FILTER
           Exact match for role dropdown
        =============================== */

        $query->andFilterWhere([
            'team_members.role' => $this->role,
        ]);

        return $dataProvider;
    }
}
