<?php

use yii\db\Migration;

class m260305_135252_add_related_tasks_to_task_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('task', 'related_tasks', $this->string(255)->defaultValue(null)->after('gitlab_issue'));
    }

    public function safeDown()
    {
        $this->dropColumn('task', 'related_tasks');
    }
}
