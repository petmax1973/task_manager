<?php

use yii\db\Migration;

class m260304_150001_create_status_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('status', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(20)->notNull()->unique(),
            'name' => $this->string(255)->notNull(),
            'color' => $this->string(20)->notNull()->defaultValue('black'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Seed with existing statuses
        $now = time();
        $this->batchInsert('status', ['slug', 'name', 'color', 'sort_order', 'created_at'], [
            ['active', 'Active', '#007bff', 1, $now],
            ['to_release', 'To Release', 'red', 2, $now],
            ['in_progress', 'In Progress', 'green', 3, $now],
            ['in_review', 'In Review', '#FF8C00', 4, $now],
            ['suspended', 'Suspended', '#999', 5, $now],
            ['completed', 'Completed', 'black', 6, $now],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('status');
    }
}
