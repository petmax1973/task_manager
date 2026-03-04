<?php

namespace tests\unit\models;

use app\models\Task;
use app\models\TaskAttachment;
use app\models\Assignee;
use Codeception\Test\Unit;

class TaskRelationsTest extends Unit
{
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testTaskAttachmentsRelation()
    {
        // Create a task
        $task = new Task();
        $task->title = 'Task with Attachments Relation Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        // Create attachments
        $attachment1 = new TaskAttachment();
        $attachment1->task_id = $task->id;
        $attachment1->original_name = 'file1.pdf';
        $attachment1->stored_name = 'stored1.pdf';
        $attachment1->file_size = 1024;
        $attachment1->save();

        $attachment2 = new TaskAttachment();
        $attachment2->task_id = $task->id;
        $attachment2->original_name = 'file2.jpg';
        $attachment2->stored_name = 'stored2.jpg';
        $attachment2->file_size = 2048;
        $attachment2->save();

        // Test relation
        $attachments = $task->attachments;
        $this->assertCount(2, $attachments);
        $this->assertInstanceOf(TaskAttachment::class, $attachments[0]);
        $this->assertInstanceOf(TaskAttachment::class, $attachments[1]);
    }

    public function testTaskAssigneeCreation()
    {
        $initialCount = Assignee::find()->count();

        // Create task with new assignee
        $task = new Task();
        $task->title = 'Task with New Assignee';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->assigned_to = 'New Person Name';
        $task->save();

        // Should create assignee automatically via afterSave hook
        $newCount = Assignee::find()->count();
        $this->assertEquals($initialCount + 1, $newCount);

        // Verify assignee exists
        $assignee = Assignee::findOne(['name' => 'New Person Name']);
        $this->assertNotNull($assignee);
        $this->assertEquals('New Person Name', $assignee->name);
    }

    public function testTaskWithExistingAssignee()
    {
        // Create assignee first
        $existingAssignee = new Assignee();
        $existingAssignee->name = 'Existing Person';
        $existingAssignee->save();

        $initialCount = Assignee::find()->count();

        // Create task with existing assignee
        $task = new Task();
        $task->title = 'Task with Existing Assignee';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->assigned_to = 'Existing Person';
        $task->save();

        // Should NOT create new assignee
        $newCount = Assignee::find()->count();
        $this->assertEquals($initialCount, $newCount);
    }

    public function testTaskWithEmptyAssignee()
    {
        $initialCount = Assignee::find()->count();

        // Create task without assignee
        $task = new Task();
        $task->title = 'Task without Assignee';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->assigned_to = null;
        $task->save();

        // Should NOT create assignee
        $newCount = Assignee::find()->count();
        $this->assertEquals($initialCount, $newCount);

        // Test with empty string
        $task2 = new Task();
        $task2->title = 'Task with Empty Assignee';
        $task2->status = Task::STATUS_IN_PROGRESS;
        $task2->assigned_to = '';
        $task2->save();

        // Should still NOT create assignee 
        $finalCount = Assignee::find()->count();
        $this->assertEquals($initialCount, $finalCount);
    }

    public function testTaskStatusLabelMethod()
    {
        $task = new Task();
        $task->status = Task::STATUS_ACTIVE;

        $statusLabel = $task->getStatusLabel();
        $this->assertIsString($statusLabel);
        $this->assertNotEmpty($statusLabel);
        
        // Should be localized label, not raw value
        $this->assertNotEquals('active', $statusLabel);
    }

    public function testTaskTimestampBehavior()
    {
        $task = new Task();
        $task->title = 'Timestamp Test Task';
        $task->status = Task::STATUS_IN_PROGRESS;
        
        $beforeSave = time();
        $task->save();
        $afterSave = time();
        
        // created_at should be set
        $this->assertNotNull($task->created_at);
        $this->assertGreaterThanOrEqual($beforeSave, $task->created_at);
        $this->assertLessThanOrEqual($afterSave, $task->created_at);
        
        // updated_at should be set  
        $this->assertNotNull($task->updated_at);
        $this->assertEquals($task->created_at, $task->updated_at);
        
        // Update task
        sleep(1); // Ensure different timestamp
        $task->title = 'Updated Timestamp Test';
        $task->save();
        
        // updated_at should be updated, created_at should remain same
        $this->assertGreaterThan($task->created_at, $task->updated_at);
    }

    public function testTaskValidationWithComplexData()
    {
        $task = new Task();
        $task->title = 'Complex Validation Test';
        $task->description = str_repeat('A very long description. ', 1000); // Long text
        $task->status = Task::STATUS_ACTIVE;
        $task->priority = 3;
        $task->assigned_to = 'Person With Spaces And-Dashes_Underscores123';
        $task->gitlab_issue = 'https://gitlab.example.com/project/-/issues/123';
        
        $this->assertTrue($task->validate());
        $this->assertTrue($task->save());
    }

    public function testTaskPrioritydefaultAndValidation()
    {
        // Test default priority
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'Default Priority Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        
        $this->assertEquals(1, $task->priority); // Default should be 1
        
        // Test priority validation range
        $task->priority = 0; // Below minimum
        $this->assertFalse($task->validate(['priority']));
        
        $task->priority = 6; // Above maximum
        $this->assertFalse($task->validate(['priority']));
        
        // Test valid range
        for ($i = 1; $i <= 5; $i++) {
            $task->priority = $i;
            $this->assertTrue($task->validate(['priority']), "Priority $i should be valid");
        }
    }
}