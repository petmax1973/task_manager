# Modello Dati

Il cuore dell'applicazione è basato sulle tabelle MySQL gestite tramite modelli `ActiveRecord`. 

## Tabelle Principali

### Task (`task`)
Mappa l'entità principale del sistema.
- **id**: Identificativo univoco (PK).
- **title**: Titolo del task (Obbligatorio).
- **description**: Descrizione estesa.
- **assigned_to**: Nome dell'assegnatario (Stringa, validata contro la tabella `assignee`).
- **status**: Stato attuale (Validato contro `status`).
- **priority**: Priorità (1-5, default 1).
- **project**: Identificativo del progetto (Validato contro `project`).
- **gitlab_issue**: Link URL a GitLab (Opzionale).
- **related_tasks**: Elenco separato da virgole di ID di task correlati.
- **created_at**, **updated_at**: Timestamp Unix gestiti automaticamente.

### Project (`project`)
Gestisce i progetti disponibili.
- **id**: Codice breve del progetto (PK, es. 'SITO').
- **name**: Nome descrittivo.

### Assignee (`assignee`)
Gestisce le persone a cui possono essere assegnati i task.
- **id**: PK.
- **name**: Nome (Univoco).
L'assegnazione nei task avviene per nome. Se viene inserito un nome non presente nel database durante il salvataggio di un task, il sistema lo crea automaticamente tramite `Assignee::ensureExists()`.

### Status (`status`)
Definisce gli stati possibili dei task (es. 'in_progress', 'completed').

## Componenti Aggiuntivi del Task

### TaskDescriptionTab (`task_description_tab`)
Permette di avere descrizioni multiple organizzate in tab all'interno della vista del task.
- **task_id**: FK verso `task`.
- **tab_title**: Titolo del tab.
- **tab_content**: Contenuto (Markdown supportato).
- **sort_order**: Ordinamento visuale.

### TaskAttachment (`task_attachment`)
Gestisce gli allegati (immagini, documenti) caricati per ogni task.
- **task_id**: FK verso `task`.
- **file_path**: Percorso del file nel filesystem.
- **file_name**: Nome originale del file.

## Relazioni Bidirezionali ("Related Tasks")

Il sistema implementa una logica di **Correlazione Bidirezionale** per i task:
- Quando viene aggiunto l'ID `B` nel campo `related_tasks` del task `A`, il sistema aggiunge automaticamente l'ID `A` nel campo `related_tasks` del task `B`.
- Questa sincronizzazione avviene nel metodo `afterSave` tramite `syncRelatedTasks()`.
- La rimozione di una correlazione agisce in modo speculare.
- È presente una validazione (`validateRelatedTasks`) per evitare auto-riferimenti e riferimenti a task inesistenti.
