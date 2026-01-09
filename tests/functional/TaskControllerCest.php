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
        $I->see('Compiti', 'h1');
        $I->see('Nuovo Compito', 'a');
    }

    public function createNewTask(FunctionalTester $I)
    {
        $I->amOnPage(['task/create']);
        $I->see('Nuovo Compito', 'h1');
        
        $I->submitForm('#task-form', [
            'Task[title]' => 'New Test Task',
            'Task[description]' => 'Test Description',
            'Task[assigned_to]' => 'Tester',
            'Task[status]' => Task::STATUS_IN_PROGRESS
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
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();
        
        $I->amOnPage(['task/view', 'id' => $task->id]);
        $I->see('View Test Task', 'h1');
        $I->see('Secret Description');
    }

    public function indexHidesDescription(FunctionalTester $I)
    {
        $task = new Task();
        $task->loadDefaultValues();
        $task->title = 'Index Only Task';
        $task->description = 'Hidden Content';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        $I->amOnPage(['task/index']);
        $I->see('Index Only Task');
        $I->dontSee('Hidden Content');
    }
}
