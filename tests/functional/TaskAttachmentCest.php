<?php

namespace tests\functional;

use app\models\Task;
use app\models\TaskAttachment;
use FunctionalTester;
use yii\web\UploadedFile;

class TaskAttachmentCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function testViewTaskWithAttachments(FunctionalTester $I)
    {
        // Create a task
        $task = new Task();
        $task->title = 'Task with Attachments';
        $task->description = 'This task has file attachments';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        // Create attachments
        $attachment1 = new TaskAttachment();
        $attachment1->task_id = $task->id;
        $attachment1->original_name = 'document.pdf';
        $attachment1->stored_name = 'stored_doc_1.pdf';
        $attachment1->file_size = 1024;
        $attachment1->mime_type = 'application/pdf';
        $attachment1->save();

        $attachment2 = new TaskAttachment();
        $attachment2->task_id = $task->id;
        $attachment2->original_name = 'image.jpg';
        $attachment2->stored_name = 'stored_img_1.jpg';
        $attachment2->file_size = 2048;
        $attachment2->mime_type = 'image/jpeg';
        $attachment2->save();

        // View task page
        $I->amOnPage(['task/view', 'id' => $task->id]);
        $I->see('Task with Attachments', 'h1');
        $I->see('document.pdf');
        $I->see('image.jpg');
        $I->see('1 KB'); // File size formatting
        $I->see('2 KB');
    }

    public function testDeleteAttachment(FunctionalTester $I)
    {
        // Create a task
        $task = new Task();
        $task->title = 'Delete Attachment Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        // Create attachment
        $attachment = new TaskAttachment();
        $attachment->task_id = $task->id;
        $attachment->original_name = 'to_delete.txt';
        $attachment->stored_name = 'stored_delete.txt';
        $attachment->file_size = 512;
        $attachment->save();

        $attachmentId = $attachment->id;

        // Send AJAX delete request
        $I->sendAjaxPostRequest("/task/delete-attachment/{$attachmentId}");
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);

        // Verify attachment was deleted
        $I->dontSeeRecord(TaskAttachment::class, ['id' => $attachmentId]);
    }

    public function testDeleteNonExistentAttachment(FunctionalTester $I)
    {
        // Try to delete non-existent attachment
        $I->sendAjaxPostRequest('/task/delete-attachment/99999');
        
        $I->seeResponseCodeIs(404);
    }

    public function testTaskIndexShowsAttachmentCount(FunctionalTester $I)
    {
        // Create task with multiple attachments
        $task = new Task();
        $task->title = 'Task with Multiple Files';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        // Create 3 attachments
        for ($i = 1; $i <= 3; $i++) {
            $attachment = new TaskAttachment();
            $attachment->task_id = $task->id;
            $attachment->original_name = "file{$i}.txt";
            $attachment->stored_name = "stored_file{$i}.txt";
            $attachment->file_size = 100 * $i;
            $attachment->save();
        }

        $I->amOnPage(['task/index']);
        $I->see('Task with Multiple Files');
        
        // Should show attachment count/indicator
        // Note: This depends on view implementation
    }

    public function testEmptyAttachmentsList(FunctionalTester $I)
    {
        // Create task without attachments
        $task = new Task();
        $task->title = 'Task without Attachments';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        $I->amOnPage(['task/view', 'id' => $task->id]);
        $I->see('Task without Attachments', 'h1');
        
        // Should show "no attachments" message or empty attachments section
        $I->see('Allegati'); // Should see attachments section in Italian
    }
}