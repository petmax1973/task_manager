<?php

use yii\db\Migration;

/**
 * Creates the `assignee` table and seeds it with existing assigned_to values from task table.
 */
class m260220_100000_create_assignee_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('assignee', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Seed with existing distinct assigned_to values from task table
        $names = (new \yii\db\Query())
            ->select('assigned_to')
            ->distinct()
            ->from('task')
            ->where(['not', ['assigned_to' => null]])
            ->andWhere(['not', ['assigned_to' => '']])
            ->column();

        $now = time();
        foreach ($names as $name) {
            $this->insert('assignee', [
                'name' => $name,
                'created_at' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTable('assignee');
    }
}
