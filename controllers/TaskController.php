<?php

namespace app\controllers;

use Yii;
use app\models\Task;
use app\models\TaskAttachment;
use app\models\TaskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

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
                    'upload-attachment' => ['POST'],
                    'delete-attachment' => ['POST'],
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
            $model->syncRelatedTasks();
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
        $oldRelatedIds = $model->parseRelatedIds();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->syncRelatedTasks($oldRelatedIds);
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
        $model = $this->findModel($id);

        // Remove this task's ID from all related tasks
        $model->unlinkAllRelatedTasks();

        // Delete physical files before deleting the task (FK CASCADE handles DB records)
        foreach ($model->attachments as $attachment) {
            $filePath = $attachment->getFilePath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Upload attachments to a task.
     * @param integer $id Task ID
     * @return mixed
     */
    public function actionUploadAttachment($id)
    {
        $task = $this->findModel($id);
        $files = UploadedFile::getInstancesByName('attachments');

        if (empty($files)) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'No files selected.'));
            return $this->redirect(['view', 'id' => $id]);
        }

        $uploadPath = Yii::getAlias('@webroot/uploads/task-attachments/');
        $successCount = 0;

        foreach ($files as $file) {
            $extension = $file->extension;
            $storedName = TaskAttachment::generateStoredName($extension);

            if ($file->saveAs($uploadPath . $storedName)) {
                $attachment = new TaskAttachment();
                $attachment->task_id = $task->id;
                $attachment->original_name = $file->name;
                $attachment->stored_name = $storedName;
                $attachment->mime_type = $file->type;
                $attachment->file_size = $file->size;

                if ($attachment->save()) {
                    $successCount++;
                } else {
                    // Remove the file if DB save fails
                    @unlink($uploadPath . $storedName);
                }
            }
        }

        if ($successCount > 0) {
            Yii::$app->session->setFlash('success', Yii::t('app', '{count} file(s) uploaded successfully.', ['count' => $successCount]));
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * View an attachment inline in the browser.
     * @param integer $id Attachment ID
     * @return mixed
     */
    public function actionViewAttachment($id)
    {
        $attachment = TaskAttachment::findOne($id);
        if ($attachment === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        $filePath = $attachment->getFilePath();
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(Yii::t('app', 'File not found.'));
        }

        return Yii::$app->response->sendFile($filePath, $attachment->original_name, [
            'inline' => true,
            'mimeType' => $attachment->mime_type,
        ]);
    }

    /**
     * Download an attachment.
     * @param integer $id Attachment ID
     * @return mixed
     */
    public function actionDownloadAttachment($id)
    {
        $attachment = TaskAttachment::findOne($id);
        if ($attachment === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        $filePath = $attachment->getFilePath();
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(Yii::t('app', 'File not found.'));
        }

        return Yii::$app->response->sendFile($filePath, $attachment->original_name);
    }

    /**
     * Delete an attachment.
     * @param integer $id Attachment ID
     * @return mixed
     */
    public function actionDeleteAttachment($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            try {
                $attachment = TaskAttachment::findOne($id);
                if ($attachment === null) {
                    Yii::$app->response->setStatusCode(404);
                    return ['success' => false, 'message' => 'Attachment not found'];
                }

                $taskId = $attachment->task_id;
                $filePath = $attachment->getFilePath();

                if ($attachment->delete()) {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    return ['success' => true, 'message' => Yii::t('app', 'File deleted successfully.')];
                } else {
                    return ['success' => false, 'message' => 'Failed to delete attachment'];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        
        // Non-AJAX request handling
        $attachment = TaskAttachment::findOne($id);
        if ($attachment === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        $taskId = $attachment->task_id;
        $filePath = $attachment->getFilePath();

        if ($attachment->delete()) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'File deleted successfully.'));
        }

        return $this->redirect(['view', 'id' => $taskId]);
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
        
        if (!Yii::$app->request->isAjax) {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        try {
            $id = Yii::$app->request->post('id');
            $status = Yii::$app->request->post('status');
            
            $task = $this->findModel($id);
            $task->status = $status;
            
            if ($task->save()) {
                return ['success' => true, 'message' => 'Status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update status', 'errors' => $task->errors];
            }
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::$app->response->setStatusCode(404);
            return ['success' => false, 'message' => 'Task not found'];
        }
    }

    /**
     * Change task assignee via AJAX
     * @return string JSON response
     */
    public function actionChangeAssignee()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        try {
            $id = Yii::$app->request->post('id');
            $assignedTo = Yii::$app->request->post('assigned_to');

            $task = $this->findModel($id);
            $task->assigned_to = $assignedTo ?: null;
            
            if ($task->save()) {
                return ['success' => true, 'message' => 'Assignee updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update assignee', 'errors' => $task->errors];
            }
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::$app->response->setStatusCode(404);
            return ['success' => false, 'message' => 'Task not found'];
        }
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
        // Validate that id is a positive integer
        if (!is_numeric($id) || $id <= 0 || $id != (int)$id) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if (($model = Task::findOne((int)$id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
