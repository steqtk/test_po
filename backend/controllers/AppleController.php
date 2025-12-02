<?php

namespace backend\controllers;

use Yii;
use common\models\Apple;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AppleController управляет операциями с яблоками
 */
class AppleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'fall' => ['POST'],
                    'eat' => ['POST'],
                    'generate' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает список всех яблок
     * 
     * @return string
     */
    public function actionIndex()
    {
        $apples = Apple::find()->all();
        
        // Обновляем статус всех яблок (проверка на гниение)
        foreach ($apples as $apple) {
            $apple->updateStatus();
        }
        
        return $this->render('index', [
            'apples' => $apples,
        ]);
    }

    /**
     * Генерирует случайное количество яблок (от 5 до 20)
     * 
     * @return \yii\web\Response
     */
    public function actionGenerate()
    {
        $count = mt_rand(5, 20);
        
        for ($i = 0; $i < $count; $i++) {
            $apple = Apple::createRandom();
            $apple->save();
        }
        
        Yii::$app->session->setFlash('success', "Создано яблок: {$count}");
        
        return $this->redirect(['index']);
    }

    /**
     * Яблоко падает с дерева
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionFall($id)
    {
        $apple = $this->findModel($id);
        
        try {
            $apple->fallToGround();
            Yii::$app->session->setFlash('success', 'Яблоко упало на землю');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Съесть процент яблока
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionEat($id)
    {
        $apple = $this->findModel($id);
        $percent = (float) Yii::$app->request->post('percent', 0);
        
        try {
            $apple->eat($percent);
            
            if ($percent >= 100 || $apple->eaten_percent >= 100) {
                Yii::$app->session->setFlash('success', 'Яблоко полностью съедено и удалено');
            } else {
                Yii::$app->session->setFlash('success', "Откушено {$percent}% яблока");
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Удаляет яблоко
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        try {
            $apple = $this->findModel($id);
            $apple->delete();
            Yii::$app->session->setFlash('success', 'Яблоко удалено');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Находит модель Apple по ID
     * 
     * @param int $id
     * @return Apple
     * @throws NotFoundHttpException если яблоко не найдено
     */
    protected function findModel($id)
    {
        if (($model = Apple::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Яблоко не найдено');
    }
}

