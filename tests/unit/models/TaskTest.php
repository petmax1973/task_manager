<?php

namespace tests\unit\models;

use app\models\Task;
use Codeception\Test\Unit;

class TaskTest extends Unit
{
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // Test validation rules
    public function testTaskValidation()
    {
        $task = new Task();
        
        // Title is required
        $task->title = null;
        $this->assertFalse($task->validate(['title']));
        
        // Title should be string max 255
        $task->title = str_repeat('a', 256);
        $this->assertFalse($task->validate(['title']));
        
        $task->title = 'Valid Title';
        $this->assertTrue($task->validate(['title']));
    }

    public function testStatusValidation()
    {
        $task = new Task();
        $task->title = 'Test Task';
        
        // Invalid status
        $task->status = 'invalid_status';
        $this->assertFalse($task->validate(['status']));
        
        // Valid statuses
        $task->status = Task::STATUS_IN_PROGRESS;
        $this->assertTrue($task->validate(['status']));
        
        $task->status = Task::STATUS_SUSPENDED;
        $this->assertTrue($task->validate(['status']));
        
        $task->status = Task::STATUS_TO_RELEASE;
        $this->assertTrue($task->validate(['status']));
        
        $task->status = Task::STATUS_COMPLETED;
        $this->assertTrue($task->validate(['status']));
    }

    public function testDefaultStatus()
    {
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'Test Task';
        
        // Check default status is in_progress
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $task->status);
    }

    public function testOptionalFields()
    {
        $task = new Task();
        $task->title = 'Test Task';
        
        // Description and assigned_to are optional
        $task->description = null;
        $task->assigned_to = null;
        
        $this->assertTrue($task->validate());
    }

    public function testStatusConstants()
    {
        $this->assertEquals('in_progress', Task::STATUS_IN_PROGRESS);
        $this->assertEquals('suspended', Task::STATUS_SUSPENDED);
        $this->assertEquals('to_release', Task::STATUS_TO_RELEASE);
        $this->assertEquals('completed', Task::STATUS_COMPLETED);
    }

    public function testGetStatusList()
    {
        $statusList = Task::getStatusList();
        
        $this->assertIsArray($statusList);
        $this->assertCount(4, $statusList);
        $this->assertArrayHasKey(Task::STATUS_IN_PROGRESS, $statusList);
        $this->assertArrayHasKey(Task::STATUS_SUSPENDED, $statusList);
        $this->assertArrayHasKey(Task::STATUS_TO_RELEASE, $statusList);
        $this->assertArrayHasKey(Task::STATUS_COMPLETED, $statusList);
    }
}
