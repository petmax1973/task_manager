<?php

use yii\db\Migration;

class m260304_104318_add_project_to_task_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('task', 'project', $this->string(50)->null()->after('title'));
    }

    public function safeDown()
    {
        $this->dropColumn('task', 'project');
    }
}
