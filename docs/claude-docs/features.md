# Funzionalità del Progetto

Il Task Manager offre diverse caratteristiche avanzate per la gestione del lavoro e dell'interfaccia utente.

## Gestione dei Task

### Ciclo di Vita e Stati
I task seguono un flusso di lavoro predefinito:
- `active`: Task aperto, pronto per essere lavorato.
- `in_progress`: Task in corso d'opera.
- `in_review`: Task completato, in attesa di revisione.
- `suspended`: Task momentaneamente fermo.
- `to_release`: Task pronto per il rilascio.
- `completed`: Task chiuso definitivamente.

### Priorità
Ogni task ha una priorità indicata con un valore da 1 a 5 (1 è la priorità minima). Nella vista ad elenco, le priorità sono evidenziate con colori o icone specifiche.

### GitLab Integration
Ogni task può avere un link diretto a un'issue di GitLab (`gitlab_issue`). Questo permette di collegare rapidamente la gestione operativa al codice sorgente.

## Interfaccia Utente (UI) e UX

### Tema Dark / Light
Il progetto include un sistema di **Tema Dinamico** gestito tramite JavaScript (`web/js/theme.js`) e CSS variables.
- Il tema viene salvato in un cookie (`theme`) e persiste per 30 giorni.
- Un pulsante nell'header permette di passare istantaneamente tra modalità chiara e scura.
- Il sistema riconosce e rispetta le preferenze di sistema del browser al primo avvio.

### Markdown Support
Le descrizioni dei task e i contenuti dei tab supportano la sintassi **Markdown** (tramite la libreria `cebe/markdown`). Questo permette di inserire codice, tabelle e formattazione complessa.

### Descrizioni Multi-Tab
Invece di un'unica descrizione chilometrica, i task possono avere diversi tab. Questo è particolarmente utile per separare:
- Descrizione generale.
- Note tecniche.
- Log delle modifiche.
- Checklist di rilascio.

## Allegati e File
- Il sistema permette il caricamento di file multipli per ogni task.
- Gli allegati vengono salvati in una cartella esterna alla web root (per sicurezza) o serviti tramite controller.
- Viene mantenuta traccia del nome originale del file e della data di caricamento.
