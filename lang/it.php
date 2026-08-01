<?php
// lang/it.php - Italian (Italiano)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Accedi',
    'nav.logout'                 => 'Esci',
    'nav.feedback'               => 'Feedback',
    'nav.volunteer'              => 'Diventa Volontario',
    'nav.leaderboard'            => 'Classifica',
    'nav.search'                 => 'Cerca',
    'nav.settings'               => 'Impostazioni di Sistema',
    'nav.high_contrast'          => 'Alto Contrasto',
    'nav.low_contrast'           => 'Basso Contrasto',
    'nav.welcome'                => 'Benvenuto,',
    'nav.data_entry'             => 'Inserimento Dati',
    'nav.moderation'             => 'Moderazione',
    'nav.invite_user'            => 'Invita Utente',
    'nav.manage_users'           => 'Gestisci Utenti',
    'nav.manage_tables'          => 'Gestisci Tabelle',
    'nav.volunteer_dashboard'    => 'Pannello Volontari',
    'nav.feedback_dashboard'     => 'Pannello Feedback',
    'nav.leaderboard_score'      => 'Punteggio Classifica',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Filtri di Ricerca Multicolonna',
    'search.reset'               => 'Reimposta Ricerca',
    'search.export_csv'          => 'Esporta Risultati Filtrati come CSV',
    'search.no_records'          => 'Nessun record trovato in questa directory.',
    'search.load_error'          => 'Errore durante il caricamento dei risultati. Riprova.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Invia',
    'btn.cancel'                 => 'Annulla',
    'btn.save'                   => 'Salva',
    'btn.delete'                 => 'Elimina',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Grazie! Il tuo feedback è stato inviato con successo.',
    'feedback.error_all_fields'   => 'Compila tutti i campi.',
    'feedback.error_invalid_email'=> 'Inserisci un indirizzo email valido.',
    'feedback.error_save_failed'  => 'Si è verificato un errore durante il salvataggio del feedback. Riprova.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Nessuna Tabella del Database Trovata',
    'index.no_tables_desc'             => 'Al momento non ci sono tabelle del database attive configurate nel sistema.',
    'index.admin_create_table_guide'   => 'Come amministratore, vai su <strong>Gestisci Tabelle</strong> per creare una tabella e aggiungere almeno una colonna prima di visualizzare o inserire record.',
    'index.go_to_manage_tables'        => 'Vai a Gestisci Tabelle',
    'index.contact_admin_tables'       => 'Contatta un amministratore per configurare le tabelle e le colonne del database.',
    'index.guest_login_tables_guide'   => 'Per favore, <a href=":login_link">accedi</a> o contatta l\'amministratore per configurare le tabelle.',
    'index.no_columns_heading'         => 'Nessuna Colonna Configurata',
    'index.no_columns_desc'            => 'Ci sono tabelle nel sistema, ma non è stata definita alcuna colonna di dati per la tabella attiva.',
    'index.admin_add_columns_guide'    => 'Come amministratore, vai su <strong>Gestisci Tabelle</strong> per aggiungere almeno una colonna alla tua tabella.',
    'index.contact_admin_columns'      => 'Contatta l\'amministratore per configurare le colonne di questa tabella.',
    'index.select_directory_database'  => 'Seleziona il database della directory:',
    'index.opt_yes_true'               => 'Sì / Vero',
    'index.opt_no_false'               => 'No / Falso',
    'index.opt_male'                   => 'Maschio',
    'index.opt_female'                 => 'Femmina',
    'index.opt_true'                   => 'Vero',
    'index.opt_false'                  => 'Falso',
    'index.opt_tick'                   => '✔ (Spuntato)',
    'index.opt_cross'                  => '✘ (Croce)',
    'index.option_all'                 => '-- Tutti --',
    'index.date_to_label'              => 'a',
    'index.search_placeholder'         => 'Cerca...',
    'index.download_entire_csv'        => 'Scarica CSV Completo',
    'index.download_entire_json'       => 'Scarica JSON Completo',
    'index.copy_entire_table'          => 'Copia Tabella Completa',
    'index.download_filtered_csv'      => 'Scarica CSV Filtrato',
    'index.download_filtered_json'     => 'Scarica JSON Filtrato',
    'index.copy_filtered_table'        => 'Copia Tabella Filtrata',
    'index.th_record_id'               => 'ID Record',
    'index.th_created_by'              => 'Creato da',
    'index.th_date_added'              => 'Data di Aggiunta',
    'index.th_actions'                 => 'Azioni',
    'index.modal_heading'              => 'Suggerisci Correzione del Record',
    'index.modal_desc'                 => 'Fornisci una correzione o informazioni alternative per questo record. Il nostro team di moderazione lo esaminerà.',
    'index.modal_target_column'        => 'Colonna di Destinazione:',
    'index.modal_proposed_value'       => 'Valore Proposto / Correzione:',
    'index.modal_input_placeholder'    => 'Inserisci le informazioni aggiornate...',
    'index.modal_submit_btn'           => 'Invia Suggerimento',
    'index.clipboard_success'          => 'I dati della tabella sono stati copiati negli appunti! Puoi incollarli in Excel o Google Fogli.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Modulo di Invito Nuovo Utente',
    'create_user.subheading'           => 'Verrà generato un link di configurazione sicuro valido per 24 ore e inviato direttamente via email all\'utente.',
    'create_user.first_name'           => 'Nome:',
    'create_user.surname'              => 'Cognome:',
    'create_user.username_label'       => 'Nome Utente (Opzionale):',
    'create_user.username_placeholder' => 'Lascia vuoto per la generazione automatica',
    'create_user.username_help'        => 'Se lasciato vuoto, verrà generato automaticamente un nome utente univoco basato sul nome.',
    'create_user.email_label'          => 'Indirizzo Email:',
    'create_user.role_label'           => 'Ruolo Utente:',
    'create_user.submit_btn'           => 'Crea Utente e Invia Invito',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Pannello Ticket di Supporto e Feedback',
    'feedback_dash.subheading'           => 'Gestisci le richieste di supporto pubblico, aggiorna gli stati e partecipa alla conversazione.',
    'feedback_dash.manage_emails'        => 'Gestisci Modelli Email',
    'feedback_dash.manage_schema'        => 'Gestisci Schema Modulo Ticket',
    'feedback_dash.th_ticket_date'       => 'ID Ticket / Data',
    'feedback_dash.th_submitter'         => 'Mittente',
    'feedback_dash.th_subject_info'      => 'Oggetto / Informazioni di Base',
    'feedback_dash.th_status'            => 'Stato',
    'feedback_dash.no_tickets'           => 'Nessun ticket di feedback trovato.',
    'feedback_dash.anonymous'            => 'Anonimo',
    'feedback_dash.default_subject'      => 'Richiesta Generale',
    'feedback_dash.open_ticket_btn'      => 'Apri Ticket e Conversazione',
    'feedback_dash.delete_confirm'       => 'Eliminare questo ticket di supporto e tutte le risposte associate?',
    'feedback_dash.msg_deleted'          => 'Ticket #:id eliminato con successo.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Modelli Email per Ticket di Supporto',
    'feedback_emails.subheading'         => 'Personalizza le notifiche email automatiche inviate durante il flusso dei ticket. Usa le parentesi graffe per i valori dinamici.',
    'feedback_emails.back_to_dashboard' => 'Torna al Pannello Ticket',
    'feedback_emails.email_subject'      => 'Oggetto Email:',
    'feedback_emails.email_body'         => 'Modello Corpo Email:',
    'feedback_emails.save_template_btn' => 'Salva Modello',
    'feedback_emails.placeholders_heading' => 'Placeholder Disponibili',
    'feedback_emails.placeholders_desc' => 'Puoi utilizzare questi tag ovunque nell\'oggetto o nel corpo:',
    'feedback_emails.fixed_tags'         => 'Tag Fissi Principali:',
    'feedback_emails.custom_tags'        => 'Tag di Schema Personalizzati:',
    'feedback_emails.custom_tags_desc'   => 'Generati automaticamente dai campi del generatore di moduli dei ticket:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Gestisci Schema Modulo Feedback',
    'feedback_schema.subheading'             => 'Configura campi personalizzati, tipi di dati, limiti di caratteri, sottotipi, opzioni e stile di visualizzazione.',
    'feedback_schema.settings_summary'       => 'Configura Titolo del Modulo e Testo di Disclaim',
    'feedback_schema.form_title_label'       => 'Titolo del Modulo:',
    'feedback_schema.form_intro_label'       => 'Testo di Introduzione / Descrizione:',
    'feedback_schema.save_settings_btn'      => 'Salva Impostazioni Modulo',
    'feedback_schema.edit_field_title'       => 'Modifica Campo Ticket:',
    'feedback_schema.add_field_title'        => '+ Aggiungi Nuovo Campo al Modulo Ticket',
    'feedback_schema.field_name_label'       => 'Etichetta / Nome Campo:',
    'feedback_schema.data_type_label'        => 'Tipo di Dati:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Testo Breve)',
    'feedback_schema.type_text'              => 'TEXT (Paragrafo Lungo / Messaggio)',
    'feedback_schema.type_int'               => 'INT (Numero Intero)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Indicatore Sì/No)',
    'feedback_schema.type_date'              => 'DATE (Data Calendario)',
    'feedback_schema.subtype_label'          => 'Sottotipo Campo / Stile di Rendering Input:',
    'feedback_schema.subtype_standard'       => '-- Standard --',
    'feedback_schema.subtype_standard_lower'=> 'standard',
    'feedback_schema.options_label'          => 'Opzioni (separate da virgola o una per riga):',
    'feedback_schema.options_help'           => 'Fornisci opzioni separate da virgole o interruzioni di riga.',
    'feedback_schema.allow_multiple'         => 'Consenti selezione multipla (Selezione Multipla)',
    'feedback_schema.boolean_format'         => 'Formato di Visualizzazione Booleano:',
    'feedback_schema.max_length_label'       => 'Lunghezza Massima / Limite Caratteri Opzionale:',
    'feedback_schema.is_required_label'      => 'Rendi questo campo obbligatorio per i mittenti',
    'feedback_schema.save_field_btn'         => 'Salva Modifiche Campo',
    'feedback_schema.create_field_btn'       => 'Crea Campo Ticket',
    'feedback_schema.sub_email'              => 'Email',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Menu a Tendina (Select)',
    'feedback_schema.sub_radio'              => 'Gruppo Pulsanti Radio',
    'feedback_schema.sub_checkbox'           => 'Casella di Controllo (Checkbox)',
    'feedback_schema.sub_textarea'           => 'Casella di Testo Multilinea',
    'feedback_schema.sub_number'             => 'Input Numerico',
    'feedback_schema.existing_fields_heading'=> 'Campi Ticket Esistenti',
    'feedback_schema.th_move'                => 'Sposta',
    'feedback_schema.th_field_name'          => 'Nome Campo',
    'feedback_schema.th_data_type'           => 'Tipo di Dati',
    'feedback_schema.th_subtype'             => 'Sottotipo',
    'feedback_schema.th_required'            => 'Obbligatorio?',
    'feedback_schema.th_max_length'          => 'Lunghezza Massima',
    'feedback_schema.th_created_by'          => 'Creato da',
    'feedback_schema.no_fields'              => 'Nessun campo ticket personalizzato definito.',
    'feedback_schema.system_user'            => 'Sistema',
    'feedback_schema.edit_btn'               => 'Modifica',
    'feedback_schema.delete_confirm'         => 'Eliminare questo campo e tutti i valori di risposta associati?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Gestisci Tabelle e Schemi',
    'manage_tables.subheading'           => 'Crea, ispeziona, modifica o elimina in sicurezza le tabelle dinamiche dell\'applicazione e i relativi schemi di colonne.',
    'manage_tables.switcher_label'       => 'Seleziona schema di tabella attivo:',
    'manage_tables.edit_metadata_btn'    => 'Modifica Metadati Tabella',
    'manage_tables.delete_table_confirm'=> 'ATTENZIONE: L\'eliminazione di questa tabella rimuoverà permanentemente tutte le colonne e il contenuto salvato. Sei assolutamente sicuro?',
    'manage_tables.delete_table_btn'     => 'Elimina Tabella',
    'manage_tables.edit_table_summary'   => 'Modifica Definizione Tabella:',
    'manage_tables.create_table_summary'=> '+ Crea Nuova Tabella Dinamica',
    'manage_tables.table_name_label'     => 'Nome Amichevole della Tabella:',
    'manage_tables.table_desc_label'     => 'Descrizione / Scopo:',
    'manage_tables.save_table_btn'       => 'Salva Modifiche Tabella',
    'manage_tables.create_table_btn'     => 'Crea Schema Tabella',
    'manage_tables.edit_col_summary'     => 'Modifica Colonna Dinamica:',
    'manage_tables.add_col_summary_prefix' => '+ Aggiungi nuova colonna tabella per',
    'manage_tables.col_name_label'       => 'Nome Colonna:',
    'manage_tables.type_text_long'       => 'TEXT (Paragrafo Lungo)',
    'manage_tables.date_behavior_label' => 'Comportamento Ricerca Data:',
    'manage_tables.date_bhv_manual'      => 'Data del database (solo inserimento manuale)',
    'manage_tables.date_bhv_admin'       => 'Solo date amministratore',
    'manage_tables.date_bhv_all'         => 'Tutte le date, comprese quelle dell\'amministratore',
    'manage_tables.req_toggle_label'     => 'Rendi questa colonna obbligatoria (inserimento dati obbligatorio)',
    'manage_tables.exclude_search_label'=> 'Escludi questa colonna dalla ricerca pubblica (index.php)',
    'manage_tables.create_col_btn'       => 'Crea Colonna',
    'manage_tables.existing_cols_heading_prefix' => 'Colonne esistenti per',
    'manage_tables.th_public_search'     => 'Ricerca Pubblica?',
    'manage_tables.th_display_format'    => 'Formato di Visualizzazione',
    'manage_tables.th_date_created'      => 'Data di Creazione',
    'manage_tables.no_columns_found'     => 'Nessuna colonna dinamica definita per questa tabella.',
    'manage_tables.status_hidden'        => 'Nascosto',
    'manage_tables.delete_col_confirm'   => 'ATTENZIONE: L\'eliminazione di questa colonna rimuoverà anche tutti i dati di cella associati in ogni record. Sei sicuro?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Gestisci Modelli Email di Notifica Utente',
    'user_emails.subheading'             => 'Personalizza i layout email invitati quando si invitano utenti o si inviano link di reset password.',
    'user_emails.select_template_label'=> 'Seleziona modello da modificare:',
    'user_emails.opt_invitation'         => 'Modello Invito Account Utente',
    'user_emails.opt_reset'              => 'Modello Reset Password / Link di Accesso',
    'currently_editing'                  => 'Modifica in corso:',
    'user_emails.desc_invitation'        => 'Inviato automaticamente quando un amministratore crea o invita un nuovo utente.',
    'user_emails.desc_reset'             => 'Inviato quando viene richiesto il reset della password o l\'invio di un link di accesso.',
    'user_emails.email_body_label'       => 'Corpo Email:',
    'user_emails.back_to_creation'       => 'Torna alla Creazione Utente',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Modelli Email e Attivatori per Volontari',
    'volunteer_emails.subheading'        => 'Personalizza le risposte email automatiche per i volontari nelle diverse fasi del flusso di lavoro. Usa le parentesi graffe per i valori dinamici.',
    'volunteer_emails.back_to_dashboard'=> 'Torna alle Richieste Volontari',
    'volunteer_emails.custom_tags_desc'  => 'Generati automaticamente dai campi del generatore di moduli:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Gestisci Schema Modulo Volontari',
    'volunteer_schema.subheading'        => 'Configura campi personalizzati, tipi di dati, sottotipi, opzioni e preferenze generali di visualizzazione del modulo.',
    'volunteer_schema.back_to_dashboard'=> 'Torna alle Richieste Volontari',
    'volunteer_schema.settings_summary'  => 'Configura Titolo del Modulo e Testo di Disclaim',
    'volunteer_schema.edit_field_title'  => 'Modifica Campo:',
    'volunteer_schema.add_field_title'   => '+ Aggiungi Nuovo Campo al Modulo Volontari',
    'volunteer_schema.create_field_btn'  => 'Crea Campo',
    'volunteer_schema.existing_fields_heading' => 'Campi Modulo Volontari Esistenti',
    'volunteer_schema.no_fields'         => 'Nessun campo volontario personalizzato definito.',
    'volunteer_schema.delete_confirm'    => 'Eliminare questo campo e tutti i valori di risposta associati?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Revisione Coda di Moderazione e Suggerimenti',
    'moderate.subheading'                => 'Confronta le modifiche suggerite dagli utenti con i record attivi nelle tue tabelle autorizzate. Approva, sovrascrivi o rifiuta i suggerimenti.',
    'moderate.shortcut_label'            => 'Suggerimento Scorciatoie da Tastiera:',
    'moderate.shortcut_desc'             => 'Premi Ctrl + Invio per approvare rapidamente o Esc per pulire la casella di sovrascrittura!',
    'moderate.th_id_date'                => 'ID / Data',
    'moderate.th_table_record'           => 'Tabella, Record e Colonna',
    'moderate.th_comparison'             => 'Confronto (Attivo vs Suggerito) e Prove',
    'moderate.th_actions'                => 'Azioni Moderatore',
    'moderate.no_suggestions'            => 'Nessun suggerimento in sospeso trovato per le tue tabelle di moderazione autorizzate.',
    'moderate.by_label'                  => 'Da:',
    'moderate.guest_user'                => 'Ospite / Visitatore',
    'moderate.record_id_label'           => 'ID Record:',
    'moderate.column_label'              => 'Colonna:',
    'moderate.required_badge'            => 'Obbligatorio',
    'moderate.live_value_label'          => 'Valore attivo attuale:',
    'moderate.empty_placeholder'         => '[Vuoto]',
    'moderate.proposed_value_label'      => 'Modifica suggerita:',
    'moderate.evidence_label'            => 'Prova / Motivazione:',
    'moderate.no_evidence'               => 'Nessuna prova o motivazione fornita.',
    'moderate.override_label'            => 'Sovrascrivi Valore:',
    'moderate.select_placeholder'        => '-- Seleziona --',
    'moderate.historical_dates_title'    => 'Date storiche supportate',
    'moderate.approve_confirm'           => 'Approvare e applicare questo valore?',
    'moderate.decline_confirm'           => 'Rifiutare e scartare questo suggerimento?',
    'moderate.approve_btn'               => 'Approva',
    'moderate.decline_btn'               => 'Rifiuta',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Gestore Avvisi e Annunci del Sito',
    'notices.subheading'                 => 'Crea avvisi dinamici, banner di benvenuto o annunci mirati per ruoli utente specifici.',
    'notices.error_blank'                => 'Il titolo e il contenuto non possono essere vuoti.',
    'notices.msg_created'                => 'Avviso creato con successo!',
    'notices.msg_deleted'                => 'Avviso eliminato.',
    'notices.create_heading'             => 'Crea Nuovo Avviso',
    'notices.title_label'                => 'Titolo / Intestazione Avviso:',
    'notices.content_label'              => 'Contenuto Avviso (consentito HTML/Testo):',
    'notices.target_roles_label'         => 'Pubblico di destinazione (seleziona ruoli o tutti):',
    'notices.role_everyone'              => 'Tutti',
    'notices.role_public'                => 'Pubblico (Ospite)',
    'notices.role_users'                 => 'Utenti',
    'notices.role_moderators'            => 'Moderatori',
    'notices.role_admins'                => 'Amministratori',
    'notices.dismissible_label'          => "Ignorabile (include il pulsante di chiusura 'X')",
    'notices.display_order_label'        => 'Ordine di Visualizzazione:',
    'notices.publish_btn'                => 'Pubblica Avviso',
    'notices.existing_heading'           => 'Avvisi Attivi ed Esistenti',
    'notices.th_order'                   => 'Ordine',
    'notices.th_title'                   => 'Titolo',
    'notices.th_target_roles'            => 'Ruoli di Destinazione',
    'notices.th_dismissible'             => 'Ignorabile',
    'notices.no_notices'                 => 'Nessun avviso creato.',
    'notices.yes'                        => 'Sì',
    'notices.no_sticky'                  => 'No (Fisso / Sticky)',
    'notices.delete_confirm'             => 'Eliminare questo avviso?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Impostazioni Globali del Sito, Moduli e Permessi',
    'settings.subheading'                => 'Gestisci impostazioni centrali, driver email, opzioni di sicurezza/CAPTCHA, moduli di funzionalità, modalità di manutenzione, avvisi del sito e matrice dei ruoli.',
    'settings.tab_core'                  => 'Core ed Email',
    'settings.tab_modules'               => 'Moduli',
    'settings.tab_maintenance'           => 'Manutenzione',
    'settings.tab_notices'               => 'Avvisi del Sito',
    'settings.tab_permissions'           => 'Ruoli e Permessi',
    'settings.tab_audit'                 => 'Registro di Audit',
    'settings.db_updates_heading'        => 'Aggiornamenti del Database',
    'settings.schema_current'            => 'Versione schema attuale:',
    'settings.schema_latest'             => 'Ultima versione disponibile:',
    'settings.download_backup_btn'       => 'Scarica Backup del Database',
    'settings.download_backup_desc'      => 'Salva un file .sql completo sul tuo computer. Conservalo in un luogo sicuro prima di eseguire gli aggiornamenti.',
    'settings.schema_update_notice'      => 'Aggiornamenti del database disponibili. Scarica un backup sopra prima di procedere.',
    'settings.migration_confirm'         => 'Hai scaricato un backup del database? Questo applicherà gli aggiornamenti di schema in sospeso.',
    'settings.update_db_btn'             => 'Aggiorna Database',
    'settings.schema_uptodate'           => 'Il database è aggiornato.',
    'settings.core_sys_heading'          => 'Impostazioni Core del Sistema',
    'settings.sys_name_label'            => 'Nome del Sistema / Applicazione:',
    'settings.default_lang_label'        => 'Lingua Predefinita del Sito:',
    'settings.default_lang_desc'         => 'Usata per i visitatori e gli utenti senza lingua preferita. Aggiungi file in lang/ (es. it.php) per altre opzioni.',
    'settings.captcha_heading'           => 'Configurazione Sicurezza e CAPTCHA',
    'settings.captcha_provider_label'    => 'Meccanismo Provider CAPTCHA:',
    'settings.captcha_none'              => 'Disattivato (Senza CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Impostazioni Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Impostazioni Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Impostazioni hCaptcha',
    'settings.site_key_label'            => 'Chiave del Sito (Pubblica):',
    'settings.secret_key_label'          => 'Chiave Segreta (Privata):',
    'settings.mail_heading'              => 'Configurazione Consegna Email',
    'settings.mail_domain_label'         => 'Dominio Email del Sistema (Fallback):',
    'settings.mail_from_label'           => "Indirizzo Email 'Da' (From) Personalizzato:",
    'settings.mail_from_desc'            => 'Un indirizzo dedicato utilizzato come mittente per le email in uscita.',
    'settings.mail_driver_label'         => 'Driver / Motore Email:',
    'settings.driver_native'             => 'Email Nativa (Postfix Local Relay)',
    'settings.driver_smtp'               => 'SMTP Autenticato (PHPMailer)',
    'settings.smtp_heading'              => 'Impostazioni Server SMTP',
    'settings.smtp_host_label'           => 'Host SMTP:',
    'settings.smtp_port_label'           => 'Porta:',
    'settings.smtp_encryption_label'     => 'Crittografia:',
    'settings.enc_tls'                   => 'TLS (Porta 587)',
    'settings.enc_ssl'                   => 'SSL (Porta 465)',
    'settings.smtp_user_label'           => 'Nome Utente SMTP:',
    'settings.smtp_pass_label'           => 'Password SMTP (lascia vuoto per mantenere quella attuale):',
    'settings.save_core_mail_btn'        => 'Salva Impostazioni Core ed Email',
    'settings.test_mail_heading'         => 'Testa Configurazione Email',
    'settings.test_email_label'          => 'Indirizzo Email del Destinatario:',
    'settings.send_test_btn'             => 'Invia Email di Test',
    'settings.modules_heading'           => 'Interruttori Moduli Applicativi e Controllo Prestazioni',
    'settings.modules_subheading'        => 'Attiva o disattiva le funzionalità per ottimizzare le prestazioni di esecuzione dell\'applicazione e adattarti ai requisiti di distribuzione specifici.',
    'settings.mod_users'                 => 'Gestione Utenti e Accesso Multiutente',
    'settings.mod_users_desc'            => 'Abilita la registrazione, la gestione degli utenti e l\'autenticazione multiutente.',
    'settings.mod_leaderboard'           => 'Classifica e Gamification',
    'settings.mod_leaderboard_desc'      => 'Riconosce gli sforzi di trascrizione e fornisce punti di valutazione con stelle.',
    'settings.mod_leaderboard_note'      => '(Richiede Gestione Utenti e Accesso Multiutente)',
    'settings.mod_moderation'            => 'Flusso di Lavoro di Moderazione',
    'settings.mod_moderation_desc'       => 'Abilita la revisione dei suggerimenti di modifica e la coda di moderazione.',
    'settings.mod_volunteers'            => 'Portale Volontari e Candidature',
    'settings.mod_volunteers_desc'       => 'Abilita il modulo di intento di volontariato pubblico e il pannello di amministrazione.',
    'settings.mod_feedback'              => 'Invio Feedback',
    'settings.mod_feedback_desc'         => 'Abilita il modulo di feedback pubblico e il relativo pannello di amministrazione.',
    'settings.save_modules_btn'          => 'Salva Configurazione Moduli',
    'settings.maintenance_heading'       => 'Modalità di Manutenzione del Sistema',
    'settings.maintenance_toggle'        => 'Attiva Modalità di Manutenzione (Metti il sito offline)',
    'settings.maintenance_reason_label'  => 'Motivo / Messaggio per gli Utenti:',
    'settings.maintenance_eta_label'     => 'Previsione di Ritorno (ETA):',
    'settings.save_maintenance_btn'      => 'Salva Impostazioni Manutenzione',
    'settings.notices_heading'           => 'Avvisi e Annunci del Sito',
    'settings.add_notice_btn'            => '+ Aggiungi Nuovo Avviso',
    'settings.no_notices'                => 'Nessun avviso configurato.',
    'settings.status_active'             => 'Attivo',
    'settings.status_inactive'           => 'Inattivo',
    'settings.notice_content_label'      => 'Contenuto:',
    'settings.save_notice_btn'           => 'Salva Avviso',
    'settings.permissions_heading'       => 'Matrice dei Ruoli e dei Permessi Dinamici',
    'settings.permissions_subheading'    => 'I permessi sono raggruppati per funzionalità di sistema. Espandi le sezioni per configurare le capacità e salva le modifiche sotto.',
    'settings.th_role'                   => 'Ruolo',
    'settings.th_capabilities'           => 'Capacità assegnate a questo gruppo',
    'settings.save_permissions_btn'      => 'Salva Matrice dei Permessi',
    'settings.audit_heading'             => 'Browser del Registro di Audit del Sistema',
    'settings.audit_subheading'          => 'Ispeziona azioni di sicurezza registrate, immissione dati e moderazione. Usa le opzioni di manutenzione sottostanti per pulire i log, se necessario.',
    'settings.purge_all_confirm'         => '⚠️ ATTENZIONE: Questo eliminerà PERMANENTEMENTE TUTTI I REGISTRI DI AUDIT DEL SISTEMA. Sei sicuro?',
    'settings.clear_all_audit_btn'       => 'Cancella Tutti i Registri di Audit',
    'settings.purge_records_confirm'     => 'Sei sicuro di voler cancellare tutti i registri di audit relativi ai record?',
    'settings.clear_records_audit_btn'   => 'Cancella Solo Audit dei Record',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Marca Temporale',
    'settings.th_actor'                  => 'Attore',
    'settings.th_action'                 => 'Azione',
    'settings.th_record_id'              => 'ID Record',
    'settings.th_details'                => 'Dettagli',
    'settings.th_ip'                     => 'Indirizzo IP',
    'settings.no_audit_logs'             => 'Nessun registro di audit trovato.',
    'settings.system_guest'              => 'Sistema / Ospite',
    'settings.audit_limit_note'          => 'Visualizzazione degli ultimi 250 registri di audit.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Gestione Account Utente e Moderazione Classifica',
    'admin_users.subheading'             => 'Verifica lo stato dell\'utente, assegna ruoli, sovrascrivi email, avvia reset password o inviti, ripristina il 2FA o sospendi gli account.',
    'admin_users.manage_templates_btn'   => 'Gestisci Modelli Email',
    'admin_users.invite_user_btn'        => 'Invita Nuovo Utente',
    'admin_users.th_username'            => 'Nome Utente',
    'admin_users.th_email_override'      => 'Email e Sovrascrittura',
    'admin_users.th_role_assignment'     => 'Assegnazione Ruolo',
    'admin_users.th_score'               => 'Punteggio',
    'admin_users.th_status'              => 'Stato',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Azioni e Moderazione',
    'admin_users.no_users'               => 'Nessun utente trovato.',
    'admin_users.save_email_title'       => 'Salva Nuovo Indirizzo Email',
    'admin_users.verified_label'         => 'Verificato:',
    'admin_users.yes'                    => 'Sì',
    'admin_users.no'                     => 'No',
    'admin_users.protected_admin'        => 'Amministratore principale protetto',
    'admin_users.update_btn'             => 'Aggiorna',
    'admin_users.status_active'          => 'Attivo',
    'admin_users.status_suspended'       => 'Sospeso',
    'admin_users.enabled'                => 'Attivato',
    'admin_users.disabled'               => 'Disattivato',
    'admin_users.set_score_btn'          => 'Imposta Punteggio',
    'admin_users.resend_invite_confirm' => 'Reinviare l\'email di invito dell\'account a questo utente?',
    'admin_users.resend_invite_btn'      => 'Reinvia Invito',
    'admin_users.reset_pwd_confirm'      => 'Inviare un link di reset password a questo utente?',
    'admin_users.reset_password_btn'     => 'Reimposta Password',
    'admin_users.suspend_confirm'        => 'Sospendere l\'utente e revocare l\'accesso a causa di un uso improprio?',
    'admin_users.suspend_btn'            => 'Sospendi Account',
    'admin_users.reactivate_btn'         => 'Riattiva',
    'admin_users.reset_2fa_confirm'      => 'Ripristinare e disattivare il 2FA per questo utente?',
    'admin_users.reset_2fa_btn'          => 'Ripristina 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Torna al Pannello Ticket',
    'view_ticket.ticket_heading_prefix'=> 'Ticket',
    'view_ticket.support_request'      => 'Richiesta di Supporto',
    'view_ticket.submitted_by'         => 'Inviato da:',
    'view_ticket.on_date'              => 'il',
    'view_ticket.submitted_fields'     => 'Campi del modulo inviati:',
    'view_ticket.ticket_status_label'  => 'Stato del Ticket:',
    'view_ticket.status_pending'       => 'In Sospeso',
    'view_ticket.status_progress'      => 'In Corso',
    'view_ticket.status_completed'     => 'Completato',
    'view_ticket.status_rejected'      => 'Rifiutato',
    'view_ticket.dialogue_heading'     => 'Cronologia della Conversazione',
    'view_ticket.no_replies'           => 'Nessuna risposta registrata.',
    'view_ticket.admin_label'          => 'Amministratore',
    'view_ticket.staff'                => 'Personale',
    'view_ticket.post_reply_heading'   => 'Pubblica Risposta e Notifica Mittente',
    'view_ticket.reply_placeholder'    => 'Scrivi la tua risposta qui...',
    'view_ticket.send_reply_btn'       => 'Invia Risposta ed Email al Mittente',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Candidature Volontari e Flusso di Lavoro',
    'volunteer_dashboard.subheading'         => 'Esamina le candidature, pianifica conversazioni, registra note di colloquio e approva i candidati nel sistema.',
    'volunteer_dashboard.manage_emails_btn' => 'Gestisci Modelli Email',
    'volunteer_dashboard.manage_schema_btn' => 'Gestisci Schema Modulo',
    'volunteer_dashboard.th_status'          => 'Stato',
    'volunteer_dashboard.th_name'            => 'Nome',
    'volunteer_dashboard.th_interview_notes'=> 'Colloquio / Note',
    'volunteer_dashboard.no_submissions'     => 'Nessuna candidatura di volontario trovata.',
    'volunteer_dashboard.volunteer_prefix'   => 'Volontario',
    'volunteer_dashboard.chat_label'         => 'Conversazione:',
    'volunteer_dashboard.notes_label'        => 'Note:',
    'volunteer_dashboard.no_notes'           => 'Nessuna nota',
    'volunteer_dashboard.chat_notes_btn'     => 'Conversazione e Note',
    'volunteer_dashboard.accept_title'       => 'Approva tramite Sistema di Invito Utente',
    'volunteer_dashboard.accept_invite_btn'  => 'Approva e Invia Invito',
    'volunteer_dashboard.delete_confirm'     => 'Eliminare questo record di volontario?',
    'volunteer_dashboard.modal_heading'      => 'Gestisci Colloquio e Note del Candidato',
    'volunteer_dashboard.modal_status_label'=> 'Stato Candidatura:',
    'volunteer_dashboard.status_pending'     => 'In Attesa di Revisione',
    'volunteer_dashboard.status_chat'        => 'Conversazione Programmata',
    'volunteer_dashboard.status_accepted'    => 'Approvato',
    'volunteer_dashboard.status_rejected'    => 'Rifiutato',
    'volunteer_dashboard.modal_date_label'   => 'Data e Ora della Conversazione Programmata:',
    'volunteer_dashboard.modal_notes_label'  => 'Note Colloquio / Riunione:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Registra il feedback della conversazione qui...',
    'volunteer_dashboard.save_changes_btn'   => 'Salva Modifiche',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Vietato: La visualizzazione pubblica non è abilitata.',
    'api_search.error_unauthorized_table' => 'Accesso non autorizzato alla tabella.',
    'api_search.no_records'              => 'Nessun record trovato in questa directory.',
    'api_search.history_btn'             => 'Cronologia',
    'api_search.suggest_edit_btn'        => 'Suggerisci Modifica',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Torna alla Pagina Iniziale Pubblica',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ------------------------------------------------------------------
    'feedback.hp_label'              => 'Lascia vuoto',
    'feedback.first_name_label'      => 'Nome:',
    'feedback.surname_label'         => 'Cognome:',
    'feedback.email_label'           => 'Indirizzo Email:',
    'feedback.subject_label'         => 'Oggetto / Titolo della Richiesta:',
    'feedback.required_title'        => 'Campo Obbligatorio',
    'feedback.select_placeholder'    => '-- Seleziona --',
    'feedback.multi_select_hint'     => 'Premi Ctrl o Cmd per selezionare più elementi.',
    'feedback.submit_btn'            => 'Invia Ticket',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Errore di sicurezza: Firma client sospetta.',
    'security_engine.err_access_denied'    => 'Errore di sicurezza: Accesso negato.',
    'security_engine.err_rate_limit'       => 'Troppe richieste da questo indirizzo IP. Riprova più tardi.',
    'security_engine.err_excessive_links'  => 'Invio bloccato a causa di un numero eccessivo di link.',
    'security_engine.err_complete_captcha' => 'Completa il controllo di sicurezza CAPTCHA.',
    'security_engine.err_captcha_failed'   => 'Verifica CAPTCHA fallita. Riprova.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Installazione Completata',
    'install.complete_heading'           => 'Installazione Completata',
    'install.complete_desc'              => 'Questo sito è già configurato. L\'installatore è stato bloccato per evitarne la riesecuzione.',
    'install.login_link'                 => 'Accedi',
    'install.home_link'                  => 'Vai al Sito',
    'install.delete_folder_hint'         => 'Per maggiore sicurezza, puoi eliminare o rinominare la cartella <code>install</code>.',
    'install.msg_db_ready'               => 'Database pronto. Crea il tuo account amministratore per completare.',
    'install.err_config_load'            => 'Impossibile utilizzare la configurazione esistente:',
    'install.err_write_permission'       => 'PHP non può creare file in questa cartella del progetto.',
    'install.detail_prefix'              => 'Dettaglio:',
    'install.err_db_required'            => 'Il nome del database e l\'utente sono obbligatori.',
    'install.err_db_not_empty'           => 'Questo database non è vuoto. Usa un nuovo database vuoto (o svuota tutte le tabelle) e riprova.',
    'install.msg_schema_imported'        => 'Database connesso e schema importato. Crea il tuo account amministratore.',
    'install.err_complete_db_first'      => 'Completa prima la fase del database.',
    'install.err_admin_required'         => 'Tutti i campi amministratore sono obbligatori.',
    'install.err_invalid_email'          => 'Indirizzo email non valido.',
    'install.err_password_length'        => 'La password deve contenere almeno 8 caratteri.',
    'install.err_passwords_match'        => 'Le password non coincidono.',
    'install.err_admin_save_failed'      => 'Salvataggio utente amministratore fallito. Controlla la struttura della tabella utenti.',
    'install.msg_installation_complete' => 'Installazione completata.',
    'install.page_title'                 => 'Installazione — Registro Parrocchiale',
    'install.heading'                    => 'Installazione',
    'install.subheading'                 => 'Configurazione iniziale <strong>solo per questa cartella dell\'applicazione</strong>. Usa un database MySQL vuoto.',
    'install.done_heading'               => 'Completato',
    'install.done_message'               => 'Installazione completata. L\'installatore è bloccato.',
    'install.admin_heading'              => 'Account Amministratore del Sito',
    'install.admin_subheading'           => 'Queste sono le credenziali di accesso per <strong>questo sito</strong> (non per il database).',
    'install.admin_username_label'       => 'Nome Utente Amministratore',
    'install.admin_email_label'          => 'Email Amministratore',
    'install.admin_password_label'       => 'Password Amministratore (min. 8 caratteri)',
    'install.admin_confirm_password_label' => 'Conferma Password Amministratore',
    'install.finish_btn'                 => 'Completa Installazione',
    'install.db_heading'                 => 'Connessione al Database',
    'install.db_hint'                    => 'Usa i dettagli MySQL dal tuo <strong>pannello di controllo hosting</strong>. Non è il login amministratore del sito.',
    'install.db_host_label'              => 'Host del Database',
    'install.db_name_label'              => 'Nome del Database',
    'install.db_user_label'              => 'Utente del Database',
    'install.db_pass_label'              => 'Password del Database',
    'install.db_submit_btn'              => 'Crea Tabelle e Continua',
    'install.req_heading'                => '1. Requisiti',
    'install.req_php'                    => 'PHP 8.0+ (rilevato %s)',
    'install.req_pdo'                    => 'Estensione PDO MySQL',
    'install.req_logs'                   => 'Cartella log con permessi di scrittura (o cartella del progetto)',
    'install.req_probe'                  => 'Capacità di creare file in questa cartella del progetto',
    'install.continue_btn'               => 'Continua',
    'install.req_fail_msg'               => 'Correggi i controlli non riusciti e ricarica questa pagina.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Visualizzazione Classifica',
    'leaderboard.heading'         => 'Classifica di Partecipazione della Comunità',
    'leaderboard.subheading'      => 'Un riconoscimento agli sforzi dei membri della nostra comunità che aiutano a raccogliere, trascrivere o gestire i record.',
    'leaderboard.th_rank'         => 'Posizione',
    'leaderboard.th_contributor'  => 'Contributore',
    'leaderboard.th_role'         => 'Ruolo',
    'leaderboard.th_score'        => 'Punteggio',
    'leaderboard.no_users'        => 'Nessun utente attivo trovato nella classifica.',
    'leaderboard.medal_gold'      => 'Medaglia d\'Oro',
    'leaderboard.medal_silver'    => 'Medaglia d\'Argento',
    'leaderboard.medal_bronze'    => 'Medaglia di Bronzo',
    'leaderboard.medal_ribbon'    => 'Nastro Livello 4',
    'leaderboard.medal_rosette'   => 'Rosetta Livello 5',
    'leaderboard.medal_trophy'    => 'Trofeo Livello 6',
    'leaderboard.medal_star'      => 'Stella Livello 7',
    'leaderboard.medal_military'  => 'Medaglia Militare Livello 8',
    'leaderboard.medal_glowing'   => 'Stella Brillante Livello 9',
    'leaderboard.medal_crown'     => 'Corona Livello 10',
    'leaderboard.you_badge'       => '(Tu)',
    'leaderboard.default_role'    => 'Utente',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Registri parrocchiali compilati da fonti storiche di pubblico dominio.',
    'footer.software_notice'  => 'Piattaforma software open source con licenza MIT.',
    'footer.rights_reserved'  => 'Tutti i diritti riservati.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Database dei Registri Parrocchiali',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Chiudi avviso',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Nessun record specificato.',
    'record_history.exit_not_found'        => 'Record non trovato.',
    'record_history.heading_prefix'        => 'Cronologia e Registro di Audit: Record',
    'record_history.return_btn'            => 'Indietro',
    'record_history.directory_table_label'=> 'Tabella della Directory:',
    'record_history.subheading_lifecycle' => 'Mostra il ciclo di vita sociale delle modifiche, dei suggerimenti e delle prove associate a questo record.',
    'record_history.snapshot_heading'      => 'Istantanea dei valori attivi correnti',
    'record_history.empty_value'           => '[Vuoto]',
    'record_history.timeline_heading'      => 'Cronologia degli Eventi e delle Attività',
    'record_history.no_history'            => 'Nessun evento di audit storico registrato specificamente per questo record.',
    'record_history.purge_confirm'         => 'Eliminare questa voce specifica del registro di audit?',
    'record_history.purge_btn'             => 'Pulisci Registro',
    'record_history.actor_label'           => 'Attore:',
    'record_history.system_guest'          => 'Sistema / Ospite',
    'record_history.target_column'         => 'Colonna di Destinazione:',
    'record_history.proposed_value'        => 'Valore Proposto:',
    'record_history.reasoning_evidence'    => 'Motivazione / Prova:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Database aggiornato con successo! Applicate %d migrazioni.',
    'update_database.msg_uptodate'     => 'Il database è già aggiornato.',
    'update_database.err_failed'       => 'Migrazione fallita:',
    'update_database.page_title'       => 'Aggiornamento di Sistema Necessario — Registro Parrocchiale',
    'update_database.heading'          => '⚠️ Aggiornamento di Sistema Necessario',
    'update_database.subheading'       => 'Lo schema del database dell\'applicazione è obsoleto e richiede un aggiornamento dello schema prima di procedere.',
    'update_database.current_version'  => 'Versione schema attuale:',
    'update_database.latest_version'   => 'Ultima versione disponibile:',
    'update_database.proceed_login'    => 'Vai alla Pagina di Accesso',
    'update_database.confirm_prompt'   => 'Hai eseguito il backup del database? Premi OK per applicare gli aggiornamenti di schema in sospeso.',
    'update_database.update_btn'       => 'Aggiorna Database Ora',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Credenziali non valide o accesso all\'account limitato.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'Il campo obbligatorio \'%s\' non può essere vuoto.',
    'save_data_entry.audit_created_prefix' => 'Record creato nella tabella con ID %d.',
    'save_data_entry.msg_success'          => 'Record aggiunto con successo!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Spam rilevato. Invio rifiutato.',
    'save_public_suggestion.err_field_required' => 'Questo campo è obbligatorio e non può essere inviato vuoto.',
    'save_public_suggestion.msg_success'        => 'Il tuo suggerimento di modifica è stato inviato con successo alla coda di moderazione. Grazie!',
    'save_public_suggestion.err_failed_submit'  => 'Invio del suggerimento di modifica fallito. Riprova.',
    'save_public_suggestion.err_invalid_column' => 'Colonna specificata non valida.',
    'save_public_suggestion.err_invalid_params' => 'Parametri di invio record non validi.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'AAAA-MM-GG (o anno parziale)',
    'data_entry.date_placeholder_dmy' => 'GG/MM/AAAA (o anno parziale)',
    'data_entry.date_placeholder_mdy' => 'MM/GG/AAAA (o anno parziale)',
    'data_entry.no_tables_heading'    => '⚠️ Nessuna Tabella del Database Trovata',
    'data_entry.no_tables_desc'       => 'Nessuna tabella del database attiva configurata per l\'inserimento dei dati.',
    'data_entry.admin_tables_prompt'  => 'Come amministratore, vai su <strong>Gestisci Tabelle</strong> per creare una tabella e aggiungere una colonna prima di inserire record.',
    'data_entry.go_manage_tables'     => 'Vai a Gestisci Tabelle',
    'data_entry.contact_admin_tables' => 'Contatta un amministratore per configurare tabelle e colonne.',
    'data_entry.no_cols_heading'      => '⚠️ Nessuna Colonna Configurata',
    'data_entry.no_cols_desc'         => 'Ci sono tabelle nel sistema, ma non è stata definita alcuna colonna di dati per la tabella attiva.',
    'data_entry.admin_cols_prompt'    => 'Come amministratore, vai su <strong>Gestisci Tabelle</strong> per aggiungere almeno una colonna.',
    'data_entry.contact_admin_cols'   => 'Contatta l\'amministratore per configurare le colonne di questa tabella.',
    'data_entry.active_table_label'   => 'Tabella di inserimento dati attiva:',
    'data_entry.add_entry_summary'    => '➕ Aggiungi nuova immissione dati (Clicca per espandere/comprimere)',
    'data_entry.bool_yes_true'        => 'Sì / Vero',
    'data_entry.bool_no_false'        => 'No / Falso',
    'data_entry.bool_male'            => 'Maschio',
    'data_entry.bool_female'          => 'Femmina',
    'data_entry.bool_true'            => 'Vero',
    'data_entry.bool_false'           => 'Falso',
    'data_entry.bool_tick'            => '✔ (Spuntato)',
    'data_entry.bool_cross'           => '✘ (Croce)',
    'data_entry.date_title_hint'      => 'Accetta date complete o parziali (es. 1842 o 1842-05)',
    'data_entry.enter_value_placeholder' => 'Inserisci valore...',
    'data_entry.submit_data_btn'      => 'Invia Dati',
    'data_entry.shortcuts_tip'        => '💡 Suggerimento: Premi <strong>Ctrl + Invio</strong> per inviare o <strong>Esc</strong> per pulire il campo corrente.',
    'data_entry.dup_heading'          => '⚠️ Avviso di Possibile Duplicato',
    'data_entry.dup_desc'             => 'Abbiamo trovato record simili nel sistema:',
    'data_entry.dup_item_format'      => 'ID Record: %d — Valore: %s',
    'data_entry.dup_prompt'           => 'Desideri procedere e salvare comunque questo duplicato?',
    'data_entry.dup_confirm_btn'      => 'Sì, conferma e salva duplicato',
    'data_entry.search_summary'       => '🔍 Cerca e Filtra Record Esistenti (Clicca per espandere/comprimere)',
    'data_entry.date_to_label'        => 'a',
    'data_entry.filter_all_option'    => '-- Tutti --',
    'data_entry.filter_placeholder'   => 'Filtra...',
    'data_entry.apply_filters_btn'    => 'Applica Filtri di Ricerca',
    'data_entry.reset_filter_btn'     => 'Reimposta Filtro',
    'data_entry.csv_entire_btn'       => 'Scarica CSV Completo',
    'data_entry.json_entire_btn'      => 'Scarica JSON Completo',
    'data_entry.copy_entire_btn'      => 'Copia Tabella Completa',
    'data_entry.csv_filtered_btn'     => 'Scarica CSV Filtrato',
    'data_entry.json_filtered_btn'     => 'Scarica JSON Filtrato',
    'data_entry.copy_filtered_btn'    => 'Copia Tabella Filtrata',
    'data_entry.clipboard_alert'      => 'Dati della tabella copiati negli appunti! Puoi incollarli in Excel o Google Fogli.',
    'data_entry.existing_records_heading' => 'Tabella dei Record Esistenti',
    'data_entry.th_added_by'          => 'Aggiunto da',
    'data_entry.th_date_created'      => 'Data di Creazione',
    'data_entry.no_records'           => 'Nessun record trovato.',
    'data_entry.na_value'             => 'N/D',
    'data_entry.page_label'           => 'Pagina:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Recupero Password',
    'forgot_password.heading'         => 'Reimposta la tua Password',
    'forgot_password.subheading'      => 'Inserisci l\'indirizzo email del tuo account qui sotto per ricevere un link sicuro di reset della password.',
    'forgot_password.email_label'     => 'Indirizzo Email:',
    'forgot_password.submit_btn'      => 'Invia Link di Reset',
    'forgot_password.back_login_link' => 'Torna alla pagina di accesso',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Accesso Utente',
    'login.heading'              => 'Accedi all\'Account',
    'login.username_label'       => 'Nome utente o email:',
    'login.password_label'       => 'Password:',
    'login.submit_btn'           => 'Accedi',
    'login.forgot_password_link' => 'Hai dimenticato la password?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Benvenuto — Configurazione Account',
    'onboarding.heading'           => 'Benvenuto nel team!',
    'onboarding.subheading'        => 'Prima di iniziare, prenditi un momento per configurare le preferenze di visualizzazione regionale e la privacy.',
    'onboarding.timezone_label'    => 'Fuso Orario / Regione:',
    'onboarding.date_format_label' => 'Formato di Visualizzazione Data:',
    'onboarding.time_format_label' => 'Formato Orologio (Visualizzazione Ora):',
    'onboarding.time_24'          => '24 ore (es. 16:07)',
    'onboarding.time_12'          => '12 ore AM/PM (es. 04:07 PM)',
    'onboarding.time_none'        => 'Solo Data (Nascondi completamente l\'ora)',
    'onboarding.attribution_label' => 'Preferenza di visualizzazione in classifica:',
    'onboarding.attribution_desc1' => 'Controlla come appare il tuo nome nella classifica pubblica e nei record.',
    'onboarding.attr_anon_title'   => 'Anonimo:',
    'onboarding.attr_anon_text'    => 'Mostra le iniziali e un numero casuale a tutti.',
    'onboarding.attr_public_title' => 'Pubblico:',
    'onboarding.attr_public_text'  => 'Mostra il tuo nome completo a tutti.',
    'onboarding.attr_vol_title'    => 'Solo Volontari:',
    'onboarding.attr_vol_text'     => 'Mostra le iniziali al pubblico, ma il tuo nome completo ai volontari, moderatori e amministratori loggati.',
    'onboarding.attr_opt_anon'     => 'Anonimo (Iniziali e numero casuale)',
    'onboarding.attr_opt_public'   => 'Pubblico (Mostra nome completo)',
    'onboarding.attr_opt_vol'      => 'Solo volontari',
    'onboarding.submit_btn'        => 'Salva Preferenze e Continua',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Gestione Profilo Utente',
    'profile.heading'              => 'Profilo Utente e Sicurezza',
    'profile.personal_details_heading' => 'Dettagli Personali',
    'profile.language_label'       => 'Lingua Preferita:',
    'profile.lang_site_default'    => 'Predefinita del Sito',
    'profile.update_details_btn'   => 'Aggiorna Dettagli Personali',
    'profile.email_heading'        => 'Indirizzo Email',
    'profile.current_email_label'  => 'Email attuale:',
    'profile.email_verified'       => '(Verificato)',
    'profile.email_unverified'     => '(Non verificato - Controlla la tua casella di posta)',
    'profile.change_email_label'   => 'Modifica Indirizzo Email:',
    'profile.aria_new_email'       => 'Nuovo Indirizzo Email',
    'profile.update_email_btn'     => 'Aggiorna Email e Verifica',
    'profile.password_heading'     => 'Modifica Password',
    'profile.current_password_label' => 'Password Attuale:',
    'profile.new_password_label'   => 'Nuova Password (min. 8 caratteri):',
    'profile.confirm_password_label' => 'Conferma Nuova Password:',
    'profile.show_passwords_label' => 'Mostra password in chiaro',
    'profile.update_password_btn'  => 'Aggiorna Password',
    'profile.tfa_heading'          => 'Autenticazione a Due Fattori (2FA)',
    'profile.tfa_status_label'     => 'Stato:',
    'profile.tfa_enabled'          => 'Attivato',
    'profile.tfa_disabled'         => 'Disattivato',
    'profile.setup_tfa_btn'        => 'Configura Google Authenticator',
    'profile.tfa_active_desc'      => 'Il 2FA sta proteggendo attivamente l\'accesso al tuo account.',
    'profile.backup_codes_heading' => 'I tuoi nuovi codici di backup di sicurezza',
    'profile.download_codes_btn'   => 'Scarica nuovi codici come .txt',
    'profile.generate_codes_confirm' => 'Sei sicuro? Questo invaliderà qualsiasi codice di backup esistente.',
    'profile.generate_codes_btn'   => 'Genera Nuovi Codici di Backup',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Registrazione Utente',
    'register.heading'        => 'Registra Nuovo Account',
    'register.username_label' => 'Nome Utente:',
    'register.submit_btn'     => 'Registrati',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Token di configurazione non valido o mancante.',
    'set_password.exit_expired_token'        => 'Questo link per la password non è valido o è scaduto.',
    'set_password.proceed_login_btn'         => 'Vai alla Pagina di Accesso',
    'set_password.aria_region'               => 'Imposta Password',
    'set_password.heading_format'            => 'Imposta Password per %s',
    'set_password.subheading_format'         => 'Benvenuto nel tuo nuovo account, %s! Scegli la tua password qui sotto.',
    'set_password.new_password_label'        => 'Nuova Password (min. 8 caratteri):',
    'set_password.confirm_password_label'    => 'Conferma Password:',
    'set_password.show_password_label'       => 'Mostra Password',
    'set_password.save_password_btn'         => 'Salva Password',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => 'Configurazione Guidata 2FA',
    'setup_2fa.heading'          => 'Configura Google Authenticator',
    'setup_2fa.subheading'       => 'Scansiona il codice QR qui sotto con la tua app di autenticazione.',
    'setup_2fa.qr_alt'           => 'Codice QR per configurazione 2FA',
    'setup_2fa.manual_prompt'    => 'Oppure inserisci questa chiave segreta manualmente:',
    'setup_2fa.backup_heading'   => 'Codici di Recupero Sicurezza di Emergenza',
    'setup_2fa.backup_desc'      => 'Conserva questi codici di backup in un luogo sicuro. Ogni codice può essere utilizzato <strong>solo una volta</strong> in caso di perdita di accesso alla tua app:',
    'setup_2fa.download_btn'     => 'Scarica codici come .txt',
    'setup_2fa.code_label'       => 'Inserisci il codice a 6 cifre dall\'app per verificare e attivare:',
    'setup_2fa.aria_code_input'  => 'Codice di verifica a 6 cifre',
    'setup_2fa.submit_btn'       => 'Verifica e Attiva 2FA',
    'setup_2fa.cancel_link'      => 'Annulla e Torna al Profilo',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Suggerisci Modifica',
    'suggest_edit.heading_prefix'       => 'Suggerisci Modifica per il Record',
    'suggest_edit.return_btn'           => 'Torna al Record',
    'suggest_edit.success_msg_suffix'   => 'Puoi inviare un\'altra modifica qui sotto o usare il link di ritorno sopra quando hai finito.',
    'suggest_edit.current_values_heading' => 'Valori Attuali:',
    'suggest_edit.empty_label'          => '(vuoto)',
    'suggest_edit.submit_heading'       => 'Invia Nuovo Valore Proposto e Prove',
    'suggest_edit.confirm_prompt'       => 'Sei sicuro di voler inviare questo suggerimento di modifica per la revisione dell\'amministratore?',
    'suggest_edit.select_column_label'  => 'Seleziona colonna da modificare:',
    'suggest_edit.reasoning_label'      => 'Prova / Motivazione / Note sulla Fonte:',
    'suggest_edit.reasoning_placeholder'=> 'Fornisci contesto, citazione della fonte o ragione per questa modifica...',
    'suggest_edit.submit_btn'           => 'Invia Suggerimento per la Revisione',
    'suggest_edit.proposed_value_label' => 'Nuovo valore proposto:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => 'Verifica 2FA',
    'verify_2fa.heading'         => 'Autenticazione a Due Fattori',
    'verify_2fa.subheading'      => 'Inserisci il codice a 6 cifre dalla tua app di autenticazione o un codice di backup di sicurezza.',
    'verify_2fa.code_label'      => 'Codice di Verifica / Codice di Sicurezza:',
    'verify_2fa.aria_code_input' => 'Inserisci il codice di verifica o di sicurezza',
    'verify_2fa.submit_btn'      => 'Verifica e Accedi',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Nessun token di verifica fornito.',
    'verify_email.err_invalid_token'    => 'Token di verifica non valido.',
    'verify_email.msg_already_verified' => 'La tua email è già stata verificata. Puoi accedere.',
    'verify_email.err_expired_token'    => 'Questo link di verifica è scaduto (limite di 24 ore superato). Registrati di nuovo o richiedi un nuovo link.',
    'verify_email.msg_success'          => 'Email verificata con successo! Il tuo account è ora attivo. Procedi con l\'accesso.',
    'verify_email.err_update_failed'    => 'Si è verificato un errore durante la verifica dell\'email. Riprova.',
    'verify_email.aria_region'          => 'Stato Verifica Email',
    'verify_email.heading'              => 'Stato Verifica Email',
    'verify_email.login_btn'            => 'Clicca qui per accedere',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Modulo Volontario',
    'volunteer.honeypot_label'       => 'Lascia questo campo vuoto:',
    'volunteer.required_field_title'=> 'Campo Obbligatorio',
    'volunteer.multi_select_hint'    => 'Premi Ctrl o Cmd per selezionare più elementi.',
    'volunteer.submit_btn'           => 'Invia Candidatura Volontario',
];
