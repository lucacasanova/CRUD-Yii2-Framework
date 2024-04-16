<?php

namespace app\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\UnauthorizedHttpException;
use app\models\User;

class BearerAuthFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $token = $matches[1];
            $user = User::find()->where(['access_token' => $token])->one();
            if ($user !== null) {
                return parent::beforeAction($action);
            }
        }

        throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
    }
}
