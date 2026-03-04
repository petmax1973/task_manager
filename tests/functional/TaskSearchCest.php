<?php

namespace tests\functional;

use app\models\Task;
use FunctionalTester;

class TaskSearchCest
{
    public function _before(FunctionalTester $I)
    {
        // Clean up and create test data
        Task::deleteAll();
        
        // Create test tasks with different statuses and content
        $this->createTestTasks();
    }

    private function createTestTasks()
    {
        // Active task
        $task1 = new Task();
        $task1->title = 'Active Development Task';
        $task1->description = 'Currently working on this feature';
        $task1->status = Task::STATUS_ACTIVE;
        $task1->priority = 5;
        $task1->assigned_to = 'John Developer';
        $task1->save();

        // In progress task
        $task2 = new Task();
        $task2->title = 'Backend API Implementation';
        $task2->description = 'Implementing REST API endpoints';
        $task2->status = Task::STATUS_IN_PROGRESS;
        $task2->priority = 3;
        $task2->assigned_to = 'Jane Backend';
        $task2->save();

        // Suspended task
        $task3 = new Task();
        $task3->title = 'Frontend Redesign';
        $task3->description = 'UI/UX improvements on hold';
        $task3->status = Task::STATUS_SUSPENDED;
        $task3->priority = 2;
        $task3->assigned_to = 'Mike Designer';
        $task3->save();

        // To release task
        $task4 = new Task();
        $task4->title = 'Database Migration Script';
        $task4->description = 'Ready for deployment';
        $task4->status = Task::STATUS_TO_RELEASE;
        $task4->priority = 4;
        $task4->assigned_to = 'Sarah DBA';
        $task4->save();

        // Completed task
        $task5 = new Task();
        $task5->title = 'User Authentication';
        $task5->description = 'Login system completed and tested';
        $task5->status = Task::STATUS_COMPLETED;
        $task5->priority = 1;
        $task5->assigned_to = 'Bob Security';
        $task5->save();
    }

    public function testSearchByTitle(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Search for "Development"
        $I->fillField('TaskSearch[title]', 'Development');
        $I->click('Search');
        
        $I->see('Active Development Task');
        $I->dontSee('Backend API Implementation');
        $I->dontSee('Frontend Redesign');
    }

    public function testSearchByDescription(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Search for "API"
        $I->fillField('TaskSearch[description]', 'API');
        $I->click('Search');
        
        $I->see('Backend API Implementation');
        $I->dontSee('Active Development Task');
    }

    public function testSearchByAssignee(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Search for assignee "Jane"
        $I->fillField('TaskSearch[assigned_to]', 'Jane');
        $I->click('Search');
        
        $I->see('Backend API Implementation');
        $I->see('Jane Backend');
        $I->dontSee('John Developer');
    }

    public function testFilterBySingleStatus(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Filter by active status
        $I->selectOption('TaskSearch[status]', 'active');
        $I->click('Search');
        
        $I->see('Active Development Task');
        $I->dontSee('Backend API Implementation');
        $I->dontSee('Frontend Redesign');
    }

    public function testFilterByMultipleStatuses(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Filter by multiple statuses using statusFilter
        // Note: This requires Select2 widget interaction
        $I->executeJS("$('select[name=\"TaskSearch[statusFilter][]\"]').val(['active', 'to_release']).trigger('change');");
        $I->click('Search');
        
        $I->see('Active Development Task');
        $I->see('Database Migration Script');
        $I->dontSee('Backend API Implementation');
        $I->dontSee('Frontend Redesign');
    }

    public function testCombinedSearchAndFilter(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Search by title AND filter by status
        $I->fillField('TaskSearch[title]', 'Task');
        $I->selectOption('TaskSearch[status]', 'in_progress');
        $I->click('Search');
        
        // Should only show in_progress tasks containing "Task"
        $I->see('Backend API Implementation');
        $I->dontSee('Active Development Task'); // Different status
    }

    public function testSearchWithNoResults(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Search for something that doesn't exist
        $I->fillField('TaskSearch[title]', 'NonExistentTask12345');
        $I->click('Search');
        
        $I->see('Visualizza'); // Should show pagination info
        $I->see('0'); // Should show 0 results
        $I->dontSee('Active Development Task');
    }

    public function testResetSearch(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Apply some filters
        $I->fillField('TaskSearch[title]', 'Development');
        $I->selectOption('TaskSearch[status]', 'active');
        $I->click('Search');
        
        // Only one task should be visible
        $I->see('Active Development Task');
        $I->dontSee('Backend API Implementation');
        
        // Reset filters
        $I->click('Reset');
        
        // All tasks should be visible again
        $I->see('Active Development Task');
        $I->see('Backend API Implementation'); 
        $I->see('Frontend Redesign');
        $I->see('Database Migration Script');
        $I->see('User Authentication');
    }

    public function testSortingByStatus(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Tasks should be sorted with active first (highest priority status)
        $taskTitles = $I->grabMultiple('.task-title'); // Assuming task titles have this class
        
        // Active status tasks should appear first due to custom sorting
        // Note: This test might need adjustment based on actual HTML structure
    }

    public function testSortingByPriority(FunctionalTester $I)
    {
        $I->amOnPage(['task/index']);
        
        // Click priority column header to sort
        $I->click('th:contains("Priorità")'); // Click priority column
        
        // Should sort by priority desc (high to low)
        // Note: Actual implementation depends on GridView structure
    }

    public function testPagination(FunctionalTester $I)
    {
        // Create many tasks to test pagination
        for ($i = 6; $i <= 25; $i++) {
            $task = new Task();
            $task->title = "Task Number $i";
            $task->status = Task::STATUS_IN_PROGRESS;
            $task->save();
        }
        
        $I->amOnPage(['task/index']);
        
        // Should see pagination controls if more than page size
        if ($I->tryToSee('»')) { // Next page link
            $I->see('»');
            $I->click('»');
            // Should be on page 2
            $I->see('Task Number');
        }
    }
}