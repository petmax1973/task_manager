# Architettura del Progetto

Questo progetto è basato sul framework **Yii 2 Basic Application Template**, un framework PHP ad alte prestazioni che segue il pattern architetturale **MVC (Model-View-Controller)**.

## Struttura delle Cartelle

- **assets/**: Contiene le definizioni degli asset bundle (JS/CSS). `AppAsset.php` gestisce il caricamento del tema e dei file core.
- **commands/**: Contiene classi per i comandi CLI (Yii console commands).
- **config/**: File di configurazione dell'applicazione (database, web, console, parametri).
- **controllers/**: Contiene le classi Controller che gestiscono le richieste HTTP:
  - `TaskController`: Gestisce il ciclo di vita dei task.
  - `SettingsController`: Gestisce le impostazioni globali.
  - `SiteController`: Gestisce le pagine statiche e l'autenticazione.
- **models/**: Contiene i modelli ActiveRecord per l'interazione con il database e i modelli per la validazione dei form (es. `LoginForm`).
- **runtime/**: Cartella temporanea per log, debug e file generati durante l'esecuzione (Ignorata da Git).
- **tests/**: Suite di test automatizzati (Codeception).
- **vendor/**: Librerie esterne gestite tramite Composer (Ignorata da Git).
- **views/**: Template PHP per l'interfaccia utente, suddivisi per controller.
- **web/**: Punto di ingresso Web (`index.php`), contiene file statici pubblici (immagini, CSS, JS).

## Pattern MVC e Componenti

### Modelli (ActiveRecord)
I modelli estendono `yii\db\ActiveRecord` e mappano direttamente le tabelle del database. Utilizzano i `behaviors` (come `TimestampBehavior`) per gestire automaticamente i campi `created_at` e `updated_at`.

### Controller e Action
Le logiche di business sono incapsulate all'interno delle `action` dei controller. Il routing di Yii2 mappa gli URL nel formato `controller/action`.

### View e Layout
Il layout principale si trova in `views/layouts/main.php`. Le view utilizzano i **Yii2 Widgets** (come `GridView` e `DetailView`) per una visualizzazione rapida e coerente dei dati.

## Ciclo di Vita della Richiesta
1. La richiesta arriva a `web/index.php`.
2. Viene caricata la configurazione dell'applicazione.
3. Il `Request Handler` risolve la rotta.
4. Il Controller viene istanziato e l'azione eseguita.
5. Il Controller popola i modelli e renderizza la View.
6. La View viene inserita nel Layout e restituita al browser.
