<?php

namespace notes\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Class User
 * @package notes\models
 * @see https://stackoverflow.com/questions/25327476/implementing-an-restful-api-authentication-using-tokens-yii-yii2
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return '{{users}}';
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['password'], $fields['salt'], $fields['password_reset_token']);

        return $fields;
    }
}
