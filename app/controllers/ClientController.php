<?php

namespace app\controllers;

use Yii;

use app\filters\BearerAuthFilter;
use app\models\Client;
use yii\web\UploadedFile;

class ClientController extends \yii\rest\ActiveController
{
    public $modelClass = 'app\models\Client';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BearerAuthFilter::className(),
            'only' => ['store', 'list'],
        ];
        return $behaviors;
    }

    private function validate_cep($cep, &$mask = null, &$error = null)
    {
        if (empty($cep)) {
            $error = 'The postal code cannot be empty';
            return false;
        }
        $mask = null;
        $error = null;
        $cep = trim(preg_replace('`[^0-9]`', '', $cep));
        if (strlen($cep) === 8) {
            $mask = sprintf('%s-%s', substr($cep, 0, 5), substr($cep, 5));
        } else {
            $mask = $cep;
        }
        if (!$cep) {
            $error = 'The postal code cannot be empty';
            return false;
        }
        if (strlen($cep) !== 8) {
            $error = 'The postal code must contain 8 digits';
            return false;
        }
        return $cep;
    }

    private function validate_cpf($cpf, &$mask = null, &$error = null)
    {
        if (empty($cpf)) {
            $error = 'The CPF cannot be empty';
            return false;
        }
        $mask = null;
        $error = null;
        $cpf = trim(preg_replace('`[^0-9]`', '', $cpf));
        if (!$cpf) {
            $error = 'The CPF cannot be empty';
            return false;
        }
        if (strlen($cpf) !== 11) {
            $error = 'The CPF must contain 11 digits' . ' (' . $cpf . ')';
            return false;
        }
        if (preg_match('`([0-9])\1{10}`', $cpf)) {
            $error = 'The CPF is incorrect';
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        $mask = sprintf('%s.%s.%s-%s', substr($cpf, 0, 3), substr($cpf, 3, 3), substr($cpf, 6, 3), substr($cpf, 9, 2));
        return $cpf;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['update']);
        return $actions;
    }

    public function actionStore()
    {

        $name = Yii::$app->request->post('name');
        $cpf = Yii::$app->request->post('cpf');
        $postal_code = Yii::$app->request->post('postal_code');
        $street = Yii::$app->request->post('street');
        $number = Yii::$app->request->post('number');
        $city = Yii::$app->request->post('city');
        $state = Yii::$app->request->post('state');
        $additional_information = Yii::$app->request->post('additional_information');
        $gender = Yii::$app->request->post('gender');

        $picture = null;
        $pictureFile = UploadedFile::getInstanceByName('picture');
        if ($pictureFile) {
            $picture = base64_encode(file_get_contents($pictureFile->tempName));
        }

        $args = [];

        if (!($postal_code = $this->validate_cep($postal_code, $args['postal_code_mask'], $args['postal_code_error']))) {
            throw new \yii\web\ServerErrorHttpException('Postal code Error: ' . $args['postal_code_error']);
        }

        if (!($cpf = $this->validate_cpf($cpf, $args['cpf_mask'], $args['cpf_error']))) {
            throw new \yii\web\ServerErrorHttpException('CPF Error: ' . $args['cpf_error']);
        }

        if (empty($name)) {
            throw new \yii\web\ServerErrorHttpException('Name must not be empty');
        }
        if (empty($gender) || !in_array($gender, ['female', 'male'])) {
            throw new \yii\web\ServerErrorHttpException('Gender must be either "female" or "male".');
        }
        if (empty($street) || empty($city) || empty($state)) {
            $json = file_get_contents('https://viacep.com.br/ws/' . $postal_code . '/json/');
            $data = json_decode($json, true);
            if (!isset($data['erro']) && $data['erro'] !== true) {
                if (empty($street)) {
                    $street = $data['logradouro'];
                }
                if (empty($city)) {
                    $city = $data['localidade'];
                }
                if (empty($state)) {
                    $state = $data['uf'];
                }
            }
        }
        if (empty($street)) {
            throw new \yii\web\ServerErrorHttpException('Street must not be empty');
        }
        if (empty($city)) {
            throw new \yii\web\ServerErrorHttpException('City must not be empty');
        }
        if (empty($state)) {
            throw new \yii\web\ServerErrorHttpException('State must not be empty');
        }
        if (empty($number)) {
            throw new \yii\web\ServerErrorHttpException('Number must not be empty');
        }
        if (empty($picture)) {
            throw new \yii\web\ServerErrorHttpException('Picture must not be empty');
        }

        $client = new Client();

        $client->name = $name;
        $client->cpf = $cpf;
        $client->postal_code = $postal_code;
        $client->street = $street;
        $client->number = $number;
        $client->city = $city;
        $client->state = $state;
        $client->additional_information = $additional_information;
        $client->gender = $gender;
        $client->picture = $picture;

        if (!$client->save()) {
            throw new \yii\web\ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $client;
    }

    public function actionList($page = 1)
    {
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        $query = Client::find();

        $total = $query->count();
        $totalPages = ceil($total / $pageSize);

        // if ($page > $totalPages) {
        //     throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        // }

        $hasMore = $page < $totalPages;

        $products = $query->offset($offset)
            ->limit($pageSize)
            ->all();

        return [
            'products' => $products,
            'currentPage' => intval($page),
            'count' => intval($total),
            'hasMore' => $hasMore,
            'perPage' => $pageSize,
            'totalPages' => $totalPages
        ];
    }
}
