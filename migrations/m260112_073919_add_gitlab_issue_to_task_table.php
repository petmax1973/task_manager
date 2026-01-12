<?php

use yii\db\Migration;

class m260112_073919_add_gitlab_issue_to_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%task}}', 'gitlab_issue', $this->string(500)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%task}}', 'gitlab_issue');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260112_073919_add_gitlab_issue_to_task_table cannot be reverted.\n";

        return false;
    }
    */
}
