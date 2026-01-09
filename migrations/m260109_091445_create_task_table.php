<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m260109_091445_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'assigned_to' => $this->string(255),
            'status' => $this->string(20)->notNull()->defaultValue('in_progress'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for status column for better performance on filtering
        $this->createIndex(
            'idx-task-status',
            '{{%task}}',
            'status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-task-status', '{{%task}}');
        $this->dropTable('{{%task}}');
    }
}
