<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use app\models\Status;
use app\models\Assignee;
use app\models\Task;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

class SettingsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add-project' => ['POST'],
                    'delete-project' => ['POST'],
                    'add-status' => ['POST'],
                    'delete-status' => ['POST'],
                    'add-assignee' => ['POST'],
                    'delete-assignee' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $projects = Project::find()->orderBy('name')->all();
        $statuses = Status::find()->orderBy('sort_order')->all();
        $assignees = Assignee::find()->orderBy('name')->all();

        return $this->render('index', [
            'projects' => $projects,
            'statuses' => $statuses,
            'assignees' => $assignees,
        ]);
    }

    public function actionAddProject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Project();
        $model->slug = Yii::$app->request->post('slug');
        $model->name = Yii::$app->request->post('name');

        if ($model->save()) {
            return ['success' => true, 'id' => $model->id, 'slug' => $model->slug, 'name' => $model->name];
        }

        return ['success' => false, 'errors' => $model->getFirstErrors()];
    }

    public function actionDeleteProject($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Project::findOne($id);
        if ($model === null) {
            return ['success' => false, 'message' => Yii::t('app', 'Not found.')];
        }

        // Check if in use
        if (Task::find()->where(['project' => $model->slug])->exists()) {
            return ['success' => false, 'message' => Yii::t('app', 'Cannot delete: value is in use by one or more tasks.')];
        }

        $model->delete();
        return ['success' => true];
    }

    public function actionAddStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Status();
        $model->slug = Yii::$app->request->post('slug');
        $model->name = Yii::$app->request->post('name');
        $model->color = Yii::$app->request->post('color', 'black');
        $model->sort_order = Yii::$app->request->post('sort_order', 0);

        if ($model->save()) {
            return ['success' => true, 'id' => $model->id, 'slug' => $model->slug, 'name' => $model->name, 'color' => $model->color, 'sort_order' => $model->sort_order];
        }

        return ['success' => false, 'errors' => $model->getFirstErrors()];
    }

    public function actionDeleteStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Status::findOne($id);
        if ($model === null) {
            return ['success' => false, 'message' => Yii::t('app', 'Not found.')];
        }

        if (Task::find()->where(['status' => $model->slug])->exists()) {
            return ['success' => false, 'message' => Yii::t('app', 'Cannot delete: value is in use by one or more tasks.')];
        }

        $model->delete();
        return ['success' => true];
    }

    public function actionAddAssignee()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Assignee();
        $model->name = Yii::$app->request->post('name');

        if ($model->save()) {
            return ['success' => true, 'id' => $model->id, 'name' => $model->name];
        }

        return ['success' => false, 'errors' => $model->getFirstErrors()];
    }

    public function actionDeleteAssignee($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Assignee::findOne($id);
        if ($model === null) {
            return ['success' => false, 'message' => Yii::t('app', 'Not found.')];
        }

        if (Task::find()->where(['assigned_to' => $model->name])->exists()) {
            return ['success' => false, 'message' => Yii::t('app', 'Cannot delete: value is in use by one or more tasks.')];
        }

        $model->delete();
        return ['success' => true];
    }
}
