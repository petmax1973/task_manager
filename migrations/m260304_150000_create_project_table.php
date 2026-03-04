<?php

use yii\db\Migration;

class m260304_150000_create_project_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(50)->notNull()->unique(),
            'name' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Seed with existing projects
        $now = time();
        $this->batchInsert('project', ['slug', 'name', 'created_at'], [
            ['zotsell', 'Zotsell', $now],
            ['help', 'Help', $now],
            ['magento', 'Magento', $now],
            ['ebike', 'eBike', $now],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('project');
    }
}
