<?php

namespace tests\functional;

use app\models\Task;
use FunctionalTester;

class TaskControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function openIndexPage(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        // Italian interface - look for the actual link text
        $I->see('Nuovo Task', 'a');
        // Should see task grid/list
        $I->seeElement('.grid-view'); // GridView container
    }

    public function createNewTask(FunctionalTester $I)
    {
        $I->amOnPage(['task/create']);
        $I->see('Nuovo Task', 'h1'); // Italian interface
        
        $I->submitForm('#task-form', [
            'Task[title]' => 'New Test Task',
            'Task[description]' => 'Test Description',
            'Task[assigned_to]' => 'Tester',
            'Task[status]' => Task::STATUS_ACTIVE
        ]);
        
        $I->seeRecord('app\models\Task', ['title' => 'New Test Task']);
        $I->see('New Test Task', 'h1'); // Assuming after create redirect to view
    }

    public function viewTask(FunctionalTester $I)
    {
        // Must create manually or via I->haveRecord if available
        // Since database cleanup might not be perfect or I want isolation, better create via ActiveRecord in code 
        // IF the test DB is the same. Codeception usually wraps in transaction.
        
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'View Test Task';
        $task->description = 'Secret Description';
        $task->status = Task::STATUS_ACTIVE;
        $task->save();
        
        $I->amOnPage(['task/view', 'id' => $task->id]);
        $I->see('View Test Task', 'h1');
        $I->see('Secret Description');
    }

    public function indexShowsDescriptionPreview(FunctionalTester $I)
    {
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'Index Preview Task';
        $task->description = 'This content should be visible in preview';
        $task->status = Task::STATUS_ACTIVE;
        $task->save();

        $I->amOnPage(['task/index']);
        $I->see('Index Preview Task');
        $I->see('This content should be visible in preview'); // Description IS shown in preview
        $I->seeElement('.task-description-preview'); // Should have preview class
    }

    public function updateTask(FunctionalTester $I)
    {
        // Create a task to update
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'Original Task Title';
        $task->description = 'Original description';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->priority = 1;
        $task->save();

        $I->amOnPage(['task/update', 'id' => $task->id]);
        $I->see('Modifica Task:'); // Italian interface
        
        // Update the task
        $I->fillField('Task[title]', 'Updated Task Title');
        $I->fillField('Task[description]', 'Updated description content');
        $I->selectOption('Task[status]', Task::STATUS_ACTIVE);
        $I->selectOption('Task[priority]', '5');
        $I->click('Salva'); // Italian save button
        
        // Should redirect to view page
        $I->see('Updated Task Title', 'h1');
        $I->see('Updated description content');
        
        // Verify in database
        $I->seeRecord('app\models\Task', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'status' => Task::STATUS_ACTIVE,
            'priority' => 5
        ]);
    }

    public function deleteTask(FunctionalTester $I)
    {
        // Create a task to delete
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'Task to Delete';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();
        
        $taskId = $task->id;

        $I->amOnPage(['task/view', 'id' => $taskId]);
        $I->see('Task to Delete', 'h1');
        
        // Click delete button (should be a POST form)
        $I->click('Elimina'); // Italian delete button
        
        // Should redirect to index
        $I->seeCurrentUrlEquals('/index-test.php?r=task%2Findex');
        
        // Task should be deleted from database
        $I->dontSeeRecord('app\models\Task', ['id' => $taskId]);
    }

    public function testFormValidation(FunctionalTester $I)
    {
        $I->amOnPage(['task/create']);
        
        // Submit form without required title
        $I->submitForm('#task-form', [
            'Task[title]' => '', // Empty title should fail validation
            'Task[description]' => 'Some description'
        ]);
        
        // Should stay on create page and show validation error
        $I->seeCurrentUrlMatches('/task\/create/');
        $I->see('Nuovo Task', 'h1'); // Still on create page
        // Should see validation error (exact text depends on translation)
    }
}
