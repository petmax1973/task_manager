<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;


class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }



    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Change language
     * @param string $lang
     * @return Response
     */
    public function actionLanguage($lang)
    {
        if (in_array($lang, ['it-IT', 'en-US'])) {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'language',
                'value' => $lang,
                'expire' => time() + 3600 * 24 * 30, // 30 days
            ]));
        }
        
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
}
