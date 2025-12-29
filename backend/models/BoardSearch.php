<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Board;

/**
 * BoardSearch model
 *
 * This model handles searching and filtering of Board records
 * in the backend panel. It supports filtering by team name,
 * creator username, and creation date.
 */
class BoardSearch extends Board
{
    /**
     * Virtual attributes for related table filters.
     */
    public $team_name;
    public $created_by_username;

    /**
     * Validation rules for search attributes.
     */
    public function rules()
    {
        return [
            // Integer filters
            [['id', 'team_id'], 'integer'],

            // Safe (searchable) fields
            [
                [
                    'title',
                    'description',
                    'team_name',
                    'created_by_username',
                    'created_at',
                ],
                'safe'
            ],
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
        // Base query with alias
        $query = Board::find()->alias('b');

        /**
         * Join related tables with aliases:
         * - team table as "t"
         * - user table (creator) as "u"
         */
        $query->joinWith([
            'team t',
            'createdBy u',
        ]);

        // Prevent duplicate rows caused by joins
        $query->groupBy('b.id');

        // Data provider configuration
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        /**
         * Custom sorting for virtual attributes.
         */
        $dataProvider->sort->attributes['team_name'] = [
            'asc'  => ['t.name' => SORT_ASC],
            'desc' => ['t.name' => SORT_DESC],
        ];

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
           DATE FILTER
           Filters by date only (ignores time)
        =============================== */

        if (!empty($this->created_at)) {
            $query->andWhere([
                'DATE(FROM_UNIXTIME(b.created_at))' => $this->created_at,
            ]);
        }

        return $dataProvider;
    }
}
