<?php

namespace app\controllers;

use Yii;

use app\filters\BearerAuthFilter;
use app\models\Product;
use app\models\Client;
use yii\web\UploadedFile;

class ProductController extends \yii\rest\ActiveController
{
    public $modelClass = 'app\models\Product';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BearerAuthFilter::className(),
            'only' => ['store', 'list'],
        ];
        return $behaviors;
    }

    private function normalize_price($price)
    {

        $price = trim(preg_replace('`(R|\$|\x20)`i', '', $price));

        /**
         * 123.456.789,01
         */

        if (preg_match('`^([0-9]+(?:\.[0-9]+)+)\,([0-9]+)$`', $price, $match)) {
            return str_replace('.', '', $match[1]) . '.' . $match[2];
        }

        /**
         * 123456789,01
         */

        if (preg_match('`^([0-9]+)\,([0-9]+)$`', $price, $match)) {
            return $match[1] . '.' . $match[2];
        }

        /**
         * 123,456,789.01
         */

        if (preg_match('`^([0-9]+(?:\,[0-9]+)+)\.([0-9]+)$`', $price, $match)) {
            return str_replace(',', '', $match[1]) . '.' . $match[2];
        }

        /**
         * 123456789.01
         */

        if (preg_match('`^([0-9]+)\.([0-9]+)$`', $price, $match)) {
            return $match[1] . '.' . $match[2];
        }

        /**
         * 12345678901
         */

        if (preg_match('`^([0-9]+)$`', $price, $match)) {
            return $match[1];
        }

        /**
         * default
         */

        $price = preg_replace('`(\.|\,)`', '', $price);
        if (preg_match('`^([0-9]+)$`', $price, $match)) {
            return $match[1];
        }

        /**
         * error
         */

        return false;
    }

    private function validate_value($value, &$mask = null, &$error = null)
    {
        $error = null;
        $value = trim(preg_replace('`[\x20]{2,}`', "\x20", $value));
        $value = trim(preg_replace('`(R|\$|\x20)`i', '', $value));
        $mask = $value;
        if (!$value) {
            $error = 'The Price cannot be empty';
            return false;
        }
        $value = $this->normalize_price($value);
        if ($value === false) {
            $error = 'The Price is incorrect';
            return false;
        }
        $mask = number_format($value, 2, ',', '.');
        return $value;
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
        $price = Yii::$app->request->post('price');
        $client_id = Yii::$app->request->post('client_id');

        $picture = null;
        $pictureFile = UploadedFile::getInstanceByName('picture');
        if ($pictureFile) {
            $picture = base64_encode(file_get_contents($pictureFile->tempName));
        }

        $args = [];

        if (empty($name)) {
            throw new \yii\web\ServerErrorHttpException('Name must not be empty');
        }
        if (!($price = $this->validate_value($price, $args['price_mask'], $args['price_error']))) {
            throw new \yii\web\ServerErrorHttpException('Price Error: ' . $args['price_error']);
        }
        if (empty($client_id)) {
            throw new \yii\web\ServerErrorHttpException('Client ID must not be empty');
        }
        $client = Client::findOne($client_id);
        if ($client === null) {
            throw new \yii\web\ServerErrorHttpException('Client not found');
        }
        if (empty($picture)) {
            throw new \yii\web\ServerErrorHttpException('Picture must not be empty');
        }

        $product = new Product();

        $product->name = $name;
        $product->price = $price;
        $product->client_id = $client_id;
        $product->picture = $picture;

        if (!$product->save()) {
            throw new \yii\web\ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $product;
    }

    public function actionList($page = 1, $client_id = null)
    {
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        $query = Product::find();

        if ($client_id !== null) {
            $client = Client::findOne($client_id);
            if ($client === null) {
                throw new \yii\web\ServerErrorHttpException('Client not found');
            }
            $query->where(['client_id' => $client_id]);
        }

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
