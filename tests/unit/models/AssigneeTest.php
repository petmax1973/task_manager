<?php

namespace tests\unit\models;

use app\models\Assignee;
use Codeception\Test\Unit;

class AssigneeTest extends Unit
{
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testAssigneeValidation()
    {
        $assignee = new Assignee();
        
        // Name is required
        $assignee->name = null;
        $this->assertFalse($assignee->validate(['name']));
        
        // Name should be string max 255
        $assignee->name = str_repeat('a', 256);
        $this->assertFalse($assignee->validate(['name']));
        
        $assignee->name = 'Valid Name';
        $this->assertTrue($assignee->validate(['name']));
    }

    public function testUniqueNameValidation()
    {
        // Create first assignee
        $assignee1 = new Assignee();
        $assignee1->name = 'John Doe';
        $this->assertTrue($assignee1->save());
        
        // Try to create second assignee with same name
        $assignee2 = new Assignee();
        $assignee2->name = 'John Doe';
        $this->assertFalse($assignee2->validate(['name']));
        $this->assertTrue($assignee2->hasErrors('name'));
    }

    public function testEnsureExists()
    {
        $initialCount = Assignee::find()->count();
        
        // Test with new assignee name
        $assignee1 = Assignee::ensureExists('New Assignee');
        $this->assertInstanceOf(Assignee::class, $assignee1);
        $this->assertEquals('New Assignee', $assignee1->name);
        $this->assertEquals($initialCount + 1, Assignee::find()->count());
        
        // Test with existing assignee name - should return existing
        $assignee2 = Assignee::ensureExists('New Assignee');
        $this->assertEquals($assignee1->id, $assignee2->id);
        $this->assertEquals($initialCount + 1, Assignee::find()->count());
    }

    public function testGetList()
    {
        // Clear existing assignees
        Assignee::deleteAll();
        
        // Create test assignees
        $assignee1 = new Assignee();
        $assignee1->name = 'Alice Smith';
        $assignee1->save();
        
        $assignee2 = new Assignee();
        $assignee2->name = 'Bob Johnson';
        $assignee2->save();
        
        $list = Assignee::getList();
        
        $this->assertIsArray($list);
        $this->assertCount(2, $list);
        $this->assertArrayHasKey($assignee1->id, $list);
        $this->assertArrayHasKey($assignee2->id, $list);
        $this->assertEquals('Alice Smith', $list[$assignee1->id]);
        $this->assertEquals('Bob Johnson', $list[$assignee2->id]);
    }

    public function testTimestampBehavior()
    {
        $assignee = new Assignee();
        $assignee->name = 'Timestamp Test';
        
        $this->assertTrue($assignee->save());
        $this->assertNotNull($assignee->created_at);
        $this->assertIsInt($assignee->created_at);
    }

    public function testAttributeLabels()
    {
        $assignee = new Assignee();
        $labels = $assignee->attributeLabels();
        
        $this->assertIsArray($labels);
        $this->assertArrayHasKey('name', $labels);
        $this->assertArrayHasKey('id', $labels);
    }
}