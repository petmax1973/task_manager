# Task Manager - Project Guide

Benvenuto nel progetto Task Manager basato su **Yii 2**. Questa guida serve come punto di ingresso per gli sviluppatori e per l'IA (Claude).

## Comandi di Sviluppo Rapidi

### Ambiente e Server
- **Avvio Server**: `php yii serve` (Default su http://localhost:8080)
- **Configurazione DB**: Controlla `config/db.php`
- **Gii (Generatore Codice)**: Accessibile in ambiente dev su `index.php?r=gii`

### Dipendenze e Pacchetti
- **Installazione**: `composer install`
- **Aggiornamento**: `composer update`

### Test Automatizzati (Codeception)
- **Esegui tutti i test**: `./vendor/bin/codecept run`
- **Esegui test unitari**: `./vendor/bin/codecept run unit`
- **Esegui test funzionali**: `./vendor/bin/codecept run functional`

## Documentazione Dettagliata

Per approfondire parti specifiche del codice e dell'architettura, consulta la documentazione in `docs/claude-docs/`:

1. [**Architettura del Progetto**](file:///Applications/MAMP/htdocs/task_manager/task-manager/docs/claude-docs/architecture.md): Struttura cartelle, MVC e ciclo di vita della richiesta.
2. [**Modello Dati**](file:///Applications/MAMP/htdocs/task_manager/task-manager/docs/claude-docs/data-model.md): Tabelle database, modelli ActiveRecord e relazioni bidirezionali.
3. [**Funzionalità Principali**](file:///Applications/MAMP/htdocs/task_manager/task-manager/docs/claude-docs/features.md): Gestione task, allegati, tab multipli e sistema dei temi.

## Convenzioni di Codice

- **Framework**: Yii 2.0 (Basic Template).
- **Stile**: Seguire lo standard PSR-12.
- **Traduzioni**: Utilizzare `Yii::t('app', 'Stringa')` per tutte le stringhe nell'interfaccia.
- **Timestamp**: Usare `TimestampBehavior` nei modelli per `created_at` e `updated_at`.
- **Tema**: Usare le variabili CSS definite in `theme.js` per garantire la compatibilità con la Dark Mode.