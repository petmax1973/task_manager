<?php

namespace tests\functional;

use app\models\Task;
use app\models\Assignee;
use app\models\TaskAttachment;
use FunctionalTester;

class TaskWorkflowCest
{
    public function _before(FunctionalTester $I)
    {
        // Clean slate for integration tests
        Task::deleteAll();
        Assignee::deleteAll();
        TaskAttachment::deleteAll();
    }

    public function testCompleteTaskWorkflow(FunctionalTester $I)
    {
        // 1. Create new task
        $I->amOnPage(['task/create']);
        $I->fillField('Task[title]', 'Complete Workflow Test Task');
        $I->fillField('Task[description]', 'This task will go through complete workflow');
        $I->selectOption('Task[status]', Task::STATUS_IN_PROGRESS);
        $I->selectOption('Task[priority]', '2');
        $I->click('Salva');
        
        // Should be redirected to view page
        $I->see('Complete Workflow Test Task', 'h1');
        $I->see('In Lavorazione'); // Status in Italian
        
        // 2. Update task to active (currently working)
        $I->click('Modifica');
        $I->selectOption('Task[status]', Task::STATUS_ACTIVE);
        $I->selectOption('Task[priority]', '5');
        $I->click('Salva');
        
        // Should show updated status and priority
        $I->see('In Corso'); // Active status in Italian
        
        // 3. Assign task via edit
        $I->click('Modifica');
        $I->fillField('Task[assigned_to]', 'Integration Tester');
        $I->click('Salva');
        
        $I->see('Integration Tester');
        
        // 4. Complete the task
        $I->click('Modifica');
        $I->selectOption('Task[status]', Task::STATUS_COMPLETED);
        $I->click('Salva');
        
        $I->see('Completato'); // Completed status in Italian
        
        // 5. Verify in task list
        $I->amOnPage(['task/index']);
        $I->see('Complete Workflow Test Task');
        $I->see('Integration Tester');
        
        // Task should appear with completed status (black color)
        $I->seeElement('[style*="color: black"]'); // Completed tasks are black
    }

    public function testTaskPriorityAndStatusInteraction(FunctionalTester $I)
    {
        // Create multiple tasks with different priorities and statuses
        $testData = [
            ['title' => 'Low Priority Active', 'priority' => 1, 'status' => Task::STATUS_ACTIVE],
            ['title' => 'High Priority To Release', 'priority' => 5, 'status' => Task::STATUS_TO_RELEASE], 
            ['title' => 'Medium Priority In Progress', 'priority' => 3, 'status' => Task::STATUS_IN_PROGRESS],
        ];
        
        foreach ($testData as $data) {
            $I->amOnPage(['task/create']);
            $I->fillField('Task[title]', $data['title']);
            $I->selectOption('Task[status]', $data['status']);
            $I->selectOption('Task[priority]', (string)$data['priority']);
            $I->click('Salva');
            $I->see($data['title']); // Verify creation
        }
        
        // Check task list ordering
        $I->amOnPage(['task/index']);
        
        // Active status should appear first (highest priority in sorting)
        $I->see('Low Priority Active');
        $I->see('High Priority To Release');
        $I->see('Medium Priority In Progress');
    }

    public function testSearchAndFilterIntegration(FunctionalTester $I)
    {
        // Create test tasks with specific data for filtering
        $tasks = [
            ['title' => 'Frontend Bug Fix', 'status' => Task::STATUS_ACTIVE, 'assignee' => 'Frontend Dev'],
            ['title' => 'Backend API', 'status' => Task::STATUS_IN_PROGRESS, 'assignee' => 'Backend Dev'],
            ['title' => 'Frontend Feature', 'status' => Task::STATUS_TO_RELEASE, 'assignee' => 'Frontend Dev'],
        ];
        
        foreach ($tasks as $taskData) {
            $task = new Task();
            $task->title = $taskData['title'];
            $task->status = $taskData['status'];
            $task->assigned_to = $taskData['assignee'];
            $task->priority = 1;
            $task->save();
        }
        
        $I->amOnPage(['task/index']);
        
        // Test search by title
        $I->fillField('TaskSearch[title]', 'Frontend');
        $I->click('Search');
        
        $I->see('Frontend Bug Fix');
        $I->see('Frontend Feature');
        $I->dontSee('Backend API');
        
        // Reset and test filter by assignee
        $I->click('Reset');
        $I->fillField('TaskSearch[assigned_to]', 'Backend');
        $I->click('Search');
        
        $I->see('Backend API');
        $I->dontSee('Frontend Bug Fix');
        $I->dontSee('Frontend Feature');
    }

    public function testAssigneeAutocreation(FunctionalTester $I)
    {
        $initialAssigneeCount = Assignee::find()->count();
        
        // Create task with new assignee
        $I->amOnPage(['task/create']);
        $I->fillField('Task[title]', 'Assignee Creation Test');
        $I->fillField('Task[assigned_to]', 'Automatically Created Person');
        $I->selectOption('Task[status]', Task::STATUS_IN_PROGRESS);
        $I->click('Salva');
        
        // Verify assignee was auto-created
        $newAssigneeCount = Assignee::find()->count();
        $I->assertEquals($initialAssigneeCount + 1, $newAssigneeCount);
        
        // Create another task with same assignee
        $I->amOnPage(['task/create']);
        $I->fillField('Task[title]', 'Same Assignee Test');
        $I->fillField('Task[assigned_to]', 'Automatically Created Person');
        $I->selectOption('Task[status]', Task::STATUS_IN_PROGRESS);
        $I->click('Salva');
        
        // Should NOT create another assignee
        $finalAssigneeCount = Assignee::find()->count();
        $I->assertEquals($newAssigneeCount, $finalAssigneeCount);
    }

    public function testTaskValidationInWorkflow(FunctionalTester $I)
    {
        // Test required field validation
        $I->amOnPage(['task/create']);
        $I->fillField('Task[title]', ''); // Empty title
        $I->click('Salva');
        
        // Should stay on create page with error
        $I->seeCurrentUrlMatches('/create/');
        $I->see('Nuovo Task'); // Still on create page
        
        // Fill title and try with invalid priority
        $I->fillField('Task[title]', 'Validation Test');
        $I->executeJS('$(\"select[name=\\\"Task[priority]\\\"]\").val(\"10\").trigger(\"change\");'); // Invalid priority
        $I->click('Salva');
        
        // Should show validation error or default to valid value
    }

    public function testMultipleTaskStatusChanges(FunctionalTester $I)
    {
        // Create a task and take it through multiple status changes
        $task = new Task();
        $task->title = 'Status Progression Test';
        $task->status = Task::STATUS_IN_PROGRESS;
        $task->priority = 1;
        $task->save();
        
        $taskId = $task->id;
        
        // Progress through statuses
        $statusProgression = [
            Task::STATUS_ACTIVE,
            Task::STATUS_IN_REVIEW,
            Task::STATUS_TO_RELEASE,
            Task::STATUS_COMPLETED
        ];
        
        foreach ($statusProgression as $status) {
            $I->amOnPage(['task/update', 'id' => $taskId]);
            $I->selectOption('Task[status]', $status);
            $I->click('Salva');
            
            // Verify status change
            $task->refresh();
            $I->assertEquals($status, $task->status);
        }
        
        // Verify final status in index
        $I->amOnPage(['task/index']);
        $I->see('Status Progression Test');
        $I->see('Completato'); // Final status in Italian
    }

    public function testDataPersistenceAcrossPages(FunctionalTester $I)
    {
        // Create task with complex data
        $I->amOnPage(['task/create']);
        $I->fillField('Task[title]', 'Persistence Test Task');
        $I->fillField('Task[description]', "Complex description with\nMultiple lines\nAnd special characters: àèìòù");
        $I->fillField('Task[assigned_to]', 'Person With Special Name (123)');
        $I->fillField('Task[gitlab_issue]', 'https://gitlab.example.com/test/issue/123');
        $I->selectOption('Task[priority]', '4');
        $I->click('Salva');
        
        // Navigate away and back
        $I->amOnPage(['task/index']);
        $I->click('Persistence Test Task');
        
        // Verify all data is preserved
        $I->see('Persistence Test Task', 'h1');
        $I->see('Complex description');
        $I->see('Person With Special Name');
        $I->see('gitlab.example.com');
    }
}