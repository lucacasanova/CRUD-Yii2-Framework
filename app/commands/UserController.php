<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\User;

class UserController extends Controller
{
    private function validate_name($name, &$error = null)
    {
        $error = null;
        $name = trim(preg_replace('`[\x20]{2,}`', "\x20", $name));
        if (!$name) {
            $error = 'Name cannot be empty';
            return false;
        }
        if (strpos($name, "\x20") === false) {
            $error = 'The last name cannot be empty';
            return false;
        }
        return $name;
    }

    public function actionCreate($username, $password, $name)
    {
        $user = new User();
        $args = [];
        if (!($name = $this->validate_name($name, $args['name_error']))) {
            throw new \yii\web\ServerErrorHttpException('Name validation failed: ' . $args['name_error']);
        }

        $parts = explode(' ', $name, 2);
        $user->first_name = $parts[0];
        $user->last_name = $parts[1];

        $user->username = $username;
        $user->password = \Yii::$app->getSecurity()->generatePasswordHash($password);
        $user->access_token = \Yii::$app->getSecurity()->generateRandomString();


        if ($user->save()) {
            echo "User {$username} created successfully.\n";
            return ExitCode::OK;
        }

        echo "Failed to create user.\n";
        return ExitCode::UNSPECIFIED_ERROR;
    }
}
