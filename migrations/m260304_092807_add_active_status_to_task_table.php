<?php

use yii\db\Migration;

class m260304_092807_add_active_status_to_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Aggiunge supporto per il nuovo status 'active' (In Corso)
        // Il campo status è già VARCHAR quindi supporta il nuovo valore
        $this->addCommentOnColumn('task', 'status', 'Status del task: active, in_progress, in_review, suspended, to_release, completed');
        
        echo "Added support for 'active' status to task table.\n";
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Rimuove il commento aggiunto
        $this->addCommentOnColumn('task', 'status', 'Status del task');
        
        echo "Reverted active status support from task table.\n";
        
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260304_092807_add_active_status_to_task_table cannot be reverted.\n";

        return false;
    }
    */
}
