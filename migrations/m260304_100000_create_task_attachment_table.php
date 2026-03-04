<?php

use yii\db\Migration;

/**
 * Handles the creation of table `task_attachment`.
 */
class m260304_100000_create_task_attachment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('task_attachment', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'original_name' => $this->string(255)->notNull(),
            'stored_name' => $this->string(255)->notNull(),
            'mime_type' => $this->string(100),
            'file_size' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-task_attachment-task_id',
            'task_attachment',
            'task_id'
        );

        $this->addForeignKey(
            'fk-task_attachment-task_id',
            'task_attachment',
            'task_id',
            'task',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-task_attachment-task_id', 'task_attachment');
        $this->dropIndex('idx-task_attachment-task_id', 'task_attachment');
        $this->dropTable('task_attachment');
    }
}
