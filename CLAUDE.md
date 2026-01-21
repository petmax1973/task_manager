# Task Manager - Documentazione Tecnica Completa

## Overview del Progetto

Il **Task Manager** è un'applicazione web sviluppata con **Yii Framework 2.0** per la gestione di task/issue aziendali. L'applicazione supporta operazioni CRUD complete con funzionalità avanzate di sorting, filtering e aggiornamenti AJAX.

### Informazioni Generali
- **Framework**: Yii2 (v2.0.14+)  
- **Template**: yii2-app-basic modificato
- **Lingua predefinita**: Italiano (it-IT)
- **Database**: MySQL (task_manager)
- **PHP**: >= 5.6.0

---

## 1. Struttura del Progetto

### Cartelle Principali
```
task-manager/
├── assets/          # Bundle CSS/JS dell'applicazione
├── commands/        # Console commands
├── config/          # File di configurazione 
├── controllers/     # Controller Web
├── migrations/      # Database migrations
├── models/          # Modelli Active Record
├── views/           # View template
├── web/            # Entry point e risorse pubbliche
├── tests/          # Test suite (Codeception)
├── vendor/         # Dipendenze Composer
└── runtime/        # File temporanei e cache
```

### Configurazioni di Deployment
- **Docker**: `docker-compose.yml` (PHP 7.4 + Apache)
- **Vagrant**: `Vagrantfile` con Ubuntu 18.04
- **Ambiente di sviluppo**: Configurazione per debug e Gii

---

## 2. Database - Struttura e Migrations

### Tabella `task`
```sql
CREATE TABLE task (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'in_progress',
    priority INT NOT NULL DEFAULT 1,
    gitlab_issue VARCHAR(500),
    created_at INT NOT NULL,
    updated_at INT NOT NULL,
    INDEX idx-task-status (status)
);
```

### History delle Migrations
1. **m260109_091445_create_task_table**: Creazione tabella base con campi principali
2. **m260109_095437_add_priority_to_task_table**: Aggiunta campo priority (1-5)
3. **m260109_101500_seed_initial_tasks**: Seeding dati iniziali con task reali del progetto
4. **m260112_073919_add_gitlab_issue_to_task_table**: Aggiunta campo per link GitLab issues

---

## 3. Modelli (Models)

### Task Model (`app\models\Task`)

**Costanti Status**:
```php
const STATUS_IN_PROGRESS = 'in_progress';
const STATUS_IN_REVIEW = 'in_review'; 
const STATUS_SUSPENDED = 'suspended';
const STATUS_TO_RELEASE = 'to_release';
const STATUS_COMPLETED = 'completed';
```

**Behaviors**:
- `TimestampBehavior`: Gestione automatica created_at/updated_at

**Validazioni**:
- `title`: Obbligatorio, max 255 caratteri
- `description`: Testo libero
- `assigned_to`: Opzionale, max 255 caratteri
- `status`: Enum sui valori definiti, default 'in_progress'
- `priority`: Integer 1-5, default 1
- `gitlab_issue`: URL valido, max 500 caratteri

**Metodi Principali**:
- `getStatusList()`: Array associativo status => label localizzata
- `getStatusLabel()`: Label localizzata dello status corrente

**Attributi Internazionalizzati**:
Tutti i label sono gestiti tramite `Yii::t('app', 'Label')` per supporto multilingua.

### TaskSearch Model (`app\models\TaskSearch`)

**Funzionalità di Ricerca**:
- Ricerca testuale su title, description, assigned_to (LIKE)
- Filtro multiplo per status via `statusFilter`
- Filtro singolo per status specifico

**Ordinamento Personalizzato**:
```php
'status' => [
    'asc' => "FIELD(status, 'to_release', 'in_progress', 'in_review', 'suspended', 'completed')",
    'desc' => "FIELD(status, 'completed', 'suspended', 'in_review', 'in_progress', 'to_release')"
]
```

**Ordinamento Default**: `status ASC, priority DESC, created_at DESC`

---

## 4. Controller

### TaskController (`app\controllers\TaskController`)

**Azioni CRUD Standard**:
- `actionIndex()`: Lista paginata con ricerca e filtri
- `actionView($id)`: Visualizzazione dettaglio task
- `actionCreate()`: Creazione nuovo task
- `actionUpdate($id)`: Modifica task esistente  
- `actionDelete($id)`: Eliminazione task (POST only)

**Azioni AJAX**:
- `actionUpdatePriority()`: Aggiornamento priorità via dots interattivi
- `actionChangeStatus()`: Cambio status via dropdown

**Behaviors**:
- `VerbFilter`: Protezione DELETE e change-status solo via POST

### SiteController (`app\controllers\SiteController`)

**Funzionalità**:
- `actionIndex()`: Homepage
- `actionAbout()`: Pagina about
- `actionLanguage($lang)`: Cambio lingua (it-IT/en-US) via cookie

---

## 5. Views e UI

### Layout Principale (`views/layouts/main.php`)
- **Framework UI**: Bootstrap 4
- **Navbar**: Brand + switcher lingua
- **Breadcrumbs**: Navigazione automatica
- **Footer**: Copyright + Yii powered

### Task Index (`views/task/index.php`)

**Caratteristiche UI Avanzate**:

1. **GridView con Pjax**: Aggiornamenti senza reload pagina
2. **Color Coding per Status**:
   - `to_release`: Rosso
   - `in_progress`: Verde
   - `in_review`: Arancione (#FF8C00)
   - `suspended`: Grigio (#999)
   - `completed`: Nero

3. **Componenti Interattivi**:
   - **Priority Dots**: 5 pallini cliccabili (●○○○○ to ●●●●●)
   - **Status Dropdown**: Cambio status in tempo reale
   - **GitLab Button**: Link esterno a issue GitLab

4. **Filtri Avanzati**:
   - Filtro multiplo status (Select2 widget)
   - Ricerca testuale su campi principali
   - Filtro priority tramite visual dots

### Task Form (`views/task/_form.php`)
- Form completo con tutti i campi
- Textarea grande per description (26 rows)
- Priority dropdown con labels descrittive
- Campo assigned_to commentato (non utilizzato)

---

## 6. Configurazione

### Web Config (`config/web.php`)

**Configurazioni Chiave**:
```php
'name' => 'Task Manager',
'defaultRoute' => 'task/index',
'language' => 'it-IT',
```

**Cookie Language Switching**:
```php
'on beforeRequest' => function ($event) {
    if (Yii::$app->request->cookies->has('language')) {
        Yii::$app->language = Yii::$app->request->cookies->getValue('language');
    }
},
```

**Componenti Attivi**:
- **Pretty URLs**: Abilitati senza showScriptName
- **i18n**: PhpMessageSource per traduzioni
- **Debug + Gii**: Solo in ambiente dev

### Database Config (`config/db.php`)
```php
'dsn' => 'mysql:host=localhost;dbname=task_manager',
'username' => 'massimop', 
'password' => '12345678',
'charset' => 'utf8',
```

---

## 7. Internazionalizzazione (i18n)

### Lingue Supportate
- **Italiano (it-IT)**: Lingua predefinita
- **English (en-US)**: Lingua secondaria

### File Traduzioni
- `messages/it-IT/app.php`: Traduzioni italiane
- `messages/en-US/app.php`: Traduzioni inglesi

**Termini Principali Localizzati**:
- Task status labels
- Form labels e buttons
- Messaggi di sistema
- Navigation items

### Meccanismo Switching
Cookie persistente (30 giorni) gestito via `SiteController::actionLanguage()`

---

## 8. Dipendenze (composer.json)

### Dipendenze Core
```json
"yiisoft/yii2": "~2.0.14",
"yiisoft/yii2-bootstrap4": "~2.0.0", 
"yiisoft/yii2-swiftmailer": "~2.0.0 || ~2.1.0",
"kartik-v/yii2-widget-select2": "@dev"
```

### Dev Dependencies
- **Debug**: yii2-debug per barra sviluppo
- **Gii**: yii2-gii per code generation
- **Testing**: Codeception completo con moduli

### Asset Management
- **fxp-asset disabilitato**: Uso npm-asset e bower-asset via Composer
- **Aliases**: @bower, @npm configurati

---

## 9. Funzionalità Implementate

### 1. Gestione Task Completa
- ✅ CRUD completo (Create, Read, Update, Delete)
- ✅ Validazione server-side completa
- ✅ Timestamp automatici (created_at, updated_at)

### 2. Ricerca e Filtri Avanzati
- ✅ Ricerca testuale multi-campo
- ✅ Filtro multiplo per status
- ✅ Ordinamento personalizzato per business logic
- ✅ Paginazione automatica

### 3. UI/UX Avanzata
- ✅ Aggiornamenti AJAX senza reload
- ✅ Color coding per status visuale
- ✅ Priority dots interattivi (1-5)
- ✅ Status dropdown in-place editing

### 4. Integrazione Esterna
- ✅ Link diretti a GitLab Issues
- ✅ Validazione URL per gitlab_issue

### 5. Multilingual Support
- ✅ Switching lingua dinamico
- ✅ Persistenza via cookie
- ✅ Localizzazione completa UI

---

## 10. Testing

### Configurazione Codeception
- **Framework**: Codeception 4.x
- **Database Test**: Configurazione separata in `config/test.php`
- **Moduli**: Yii2, Filesystem, Asserts

### Test Funzionali (`tests/functional/TaskControllerCest.php`)
```php
// Test implementati:
- openIndexPage(): Verifica homepage task
- createNewTask(): Test creazione completa  
- viewTask(): Test visualizzazione dettaglio
- indexHidesDescription(): Verifica UI index (descrizione nascosta)
```

### Test Coverage Disponibile
Configurazione per code coverage su:
- models/*
- controllers/* 
- commands/*
- mail/*

---

## 11. Assets e Styling

### AppAsset (`assets/AppAsset.php`)
```php
'css' => ['css/site.css'],
'depends' => [
    'yii\web\YiiAsset',
    'yii\bootstrap4\BootstrapAsset',
]
```

### CSS Personalizzato (`web/css/site.css`)
- **Layout responsive**: Padding per navbar fixed-top
- **GridView styling**: Icone sorting personalizzate  
- **Footer styling**: Background e typography
- **Utility classes**: .not-set per valori vuoti

---

## 12. Peculiarità e Implementazioni Speciali

### 1. Business Logic Avanzata

**Ordinamento Status Prioritario**:
```sql
FIELD(status, 'to_release', 'in_progress', 'in_review', 'suspended', 'completed')
```
Status "to_release" ha priorità massima, seguito da "in_progress", etc.

**Priority Management**:
- Dots visivi interattivi (●●●○○)
- Update AJAX con feedback immediato
- Validazione range 1-5

### 2. AJAX Architecture

**Status Update Pattern**:
```javascript
// Immediate UI update + Server sync
dropdown.css('color', statusColors[newStatus]);
// AJAX call with revert on failure
```

**Priority Update Pattern**:
```javascript  
// Loading state + Optimistic update
container.html('<span style="opacity: 0.5;">⏳</span>');
// Success: render new dots
// Failure: revert to original
```

### 3. Data Seeding Realistico
Migration con task reali del progetto:
- Task "Da Rilasciare" con date specifiche
- Task "Sospese" per progetti in hold
- Task "In Lavorazione" operative

### 4. URL Strategy
- **Pretty URLs abilitati**
- **Default route**: `/task/index` invece di `/site/index`  
- **RESTful patterns**: `/task/create`, `/task/123/update`

### 5. Error Handling
- **NotFoundHttpException** per task inesistenti
- **CSRF protection** su tutte le azioni POST
- **VerbFilter** per sicurezza HTTP methods

---

## 13. Convenzioni di Sviluppo

### 1. Naming Conventions
- **Modelli**: PascalCase (`Task`, `TaskSearch`)
- **Azioni**: camelCase con prefisso `action` (`actionUpdatePriority`)
- **Campi DB**: snake_case (`created_at`, `gitlab_issue`)
- **Constanti**: UPPER_SNAKE_CASE (`STATUS_IN_PROGRESS`)

### 2. Code Organization
- **Controller thin**: Business logic nei modelli
- **View separation**: Form in _form.php parziali
- **Translation keys**: Consistenti tra lingue
- **Asset bundling**: Un bundle principale (AppAsset)

### 3. Security Patterns
- **CSRF tokens**: Su tutte le form e AJAX
- **Input validation**: Server-side obbligatoria
- **SQL injection**: Prevenzione via ActiveRecord
- **XSS prevention**: Html::encode() consistente

---

## 14. Deployment e Ambiente

### Opzioni di Deployment
1. **Docker**: `docker-compose up` (porta 8000)
2. **Vagrant**: Setup VM Ubuntu completo
3. **LAMP tradizionale**: Apache/Nginx + MySQL + PHP

### Environment Variables
- **YII_ENV**: dev/prod/test
- **YII_DEBUG**: true/false per debug mode
- **DB credentials**: Configurabili in `config/db.php`

### Performance Considerations
- **Schema cache**: Configurabile per production
- **File cache**: Default per session/cache
- **Index DB**: Su campo status per performance filtri

---

## 15. Future Development Notes

### Possibili Estensioni
1. **User Management**: Sistema auth per assigned_to
2. **File Attachments**: Upload documenti per task
3. **Comments System**: Thread discussioni per task
4. **Activity Log**: Storico modifiche task
5. **Dashboard**: Statistiche e grafici
6. **API REST**: Esposizione dati per integrazione
7. **Email Notifications**: Alert su cambio status
8. **Time Tracking**: Logging tempo lavoro

### Refactoring Opportunities
1. **Service Layer**: Estrarre business logic da controller
2. **Event System**: Eventi per azioni CRUD
3. **Caching**: Cache query complesse
4. **Validation**: Custom validators per business rules
5. **Background Jobs**: Task asincroni via queue

---

## 16. Troubleshooting Common Issues

### Database Connection
```bash
# Verifica connessione MySQL
mysql -u massimop -p task_manager
```

### Migration Issues
```bash
# Run pending migrations
./yii migrate
# Rollback last migration  
./yii migrate/down
```

### Asset Problems
```bash
# Clear asset cache
rm -rf web/assets/*
```

### Permission Issues
```bash
# Fix runtime permissions
chmod 777 runtime/ web/assets/
```

---

Questa documentazione fornisce una visione completa del progetto Task Manager, delle sue funzionalità, architettura e convenzioni di sviluppo. Può essere utilizzata come riferimento per manutenzione, estensioni future e onboarding di nuovi sviluppatori.