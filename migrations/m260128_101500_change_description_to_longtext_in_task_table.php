<?php

use yii\db\Migration;

/**
 * Class m260128_101500_change_description_to_longtext_in_task_table
 *
 * Modifica il campo description della tabella task da TEXT a LONGTEXT
 * per consentire la memorizzazione di descrizioni molto lunghe.
 */
class m260128_101500_change_description_to_longtext_in_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Usa un ColumnSchemaBuilder esplicito per il tipo LONGTEXT (MySQL)
        $this->alterColumn(
            '{{%task}}',
            'description',
            $this->getDb()->getSchema()->createColumnSchemaBuilder('LONGTEXT')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Torna al tipo TEXT originale definito nella migration iniziale
        $this->alterColumn(
            '{{%task}}',
            'description',
            $this->getDb()->getSchema()->createColumnSchemaBuilder('TEXT')
        );
    }
}

