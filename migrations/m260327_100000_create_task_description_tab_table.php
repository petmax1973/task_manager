<?php

use yii\db\Migration;

class m260327_100000_create_task_description_tab_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('task_description_tab', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull()->defaultValue(''),
            'content' => 'LONGTEXT',
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex(
            'idx-task_description_tab-task_id-sort_order',
            'task_description_tab',
            ['task_id', 'sort_order']
        );

        $this->addForeignKey(
            'fk-task_description_tab-task_id',
            'task_description_tab',
            'task_id',
            'task',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Migrate existing descriptions into the new table
        $tasks = (new \yii\db\Query())
            ->select(['id', 'description'])
            ->from('task')
            ->where(['not', ['description' => null]])
            ->andWhere(['not', ['description' => '']])
            ->all();

        foreach ($tasks as $task) {
            $this->insert('task_description_tab', [
                'task_id' => $task['id'],
                'title' => '',
                'content' => $task['description'],
                'sort_order' => 0,
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTable('task_description_tab');
    }
}
