<?php

namespace tests\unit\models;

use app\models\Task;
use app\models\TaskAttachment;
use Codeception\Test\Unit;

class TaskAttachmentTest extends Unit
{
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testTaskAttachmentValidation()
    {
        $attachment = new TaskAttachment();
        
        // Required fields
        $attachment->task_id = null;
        $attachment->original_name = null;
        $attachment->stored_name = null;
        $attachment->file_size = null;
        
        $this->assertFalse($attachment->validate());
        $this->assertTrue($attachment->hasErrors('task_id'));
        $this->assertTrue($attachment->hasErrors('original_name'));
        $this->assertTrue($attachment->hasErrors('stored_name'));
        $this->assertTrue($attachment->hasErrors('file_size'));
    }

    public function testValidAttachment()
    {
        // Create a task first
        $task = new Task();
        $task->title = 'Test Task for Attachment';
        $task->status = Task::STATUS_IN_PROGRESS;
        $this->assertTrue($task->save());
        
        $attachment = new TaskAttachment();
        $attachment->task_id = $task->id;
        $attachment->original_name = 'test_file.pdf';
        $attachment->stored_name = 'unique_stored_name.pdf';
        $attachment->mime_type = 'application/pdf';
        $attachment->file_size = 1024;
        
        $this->assertTrue($attachment->validate());
        $this->assertTrue($attachment->save());
    }

    public function testFileSizeValidation()
    {
        $attachment = new TaskAttachment();
        $attachment->task_id = 1;
        $attachment->original_name = 'test.txt';
        $attachment->stored_name = 'stored.txt';
        
        // Test negative file size
        $attachment->file_size = -1;
        $this->assertFalse($attachment->validate(['file_size']));
        
        // Test zero file size (should be invalid)
        $attachment->file_size = 0;
        $this->assertFalse($attachment->validate(['file_size']));
        
        // Test valid file size
        $attachment->file_size = 1024;
        $this->assertTrue($attachment->validate(['file_size']));
    }

    public function testFileNameLength()
    {
        $attachment = new TaskAttachment();
        $attachment->task_id = 1;
        $attachment->file_size = 1024;
        
        // Test max length for original_name (255)
        $attachment->original_name = str_repeat('a', 256);
        $this->assertFalse($attachment->validate(['original_name']));
        
        $attachment->original_name = str_repeat('a', 255);
        $attachment->stored_name = 'valid_stored.txt';
        $this->assertTrue($attachment->validate(['original_name']));
        
        // Test max length for stored_name (255)
        $attachment->stored_name = str_repeat('b', 256);
        $this->assertFalse($attachment->validate(['stored_name']));
        
        $attachment->stored_name = str_repeat('b', 255);
        $this->assertTrue($attachment->validate(['stored_name']));
    }

    public function testTaskRelation()
    {
        // Create a task
        $task = new Task();
        $task->title = 'Task with Attachment';
        $task->status = Task::STATUS_IN_PROGRESS;
        $this->assertTrue($task->save());
        
        // Create attachment
        $attachment = new TaskAttachment();
        $attachment->task_id = $task->id;
        $attachment->original_name = 'related_file.doc';
        $attachment->stored_name = 'stored_related.doc';
        $attachment->file_size = 2048;
        $this->assertTrue($attachment->save());
        
        // Test relation
        $relatedTask = $attachment->task;
        $this->assertInstanceOf(Task::class, $relatedTask);
        $this->assertEquals($task->id, $relatedTask->id);
        $this->assertEquals('Task with Attachment', $relatedTask->title);
    }

    public function testGetFilePath()
    {
        $attachment = new TaskAttachment();
        $attachment->stored_name = 'test_file.pdf';
        
        $filePath = $attachment->getFilePath();
        $this->assertStringContainsString('uploads', $filePath);
        $this->assertStringContainsString('test_file.pdf', $filePath);
    }

    public function testGetFileUrl()
    {
        $attachment = new TaskAttachment();
        $attachment->stored_name = 'test_file.pdf';
        
        $fileUrl = $attachment->getFileUrl();
        $this->assertStringContainsString('uploads', $fileUrl);
        $this->assertStringContainsString('test_file.pdf', $fileUrl);
    }

    public function testGetHumanFileSize()
    {
        $attachment = new TaskAttachment();
        
        // Test bytes
        $attachment->file_size = 512;
        $this->assertEquals('512 B', $attachment->getHumanFileSize());
        
        // Test KB
        $attachment->file_size = 1536; // 1.5 KB
        $this->assertEquals('1.5 KB', $attachment->getHumanFileSize());
        
        // Test MB
        $attachment->file_size = 1572864; // 1.5 MB
        $this->assertEquals('1.5 MB', $attachment->getHumanFileSize());
        
        // Test GB
        $attachment->file_size = 1610612736; // 1.5 GB
        $this->assertEquals('1.5 GB', $attachment->getHumanFileSize());
    }

    public function testTimestampBehavior()
    {
        $task = new Task();
        $task->title = 'Test Task';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();
        
        $attachment = new TaskAttachment();
        $attachment->task_id = $task->id;
        $attachment->original_name = 'timestamp_test.txt';
        $attachment->stored_name = 'stored_timestamp.txt';
        $attachment->file_size = 100;
        
        $this->assertTrue($attachment->save());
        $this->assertNotNull($attachment->created_at);
        $this->assertIsInt($attachment->created_at);
    }

    public function testAttributeLabels()
    {
        $attachment = new TaskAttachment();
        $labels = $attachment->attributeLabels();
        
        $this->assertIsArray($labels);
        $this->assertArrayHasKey('original_name', $labels);
        $this->assertArrayHasKey('file_size', $labels);
        $this->assertArrayHasKey('mime_type', $labels);
    }
}