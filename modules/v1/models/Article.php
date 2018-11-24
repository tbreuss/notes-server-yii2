<?php

namespace notes\modules\v1\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;

class Article extends ActiveRecord
{
    public static function tableName()
    {
        return '{{articles}}';
    }

    public function rules()
    {
        return [
            [['title', 'content', 'tags'], 'required'],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        #$fields[] = 'createdByUser';
        #$fields[] = 'modifiedByUser';
        return $fields;
    }

    /**
     * @return array
     */
    public static function findLatestItems()
    {
        $articles = static::find()
            ->select(['id', 'title', 'created'])
            ->limit(5)
            ->orderBy('created DESC')
            ->asArray()
            ->all();
        return $articles;
    }

    /**
     * @return array
     */
    public static function findMostLikedItems()
    {
        $articles = static::find()
            ->select(['id', 'title', 'likes'])
            ->limit(5)
            ->orderBy('likes DESC')
            ->asArray()
            ->all();
        return $articles;
    }

    /**
     * @return array
     */
    public static function findLastModifiedItems()
    {
        $articles = static::find()
            ->select(['id', 'title', 'modified'])
            ->limit(5)
            ->orderBy('modified DESC')
            ->asArray()
            ->all();
        return $articles;
    }

    /**
     * @return array
     */
    public static function findPopularItems()
    {
        $articles = static::find()
            ->select(['id', 'title', 'views'])
            ->limit(5)
            ->orderBy('views DESC')
            ->asArray()
            ->all();
        return $articles;
    }

    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getModifiedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'modified_by']);
    }

    public static function findAllAsProvider($q = '', array $tags = [])
    {
        $query = new Query;

        $query->select('a.id, a.title AS title, GROUP_CONCAT(t.name) AS tags, a.created, a.modified, a.views');
        $query->from('articles a');
        $query->innerJoin('tags t', 'FIND_IN_SET(t.id, a.tag_ids)');
        $query->groupBy('a.id');

        if (!empty($q)) {
            $query->andWhere('(a.title LIKE :q OR a.content LIKE :q)', ['q' => '%' . $q . '%']);
        }

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $query->andWhere('FIND_IN_SET(:tag_id, a.tag_ids)>0', ['tag_id' => $tag]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'title',
                    'changed' => [
                        'asc' => ['a.modified' => SORT_DESC, 'a.title' => SORT_ASC],
                        'desc' => ['a.modified' => SORT_ASC, 'a.title' => SORT_ASC],
                        'default' => SORT_DESC
                    ],
                    'created' => [
                        'asc' => ['a.created' => SORT_DESC, 'a.title' => SORT_ASC],
                        'desc' => ['a.created' => SORT_ASC, 'a.title' => SORT_ASC],
                        'default' => SORT_DESC
                    ],
                    'popular' => [
                        'asc' => ['a.views' => SORT_DESC, 'a.title' => SORT_ASC],
                        'desc' => ['a.views' => SORT_ASC, 'a.title' => SORT_ASC],
                        'default' => SORT_DESC
                    ]
                ],
                'defaultOrder' => [
                    'title' => SORT_ASC
                ]
            ]
        ]);

        // TODO optimize implementation to explode tags
        $models = $dataProvider->getModels();
        foreach ($models as $i => $model) {
            $model['tags'] = explode(',', $model['tags']);
            $models[$i] = $model;
        }
        $dataProvider->setModels($models);

        return $dataProvider;
    }

}