<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\User;

class AuthController extends Controller
{
    public function actionLogin()
    {
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        if (empty($username) || empty($password)) {
            throw new \yii\web\BadRequestHttpException('Username and password must not be empty');
        }
        
        $user = User::findByUsername($username);

        if (!$user || !$user->validatePassword($password)) {
            throw new \yii\web\UnauthorizedHttpException('Invalid credentials');
        }

        $user->access_token = \Yii::$app->getSecurity()->generateRandomString();

        if (!$user->save()) {
            throw new \yii\web\ServerErrorHttpException('Failed to generate new access token');
        }

        return ['access_token' => $user->access_token];
    }
}
