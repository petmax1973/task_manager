<?php

namespace app\controllers;

use Yii;
use app\models\Task;
use app\models\TaskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'change-status' => ['POST'],
                    'change-assignee' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Task models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TaskSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Task model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Task model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Task();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Task model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Updates the priority of a task via AJAX.
     * @return array JSON response
     */
    public function actionUpdatePriority()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isAjax) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $id = Yii::$app->request->post('id');
        $priority = Yii::$app->request->post('priority');

        if (!$id || !$priority) {
            return ['success' => false, 'message' => 'Missing parameters'];
        }

        try {
            $model = $this->findModel($id);
            $model->priority = $priority;
            
            if ($model->save(false)) {
                return ['success' => true, 'message' => Yii::t('app', 'Priority updated successfully')];
            } else {
                return ['success' => false, 'message' => Yii::t('app', 'Failed to update priority')];
            }
        } catch (NotFoundHttpException $e) {
            return ['success' => false, 'message' => Yii::t('app', 'Task not found')];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Change task status via AJAX
     * @return string JSON response
     */
    public function actionChangeStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $status = Yii::$app->request->post('status');
            
            $task = $this->findModel($id);
            if ($task) {
                $task->status = $status;
                if ($task->save()) {
                    return ['success' => true, 'message' => 'Status updated successfully'];
                } else {
                    return ['success' => false, 'message' => 'Failed to update status', 'errors' => $task->errors];
                }
            }
        }
        
        return ['success' => false, 'message' => 'Invalid request'];
    }

    /**
     * Change task assignee via AJAX
     * @return string JSON response
     */
    public function actionChangeAssignee()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $assignedTo = Yii::$app->request->post('assigned_to');

            $task = $this->findModel($id);
            if ($task) {
                $task->assigned_to = $assignedTo ?: null;
                if ($task->save()) {
                    return ['success' => true, 'message' => 'Assignee updated successfully'];
                } else {
                    return ['success' => false, 'message' => 'Failed to update assignee', 'errors' => $task->errors];
                }
            }
        }

        return ['success' => false, 'message' => 'Invalid request'];
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Task::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
