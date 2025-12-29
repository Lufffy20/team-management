<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Team;

/**
 * TeamSearch model
 *
 * This model handles searching and filtering of Team records
 * in the backend panel. It supports filtering by team name,
 * description, creator username, and creation date.
 */
class TeamSearch extends Team
{
    /**
     * Virtual attribute for creator username search.
     */
    public $created_by_username;

    /**
     * Virtual attribute for date-based filtering.
     */
    public $created_at_date;

    /**
     * Validation rules for search attributes.
     */
    public function rules()
    {
        return [
            // Integer filters
            [['id'], 'integer'],

            // Safe (searchable) fields
            [['name', 'description', 'created_by_username', 'created_at_date'], 'safe'],
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
        // Main table alias
        $query = Team::find()->alias('t');
        // Pagination size
        $pageSize = $params['per-page'] ?? 10;

        // Join creator relation with alias
        $query->joinWith(['creator u']);

        // Data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        /**
         * Custom sorting for creator username.
         */
        $dataProvider->sort->attributes['created_by_username'] = [
            'asc'  => ['u.username' => SORT_ASC],
            'desc' => ['u.username' => SORT_DESC],
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

        $query->andFilterWhere([
            't.id' => $this->id,
        ]);

        /* ===============================
           TEXT SEARCH FILTERS
        =============================== */

        $query->andFilterWhere(['like', 't.name', $this->name])
              ->andFilterWhere(['like', 't.description', $this->description])
              ->andFilterWhere(['like', 'u.username', $this->created_by_username]);

        /* ===============================
           DATE FILTER
           Filters records for a single day
        =============================== */

        if (!empty($this->created_at_date)) {
            $start = strtotime($this->created_at_date . ' 00:00:00');
            $end   = strtotime($this->created_at_date . ' 23:59:59');

            $query->andFilterWhere(['between', 't.created_at', $start, $end]);
        }

        return $dataProvider;
    }
}
