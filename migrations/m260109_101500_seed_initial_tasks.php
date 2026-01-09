<?php

use yii\db\Migration;

/**
 * Class m260109_101500_seed_initial_tasks
 */
class m260109_101500_seed_initial_tasks extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%task}}', ['title', 'description', 'status', 'created_at', 'updated_at'], [
            // DA RILASCIARE
            [
                'Verifica funzionamento setting enable_check_daily_sync', 
                "verificato, si può chiudere\naggiunta visualizzazione setting nella schermata di sincronizzazione, visulizzazione stato chron tramite presenza log ed icona info\nrilasciare al rientro dalle vacanze, rilascio lunedì 12 Gennaio 2026", 
                'to_release', 
                time(), 
                time()
            ],
            [
                'Proposta Vini | Sell | Gestione bedge per la chiamata fatto nel menu AREA PV', 
                "rilasciare lunedì 12 Gennaio 2026", 
                'to_release', 
                time(), 
                time()
            ],

            // SOSPESE
            [
                'Assistenza | Sassi | Implementazione di un form per la registrazione di nuovi clienti nel client BUY - Bozza di discussione', 
                '', 
                'suspended', 
                time(), 
                time()
            ],

            // IN LAVORAZIONE
            [
                'Assistenza | catalogue.polini.com | Problemi con simboli errati nel campo postilla quando si usano accenti o apostrofi', 
                '', 
                'in_progress', 
                time(), 
                time()
            ],
            [
                'Proposta vini - Progetto Integrazione ordini Quamm → Zotsell | 1.4. Settings per definizione campi e valori obbligatori', 
                '', 
                'in_progress', 
                time(), 
                time()
            ],
            [
                'Dismssione Setting | CRM --> Task8E Notify config --> task8e_notification_send_on_update', 
                "meglio fare la lavorazione dopo il merge della issue", 
                'in_progress', 
                time(), 
                time()
            ],
            [
                'Admin | settings Obsoleti | Rimozione di quest\'ultimi dalla lista', 
                "analizzare bene la logica, ok funziona correttamente", 
                'in_progress', 
                time(), 
                time()
            ],
            [
                'Sistemazione potenziale bug | Esportazione dati in tracciato ordine | gestione decimali a 2 posizioni', 
                "creato test per verifica --> fare il test", 
                'in_progress', 
                time(), 
                time()
            ],
            [
                'Zotsell | Swagger | Aggiungere link da panello Admn', 
                "posso fare questa lavorazione dopo aver mergiato il branch:", 
                'in_progress', 
                time(), 
                time()
            ],
            [
                'Assistenza | Enartissrl | Importazione csv Verifica setting elaborazioni e analisi tracciati', 
                "fatti i test", 
                'in_progress', 
                time(), 
                time()
            ],

            // COMPLETATE
            [
                'SQL injection in Zotsell | shareprocessresult', 
                "fatti i itest --> OK\nmerge fatto 15 dicembre 2025", 
                'completed', 
                time(), 
                time()
            ],
            [
                'Predisposizione swagger relativo ad api ad uso applicazione IOS', 
                "merge fatto 15 dicembre 2025", 
                'completed', 
                time(), 
                time()
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%task}}');
    }
}
