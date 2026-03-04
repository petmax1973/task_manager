<?php

namespace tests\functional;

use app\models\Task;
use FunctionalTester;

class TaskControllerAjaxCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function testUpdatePriorityAjax(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Priority Test Task';
        $task->description = 'Test task for priority update';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->priority = 1;
        $task->save();

        // Send AJAX request to update priority
        $I->sendAjaxPostRequest('/task/update-priority', [
            'id' => $task->id,
            'priority' => 5
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);

        // Verify task priority was updated
        $task->refresh();
        $I->assertEquals(5, $task->priority);
    }

    public function testUpdatePriorityInvalidTask(FunctionalTester $I)
    {
        // Send AJAX request with non-existent task ID
        $I->sendAjaxPostRequest('/task/update-priority', [
            'id' => 99999,
            'priority' => 3
        ]);

        $I->seeResponseCodeIs(404);
    }

    public function testUpdatePriorityInvalidPriority(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Invalid Priority Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->priority = 1;
        $task->save();

        // Send AJAX request with invalid priority (out of range)
        $I->sendAjaxPostRequest('/task/update-priority', [
            'id' => $task->id,
            'priority' => 10 // Invalid - should be 1-5
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => false]);
    }

    public function testChangeStatusAjax(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Status Change Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->priority = 1;
        $task->save();

        // Send AJAX request to change status
        $I->sendAjaxPostRequest('/task/change-status', [
            'id' => $task->id,
            'status' => Task::STATUS_ACTIVE
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);

        // Verify task status was updated
        $task->refresh();
        $I->assertEquals(Task::STATUS_ACTIVE, $task->status);
    }

    public function testChangeStatusInvalidStatus(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Invalid Status Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        // Send AJAX request with invalid status
        $I->sendAjaxPostRequest('/task/change-status', [
            'id' => $task->id,
            'status' => 'invalid_status'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => false]);

        // Verify task status was NOT changed
        $task->refresh();
        $I->assertEquals(Task::STATUS_IN_PROGRESS, $task->status);
    }

    public function testChangeAssigneeAjax(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Assignee Test Task';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->assigned_to = null;
        $task->save();

        // Send AJAX request to change assignee
        $I->sendAjaxPostRequest('/task/change-assignee', [
            'id' => $task->id,
            'assignee' => 'John Doe'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);

        // Verify task assignee was updated
        $task->refresh();
        $I->assertEquals('John Doe', $task->assigned_to);
    }

    public function testChangeAssigneeEmpty(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Empty Assignee Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->assigned_to = 'Original Assignee';
        $task->save();

        // Send AJAX request to clear assignee (empty string)
        $I->sendAjaxPostRequest('/task/change-assignee', [
            'id' => $task->id,
            'assignee' => ''
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);

        // Verify assignee was cleared
        $task->refresh();
        $I->assertEmpty($task->assigned_to);
    }

    public function testAjaxRequestsRequireAjax(FunctionalTester $I)
    {
        // Create a test task
        $task = new Task();
        $task->title = 'Non-AJAX Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        // Send regular POST request (not AJAX) - should fail
        $I->sendPOST('/task/update-priority', [
            'id' => $task->id,
            'priority' => 3
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => false]);
    }
}