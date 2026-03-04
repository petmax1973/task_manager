<?php

namespace tests\unit\models;

use app\models\Task;
use app\models\TaskSearch;
use Codeception\Test\Unit;

class TaskSearchTest extends Unit
{
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testSearchByTitle()
    {
        $searchModel = new TaskSearch();
        $searchModel->title = 'test';
        
        $dataProvider = $searchModel->search([]);
        
        $this->assertInstanceOf('yii\data\ActiveDataProvider', $dataProvider);
    }

    public function testSearchByDescription()
    {
        $searchModel = new TaskSearch();
        $searchModel->description = 'description';
        
        $dataProvider = $searchModel->search([]);
        
        $this->assertInstanceOf('yii\data\ActiveDataProvider', $dataProvider);
    }

    public function testFilterBySingleStatus()
    {
        $searchModel = new TaskSearch();
        $searchModel->status = Task::STATUS_ACTIVE;
        
        $dataProvider = $searchModel->search([]);
        
        $this->assertInstanceOf('yii\data\ActiveDataProvider', $dataProvider);
    }

    public function testFilterByMultipleStatuses()
    {
        $searchModel = new TaskSearch();
        $searchModel->statusFilter = [Task::STATUS_ACTIVE, Task::STATUS_TO_RELEASE];
        
        $dataProvider = $searchModel->search([]);
        
        $this->assertInstanceOf('yii\data\ActiveDataProvider', $dataProvider);
    }

    public function testCombinedSearchAndFilter()
    {
        $searchModel = new TaskSearch();
        $searchModel->title = 'test';
        $searchModel->statusFilter = [Task::STATUS_ACTIVE];
        
        $dataProvider = $searchModel->search([]);
        
        $this->assertInstanceOf('yii\data\ActiveDataProvider', $dataProvider);
    }
}
