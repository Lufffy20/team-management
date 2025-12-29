<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch model
 *
 * This model is used to search and filter User records.
 * It is mainly used in GridView and listing pages.
 */
class UserSearch extends User
{
    /**
     * Validation rules for search fields.
     */
    public function rules()
    {
        return [
            // Integer filters
            [['id', 'role', 'status', 'created_at', 'updated_at'], 'integer'],

            // Safe text filters
            [
                [
                    'first_name',
                    'last_name',
                    'avatar',
                    'username',
                    'auth_key',
                    'password_hash',
                    'password_reset_token',
                    'email',
                    'pending_email',
                    'verification_token'
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
        // Bypass parent scenarios
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with applied search filters.
     *
     * @param array $params     Request parameters
     * @param string|null $formName Optional form name
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        // Base query
        $query = User::find();

        // Data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Load search parameters
        $this->load($params, $formName);

        // If validation fails, return unfiltered data
        if (!$this->validate()) {
            return $dataProvider;
        }

        /* ================= EXACT MATCH FILTERS ================= */

        $query->andFilterWhere([
            'id' => $this->id,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        /* ================= TEXT SEARCH FILTERS ================= */

        $query->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'avatar', $this->avatar])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'pending_email', $this->pending_email])
            ->andFilterWhere(['like', 'verification_token', $this->verification_token]);

        return $dataProvider;
    }
}
