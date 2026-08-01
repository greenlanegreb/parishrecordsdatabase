<?php
// lang/da.php - Danish (Dansk)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Log ind',
    'nav.logout'                 => 'Log ud',
    'nav.feedback'               => 'Feedback',
    'nav.volunteer'              => 'Bliv frivillig',
    'nav.leaderboard'            => 'Rangliste',
    'nav.search'                 => 'Søg',
    'nav.settings'               => 'Systemindstillinger',
    'nav.high_contrast'          => 'Høj kontrast',
    'nav.low_contrast'           => 'Lav kontrast',
    'nav.welcome'                => 'Velkommen,',
    'nav.data_entry'             => 'Dataintastning',
    'nav.moderation'             => 'Moderering',
    'nav.invite_user'            => 'Inviter bruger',
    'nav.manage_users'           => 'Administrer brugere',
    'nav.manage_tables'          => 'Administrer tabeller',
    'nav.volunteer_dashboard'    => 'Frivillig-kontrolpanel',
    'nav.feedback_dashboard'     => 'Feedback-kontrolpanel',
    'nav.leaderboard_score'      => 'Ranglistescore',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Multikolonne-sammensat søgning',
    'search.reset'               => 'Nulstil søgning',
    'search.export_csv'          => 'Eksporter filtrerede resultater til CSV',
    'search.no_records'          => 'Ingen poster fundet i dette arkiv.',
    'search.load_error'          => 'Kunne ikke indlæse resultater. Prøv igen.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Indsend',
    'btn.cancel'                 => 'Annuller',
    'btn.save'                   => 'Gem',
    'btn.delete'                 => 'Slet',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Mange tak! Din feedback er blevet sendt succesfuldt.',
    'feedback.error_all_fields'   => 'Udfyld venligst alle felter.',
    'feedback.error_invalid_email'=> 'Indtast venligst en gyldig e-mailadresse.',
    'feedback.error_save_failed'  => 'Der opstod en fejl under gemningen af din feedback. Prøv igen.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Ingen databasetabeller fundet',
    'index.no_tables_desc'             => 'Der er i øjeblikket ingen aktive databasetabeller konfigureret i systemet.',
    'index.admin_create_table_guide'   => 'Som administrator skal du gå til <strong>Administrer tabeller</strong> for at oprette en tabel og tilføje mindst én kolonne, før du kan få vist eller indtaste poster.',
    'index.go_to_manage_tables'        => 'Gå til Administrer tabeller',
    'index.contact_admin_tables'       => 'Kontakt venligst en administrator for at konfigurere databasetabeller og kolonner.',
    'index.guest_login_tables_guide'   => 'Venligst <a href=":login_link">log ind</a> eller kontakt en administrator for at konfigurere tabeller.',
    'index.no_columns_heading'         => 'Ingen kolonner konfigureret',
    'index.no_columns_desc'            => 'Der findes tabeller i systemet, men der er ikke defineret nogen datakolonner for den aktive tabel.',
    'index.admin_add_columns_guide'    => 'Som administrator skal du gå til <strong>Administrer tabeller</strong> for at tilføje mindst én kolonne til din tabel.',
    'index.contact_admin_columns'      => 'Kontakt venligst administrator for at konfigurere kolonner for denne tabel.',
    'index.select_directory_database'  => 'Vælg arkivdatabase:',
    'index.opt_yes_true'               => 'Ja / Sand',
    'index.opt_no_false'               => 'Nej / Falsk',
    'index.opt_male'                   => 'Mand',
    'index.opt_female'                 => 'Kvinde',
    'index.opt_true'                   => 'Sand',
    'index.opt_false'                  => 'Falsk',
    'index.opt_tick'                   => '✔ (Tjekket)',
    'index.opt_cross'                  => '✘ (Kryds)',
    'index.option_all'                 => '-- Alle --',
    'index.date_to_label'              => 'til',
    'index.search_placeholder'         => 'Søg...',
    'index.download_entire_csv'        => 'Download hele CSV',
    'index.download_entire_json'       => 'Download hele JSON',
    'index.copy_entire_table'          => 'Kopier hele tabellen',
    'index.download_filtered_csv'      => 'Download filtreret CSV',
    'index.download_filtered_json'     => 'Download filtreret JSON',
    'index.copy_filtered_table'        => 'Kopier filtreret tabel',
    'index.th_record_id'               => 'Post-ID',
    'index.th_created_by'              => 'Oprettet af',
    'index.th_date_added'              => 'Dato tilføjet',
    'index.th_actions'                 => 'Handlinger',
    'index.modal_heading'              => 'Foreslå rettelse til post',
    'index.modal_desc'                 => 'Giv en rettelse eller alternative oplysninger til denne post. Vores modereringsteam vil gennemgå det.',
    'index.modal_target_column'        => 'Målkolonne:',
    'index.modal_proposed_value'       => 'Foreslået værdi / Rettelse:',
    'index.modal_input_placeholder'    => 'Indtast opdaterede oplysninger...',
    'index.modal_submit_btn'           => 'Indsend forslag',
    'index.clipboard_success'          => 'Tabeldata kopieret til udklipsholderen! Du kan indsætte dem direkte i Excel eller Google Sheets.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Formular til invitation af ny bruger',
    'create_user.subheading'           => 'Dette vil generere et sikkert opsætningslink, der er gyldigt i 24 timer, og sende det direkte via e-mail til brugeren.',
    'create_user.first_name'           => 'Fornavn:',
    'create_user.surname'              => 'Efternavn:',
    'create_user.username_label'       => 'Brugernavn (valgfrit):',
    'create_user.username_placeholder' => 'Lad stå tom for automatisk generering',
    'create_user.username_help'        => 'Hvis feltet efterlades tomt, genereres der automatisk et unikt brugernavn baseret på fornavnet.',
    'create_user.email_label'          => 'E-mailadresse:',
    'create_user.role_label'           => 'Brugerrolle:',
    'create_user.submit_btn'           => 'Opret bruger og send invitation',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Kontrolpanel for supportanmodninger og feedback',
    'feedback_dash.subheading'           => 'Administrer offentlige supportanmodninger, opdater statusser og deltag i trådede diskussioner.',
    'feedback_dash.manage_emails'        => 'Administrer e-mailskabeloner',
    'feedback_dash.manage_schema'        => 'Administrer skema for supportformular',
    'feedback_dash.th_ticket_date'       => 'Support-ID / Dato',
    'feedback_dash.th_submitter'         => 'Indsender',
    'feedback_dash.th_subject_info'      => 'Emne / Kernedata',
    'feedback_dash.th_status'            => 'Status',
    'feedback_dash.no_tickets'           => 'Ingen feedback-sager fundet.',
    'feedback_dash.anonymous'            => 'Anonym',
    'feedback_dash.default_subject'      => 'Generel forespørgsel',
    'feedback_dash.open_ticket_btn'      => 'Åbn sag og dialog',
    'feedback_dash.delete_confirm'       => 'Slet denne support-sag og alle tilhørende svar?',
    'feedback_dash.msg_deleted'          => 'Sag #:id blev slettet succesfuldt.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'E-mailskabeloner for support-sager',
    'feedback_emails.subheading'         => 'Tilpas automatiske e-mailnotifikationer sendt under sagsworkflowet. Brug krøllede parenteser til dynamiske værdier.',
    'feedback_emails.back_to_dashboard' => 'Tilbage til kontrolpanel for sager',
    'feedback_emails.email_subject'      => 'E-mailemne:',
    'feedback_emails.email_body'         => 'Skabelon for e-mailtekst:',
    'feedback_emails.save_template_btn' => 'Gem skabelon',
    'feedback_emails.placeholders_heading' => 'Tilgængelige pladsholdere',
    'feedback_emails.placeholders_desc' => 'Du kan bruge disse tags overalt i emnet eller brødteksten:',
    'feedback_emails.fixed_tags'         => 'Kerne-faste tags:',
    'feedback_emails.custom_tags'        => 'Brugerdefinerede skematags:',
    'feedback_emails.custom_tags_desc'   => 'Genereres automatisk fra felterne i sagernes formularbygger:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Administrer skema for feedbackformular',
    'feedback_schema.subheading'             => 'Konfigurer brugerdefinerede felter, datatyper, længdegrænser, undertyper, indstillinger og visningsstil.',
    'feedback_schema.settings_summary'       => 'Konfigurer formulartitel og ansvarsfraskrivelse',
    'feedback_schema.form_title_label'       => 'Formulartitel:',
    'feedback_schema.form_intro_label'       => 'Introduktion / Beskrivelse:',
    'feedback_schema.save_settings_btn'      => 'Gem formularindstillinger',
    'feedback_schema.edit_field_title'       => 'Rediger sagfelt:',
    'feedback_schema.add_field_title'        => '+ Tilføj nyt felt til supportformular',
    'feedback_schema.field_name_label'       => 'Feltmærkat / Navn:',
    'feedback_schema.data_type_label'        => 'Datatype:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Kort tekst)',
    'feedback_schema.type_text'              => 'TEXT (Langt afsnit / Besked)',
    'feedback_schema.type_int'               => 'INT (Heltal)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Ja/Nej-flag)',
    'feedback_schema.type_date'              => 'DATE (Kalenderdato)',
    'feedback_schema.subtype_label'          => 'Feltundertype / Inputvisningsstil:',
    'feedback_schema.subtype_standard'       => '-- Standard --',
    'feedback_schema.subtype_standard_lower'=> 'standard',
    'feedback_schema.options_label'          => 'Valgmuligheder (kommaseparerede eller én pr. linje):',
    'feedback_schema.options_help'           => 'Angiv valgmuligheder adskilt af kommaer eller linjeskift.',
    'feedback_schema.allow_multiple'         => 'Tillad flere valg (Flervalg)',
    'feedback_schema.boolean_format'         => 'Boolsk visningsformat:',
    'feedback_schema.max_length_label'       => 'Maksimal længde / Tegnbegrænsning (valgfrit):',
    'feedback_schema.is_required_label'      => 'Gør dette felt obligatorisk for indsendere',
    'feedback_schema.save_field_btn'         => 'Gem feltændringer',
    'feedback_schema.create_field_btn'       => 'Opret sagfelt',
    'feedback_schema.sub_email'              => 'E-mail',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Rullemenu (Select)',
    'feedback_schema.sub_radio'              => 'Radioknap-gruppe',
    'feedback_schema.sub_checkbox'           => 'Afkrydsningsfelt',
    'feedback_schema.sub_textarea'           => 'Tekstfelt med flere linjer',
    'feedback_schema.sub_number'             => 'Numerisk input',
    'feedback_schema.existing_fields_heading'=> 'Eksisterende sagfelter',
    'feedback_schema.th_move'                => 'Flyt',
    'feedback_schema.th_field_name'          => 'Feltnavn',
    'feedback_schema.th_data_type'           => 'Datatype',
    'feedback_schema.th_subtype'             => 'Undertype',
    'feedback_schema.th_required'            => 'Obligatorisk?',
    'feedback_schema.th_max_length'          => 'Maks. længde',
    'feedback_schema.th_created_by'          => 'Oprettet af',
    'feedback_schema.no_fields'              => 'Ingen brugerdefinerede sagfelter defineret endnu.',
    'feedback_schema.system_user'            => 'System',
    'feedback_schema.edit_btn'               => 'Rediger',
    'feedback_schema.delete_confirm'         => 'Slet dette felt og alle tilhørende svarværdier?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Administrer tabeller og skemaer',
    'manage_tables.subheading'           => 'Opret, inspicer, rediger eller slet dynamiske applikationstabeller og deres kololskemaer på en sikker måde.',
    'manage_tables.switcher_label'       => 'Vælg aktiv tabelskema:',
    'manage_tables.edit_metadata_btn'    => 'Rediger tabelmetadata',
    'manage_tables.delete_table_confirm'=> 'ADVARSEL: Sletning af denne tabel vil permanent fjerne alle kolonner og gemt indhold. Er du helt sikker?',
    'manage_tables.delete_table_btn'     => 'Slet tabel',
    'manage_tables.edit_table_summary'   => 'Rediger tabeldefinition:',
    'manage_tables.create_table_summary'=> '+ Opret ny dynamisk tabel',
    'manage_tables.table_name_label'     => 'Tabelvenligt navn:',
    'manage_tables.table_desc_label'     => 'Beskrivelse / Formål:',
    'manage_tables.save_table_btn'       => 'Gem tabelændringer',
    'manage_tables.create_table_btn'     => 'Opret tabelskema',
    'manage_tables.edit_col_summary'     => 'Rediger dynamisk kolonne:',
    'manage_tables.add_col_summary_prefix' => '+ Tilføj ny tabelkolonne for',
    'manage_tables.col_name_label'       => 'Kolonnenavn:',
    'manage_tables.type_text_long'       => 'TEXT (Langt afsnit)',
    'manage_tables.date_behavior_label' => 'Dato-søgeadfærd:',
    'manage_tables.date_bhv_manual'      => 'Databasedato (kun manuel indtastning)',
    'manage_tables.date_bhv_admin'       => 'Kun admindatoer',
    'manage_tables.date_bhv_all'         => 'Alle datoer inklusive admin',
    'manage_tables.req_toggle_label'     => 'Gør denne kolonne obligatorisk (håndhæver dataintastning)',
    'manage_tables.exclude_search_label'=> 'Udelad denne kolonne fra offentlig søgning (index.php)',
    'manage_tables.create_col_btn'       => 'Opret kolonne',
    'manage_tables.existing_cols_heading_prefix' => 'Eksisterende kolonner for',
    'manage_tables.th_public_search'     => 'Offentlig søgning?',
    'manage_tables.th_display_format'    => 'Visningsformat',
    'manage_tables.th_date_created'      => 'Oprettet dato',
    'manage_tables.no_columns_found'     => 'Ingen dynamiske kolonner defineret for denne tabel endnu.',
    'manage_tables.status_hidden'        => 'Skjult',
    'manage_tables.delete_col_confirm'   => 'ADVARSEL: Sletning af denne kolonne vil også fjerne alle tilhørende celledata på tværs af alle poster. Er du sikker?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Administrer e-mailskabeloner til brugernotifikationer',
    'user_emails.subheading'             => 'Tilpas e-maillayouts, der sendes, når brugere inviteres, eller når der sendes links til nulstilling af adgangskode.',
    'user_emails.select_template_label'=> 'Vælg skabelon der skal redigeres:',
    'user_emails.opt_invitation'         => 'Skabelon til brugerkonto-invitation',
    'user_emails.opt_reset'              => 'Skabelon til nulstilling af adgangskode / adgangslink',
    'currently_editing'                  => 'Redigerer i øjeblikket:',
    'user_emails.desc_invitation'        => 'Sendes automatisk, når en administrator opretter eller inviterer en ny bruger.',
    'user_emails.desc_reset'             => 'Sendes ved udløsning af nulstilling af adgangskode eller genudsendelse af adgangslink.',
    'user_emails.email_body_label'       => 'E-mailtekst:',
    'user_emails.back_to_creation'       => 'Tilbage til brugeroprettelse',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'E-mailskabeloner og udløsere for frivillige',
    'volunteer_emails.subheading'        => 'Konfigurer automatiske e-mailsvar for frivillige på forskellige trin i workflowet. Brug krøllede parenteser til dynamiske værdier.',
    'volunteer_emails.back_to_dashboard'=> 'Tilbage til frivilligansøgninger',
    'volunteer_emails.custom_tags_desc'  => 'Genereres automatisk fra felterne i formularbyggeren:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Administrer skema for frivilligformular',
    'volunteer_schema.subheading'        => 'Konfigurer brugerdefinerede felter, datatyper, undertyper, indstillinger og generelle præferencer for formularvisning.',
    'volunteer_schema.back_to_dashboard'=> 'Tilbage til frivilligansøgninger',
    'volunteer_schema.settings_summary'  => 'Konfigurer formulartitel og ansvarsfraskrivelse',
    'volunteer_schema.edit_field_title'  => 'Rediger felt:',
    'volunteer_schema.add_field_title'   => '+ Tilføj nyt felt til frivilligformular',
    'volunteer_schema.create_field_btn'  => 'Opret felt',
    'volunteer_schema.existing_fields_heading' => 'Eksisterende felter i frivilligformular',
    'volunteer_schema.no_fields'         => 'Ingen brugerdefinerede frivilligfelter defineret endnu.',
    'volunteer_schema.delete_confirm'    => 'Slet dette felt og alle tilhørende svarværdier?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Moderationskø og gennemgang af forslag',
    'moderate.subheading'                => 'Sammenlign brugerforslåede rettelser med aktive poster i dine autoriserede tabeller. Godkend, tilsidesæt eller afvis forslag.',
    'moderate.shortcut_label'            => 'Tip til tastaturgenveje:',
    'moderate.shortcut_desc'             => 'Tryk på Ctrl + Enter for at godkende hurtigt, eller Esc for at rydde tilsidesættelsesboksen!',
    'moderate.th_id_date'                => 'ID / Dato',
    'moderate.th_table_record'           => 'Tabel, post og kolonne',
    'moderate.th_comparison'             => 'Sammenligning (Live vs Forslag) og Dokumentation',
    'moderate.th_actions'                => 'Moderatorhandlinger',
    'moderate.no_suggestions'            => 'Ingen afventende forslag fundet på tværs af dine autoriserede modereringstabeller.',
    'moderate.by_label'                  => 'Af:',
    'moderate.guest_user'                => 'Gæst / Besøgende',
    'moderate.record_id_label'           => 'Post-ID:',
    'moderate.column_label'              => 'Kolonne:',
    'moderate.required_badge'            => 'Obligatorisk',
    'moderate.live_value_label'          => 'Aktuel live-værdi:',
    'moderate.empty_placeholder'         => '[Tom]',
    'moderate.proposed_value_label'      => 'Foreslået ændring:',
    'moderate.evidence_label'            => 'Dokumentation / Begrundelse:',
    'moderate.no_evidence'               => 'Ingen dokumentation eller begrundelse angivet.',
    'moderate.override_label'            => 'Tilsidesæt værdi:',
    'moderate.select_placeholder'        => '-- Vælg --',
    'moderate.historical_dates_title'    => 'Understøttede historiske datoer',
    'moderate.approve_confirm'           => 'Godkend og anvend denne værdi?',
    'moderate.decline_confirm'           => 'Afvis og kassér dette forslag?',
    'moderate.approve_btn'               => 'Godkend',
    'moderate.decline_btn'               => 'Afvis',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Håndtering af webstedsmeddelelser og annoncer',
    'notices.subheading'                 => 'Opret dynamiske alarmer, velkomstbannere eller målrettede meddelelser til specifikke brugerroller.',
    'notices.error_blank'                => 'Titel og indhold må ikke være tomme.',
    'notices.msg_created'                => 'Meddelelse oprettet succesfuldt!',
    'notices.msg_deleted'                => 'Meddelelse slettet.',
    'notices.create_heading'             => 'Opret ny meddelelse',
    'notices.title_label'                => 'Meddelelsestitel / Overskrift:',
    'notices.content_label'              => 'Meddelelsesindhold (HTML/tekst tilladt):',
    'notices.target_roles_label'         => 'Målgruppe (vælg roller eller alle):',
    'notices.role_everyone'              => 'Alle',
    'notices.role_public'                => 'Offentlig (Gæst)',
    'notices.role_users'                 => 'Brugere',
    'notices.role_moderators'            => 'Moderatorer',
    'notices.role_admins'                => 'Administratorer',
    'notices.dismissible_label'          => "Kan afvises (inkluderer 'X' lukke-knap)",
    'notices.display_order_label'        => 'Visningsrækkefølge:',
    'notices.publish_btn'                => 'Udgiv meddelelse',
    'notices.existing_heading'           => 'Aktive og eksisterende meddelelser',
    'notices.th_order'                   => 'Rækkefølge',
    'notices.th_title'                   => 'Titel',
    'notices.th_target_roles'            => 'Målroller',
    'notices.th_dismissible'             => 'Kan afvises',
    'notices.no_notices'                 => 'Ingen meddelelser oprettet endnu.',
    'notices.yes'                        => 'Ja',
    'notices.no_sticky'                  => 'Nej (Klæbende / Sticky)',
    'notices.delete_confirm'             => 'Slet denne meddelelse?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Globale webstedsindstillinger, moduler og tilladelser',
    'settings.subheading'                => 'Administrer kerneindstillinger, maildrivere, sikkerheds-/CAPTCHA-indstillinger, funktionsmoduler, vedligeholdelsestilstand, webstedsmeddelelser og rollematrix.',
    'settings.tab_core'                  => 'Kerne og mail',
    'settings.tab_modules'               => 'Moduler',
    'settings.tab_maintenance'           => 'Vedligeholdelse',
    'settings.tab_notices'               => 'Webstedsmeddelelser',
    'settings.tab_permissions'           => 'Roller og tilladelser',
    'settings.tab_audit'                 => 'Aktivitetslog',
    'settings.db_updates_heading'        => 'Databaseopdateringer',
    'settings.schema_current'            => 'Aktuel skemaversion:',
    'settings.schema_latest'             => 'Seneste tilgængelige version:',
    'settings.download_backup_btn'       => 'Download databasebackup',
    'settings.download_backup_desc'      => 'Gemmer en fuld .sql-fil på din computer. Opbevar den et sikkert sted, før du kører opdateringer.',
    'settings.schema_update_notice'      => 'Databaseopdateringer er tilgængelige. Download venligst en backup ovenfor, før du fortsætter.',
    'settings.migration_confirm'         => 'Har du downloadet en databasebackup? Dette vil anvende ventende skemaopdateringer.',
    'settings.update_db_btn'             => 'Opdater database',
    'settings.schema_uptodate'           => 'Databasen er opdateret.',
    'settings.core_sys_heading'          => 'Kernesystemindstillinger',
    'settings.sys_name_label'            => 'System- / Applikationsnavn:',
    'settings.default_lang_label'        => 'Standard webstedssprog:',
    'settings.default_lang_desc'         => 'Bruges til gæster og brugere, der ikke har indstillet en sprogpræference. Tilføj filer til lang/ (f.eks. da.php) for flere sprogmuligheder.',
    'settings.captcha_heading'           => 'Sikkerheds- og CAPTCHA-konfiguration',
    'settings.captcha_provider_label'    => 'CAPTCHA-udbyderengine:',
    'settings.captcha_none'              => 'Deaktiveret (Ingen CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Indstillinger for Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Indstillinger for Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Indstillinger for hCaptcha',
    'settings.site_key_label'            => 'Webstedsnøgle (Offentlig):',
    'settings.secret_key_label'          => 'Hemmelig nøgle (Privat):',
    'settings.mail_heading'              => 'Mail-leveringskonfiguration',
    'settings.mail_domain_label'         => 'Systemmail-domæne (Fallback):',
    'settings.mail_from_label'           => "Brugerdefineret 'Fra' (From) e-mailadresse:",
    'settings.mail_from_desc'            => 'En dedikeret adresse, der bruges som afsender for ugående e-mails.',
    'settings.mail_driver_label'         => 'Maildriver / Engine:',
    'settings.driver_native'             => 'Nativ mail (Lokal Postfix-relæ)',
    'settings.driver_smtp'               => 'Godkendt SMTP (PHPMailer)',
    'settings.smtp_heading'              => 'SMTP-serverindstillinger',
    'settings.smtp_host_label'           => 'SMTP-vært:',
    'settings.smtp_port_label'           => 'Port:',
    'settings.smtp_encryption_label'     => 'Kryptering:',
    'settings.enc_tls'                   => 'TLS (Port 587)',
    'settings.enc_ssl'                   => 'SSL (Port 465)',
    'settings.smtp_user_label'           => 'SMTP-brugernavn:',
    'settings.smtp_pass_label'           => 'SMTP-adgangskode (lad stå tom for at beholde den nuværende):',
    'settings.save_core_mail_btn'        => 'Gem kerne- og mailindstillinger',
    'settings.test_mail_heading'         => 'Test e-mailkonfiguration',
    'settings.test_email_label'          => 'Modtagers e-mailadresse:',
    'settings.send_test_btn'             => 'Send test-e-mail',
    'settings.modules_heading'           => 'Applikationsmodul-kontakter og ydelseskontrol',
    'settings.modules_subheading'        => 'Aktiver eller deaktiver funktioner for at optimere applikationens udførelsesydelse og tilpasse til specifikke implementeringskrav.',
    'settings.mod_users'                 => 'Brugeradministration og flerbrugeradgang',
    'settings.mod_users_desc'            => 'Aktiverer registrering, brugeradministration og flerbrugergodkendelse.',
    'settings.mod_leaderboard'           => 'Rangliste og gamification',
    'settings.mod_leaderboard_desc'      => 'Sporer transskriberingsindsats og uddeler stjernebedømmelsespunkter.',
    'settings.mod_leaderboard_note'      => '(Kræver brugeradministration og flerbrugeradgang)',
    'settings.mod_moderation'            => 'Moderationsworkflow',
    'settings.mod_moderation_desc'       => 'Aktiverer gennemgang af redigeringsforslag og moderationskø.',
    'settings.mod_volunteers'            => 'Frivilligportal og ansøgninger',
    'settings.mod_volunteers_desc'       => 'Aktiverer offentlig formular for frivillighensigt og adminkontrolpanel.',
    'settings.mod_feedback'              => 'Feedback-indsendelse',
    'settings.mod_feedback_desc'         => 'Aktiverer offentlig feedbackformular og tilhørende adminkontrolpanel.',
    'settings.save_modules_btn'          => 'Gem modulkonfiguration',
    'settings.maintenance_heading'       => 'Systemvedligeholdelsestilstand',
    'settings.maintenance_toggle'        => 'Aktiver vedligeholdelsestilstand (sæt webstedet offline)',
    'settings.maintenance_reason_label'  => 'Årsag / Besked til brugere:',
    'settings.maintenance_eta_label'     => 'Forventet tid for genåbning (ETA):',
    'settings.save_maintenance_btn'      => 'Gem vedligeholdelsesindstillinger',
    'settings.notices_heading'           => 'Webstedsmeddelelser og annoncer',
    'settings.add_notice_btn'            => '+ Tilføj ny meddelelse',
    'settings.no_notices'                => 'Ingen meddelelser konfigureret.',
    'settings.status_active'             => 'Aktiv',
    'settings.status_inactive'           => 'Inaktiv',
    'settings.notice_content_label'      => 'Indhold:',
    'settings.save_notice_btn'           => 'Gem meddelelse',
    'settings.permissions_heading'       => 'Dynamisk rolle- og tilladelsesmatrix',
    'settings.permissions_subheading'    => 'Tilladelser er grupperet efter systemfunktioner. Udvid sektioner for at konfigurere rettigheder og gem matrixen nedenfor.',
    'settings.th_role'                   => 'Rolle',
    'settings.th_capabilities'           => 'Rettigheder tildelt denne gruppe',
    'settings.save_permissions_btn'      => 'Gem tilladelsesmatrix',
    'settings.audit_heading'             => 'Browser for systemaktivitetslog',
    'settings.audit_subheading'          => 'Inspicer loggede sikkerhedshandlinger, dataintastning og moderering. Brug vedligeholdelsesindstillingerne nedenfor til at rydde logs om nødvendigt.',
    'settings.purge_all_confirm'         => '⚠️ ADVARSEL: Dette vil PERMANENT SLETTE ALLE SYSTEMAKTIVITETSLOGS. Er du sikker?',
    'settings.clear_all_audit_btn'       => 'Ryd alle aktivitetslogs',
    'settings.purge_records_confirm'     => 'Er du sikker på, at du vil rydde alle aktivitetslogs knyttet til poster?',
    'settings.clear_records_audit_btn'   => 'Ryd kun post-audit',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Tidsstempel',
    'settings.th_actor'                  => 'Aktør',
    'settings.th_action'                 => 'Handling',
    'settings.th_record_id'              => 'Post-ID',
    'settings.th_details'                => 'Detaljer',
    'settings.th_ip'                     => 'IP-adresse',
    'settings.no_audit_logs'             => 'Ingen aktivitetslogs fundet.',
    'settings.system_guest'              => 'System / Gæst',
    'settings.audit_limit_note'          => 'Viser de sidste 250 aktivitetslogposter.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Brugerkontoadministration og ranglistemoderering',
    'admin_users.subheading'             => 'Bekræft brugerstatus, tildel roller, tilsidesæt e-mails, udløs nulstilling af adgangskode eller geninvitationer, nulstil 2FA eller suspender konti.',
    'admin_users.manage_templates_btn'   => 'Administrer e-mailskabeloner',
    'admin_users.invite_user_btn'        => 'Inviter ny bruger',
    'admin_users.th_username'            => 'Brugernavn',
    'admin_users.th_email_override'      => 'E-mail og tilsidesættelse',
    'admin_users.th_role_assignment'     => 'Rolletildeling',
    'admin_users.th_score'               => 'Score',
    'admin_users.th_status'              => 'Status',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Handlinger og moderering',
    'admin_users.no_users'               => 'Ingen brugere fundet.',
    'admin_users.save_email_title'       => 'Gem ny e-mailadresse',
    'admin_users.verified_label'         => 'Bekræftet:',
    'admin_users.yes'                    => 'Ja',
    'admin_users.no'                     => 'Nej',
    'admin_users.protected_admin'        => 'Beskyttet primær administrator',
    'admin_users.update_btn'             => 'Opdater',
    'admin_users.status_active'          => 'Aktiv',
    'admin_users.status_suspended'       => 'Suspenderet',
    'admin_users.enabled'                => 'Aktiveret',
    'admin_users.disabled'               => 'Deaktiveret',
    'admin_users.set_score_btn'          => 'Indstiller score',
    'admin_users.resend_invite_confirm' => 'Genindsend konto-invitations-e-mail til denne bruger?',
    'admin_users.resend_invite_btn'      => 'Genindsend invitation',
    'admin_users.reset_pwd_confirm'      => 'Send link til nulstilling af adgangskode til denne bruger?',
    'admin_users.reset_password_btn'     => 'Nulstil adgangskode',
    'admin_users.suspend_confirm'        => 'Suspender bruger og tilbagekald adgang på grund af misbrug?',
    'admin_users.suspend_btn'            => 'Suspender',
    'admin_users.reactivate_btn'         => 'Genaktiver',
    'admin_users.reset_2fa_confirm'      => 'Nulstil og deaktiver 2FA for denne bruger?',
    'admin_users.reset_2fa_btn'          => 'Nulstil 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Tilbage til kontrolpanel for sager',
    'view_ticket.ticket_heading_prefix'=> 'Sag',
    'view_ticket.support_request'      => 'Supportanmodning',
    'view_ticket.submitted_by'         => 'Indsendt af:',
    'view_ticket.on_date'              => 'den',
    'view_ticket.submitted_fields'     => 'Indsendte formularfelter:',
    'view_ticket.ticket_status_label'  => 'Sagstatus:',
    'view_ticket.status_pending'       => 'Afventer',
    'view_ticket.status_progress'      => 'I gang',
    'view_ticket.status_completed'     => 'Afsluttet',
    'view_ticket.status_rejected'      => 'Afvist',
    'view_ticket.dialogue_heading'     => 'Samtaletråd',
    'view_ticket.no_replies'           => 'Ingen svar registreret endnu.',
    'view_ticket.admin_label'          => 'Administrator',
    'view_ticket.staff'                => 'Personale',
    'view_ticket.post_reply_heading'   => 'Skriv svar og giv besked til indsender',
    'view_ticket.reply_placeholder'    => 'Skriv dit svar her...',
    'view_ticket.send_reply_btn'       => 'Send svar og e-mail til indsender',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Frivilligansøgninger og workflow',
    'volunteer_dashboard.subheading'         => 'Gennemgå ansøgninger, planlæg samtaler, registrer samtatenotater og godkend kandidater i systemet.',
    'volunteer_dashboard.manage_emails_btn' => 'Administrer e-mailskabeloner',
    'volunteer_dashboard.manage_schema_btn' => 'Administrer formularskema',
    'volunteer_dashboard.th_status'          => 'Status',
    'volunteer_dashboard.th_name'            => 'Navn',
    'volunteer_dashboard.th_interview_notes'=> 'Samtale / Notater',
    'volunteer_dashboard.no_submissions'     => 'Ingen frivilligansøgninger fundet.',
    'volunteer_dashboard.volunteer_prefix'   => 'Frivillig',
    'volunteer_dashboard.chat_label'         => 'Chat:',
    'volunteer_dashboard.notes_label'        => 'Notater:',
    'volunteer_dashboard.no_notes'           => 'Ingen notater endnu',
    'volunteer_dashboard.chat_notes_btn'     => 'Chat og notater',
    'volunteer_dashboard.accept_title'       => 'Godkend via brugerinvitationssystem',
    'volunteer_dashboard.accept_invite_btn'  => 'Godkend og send invitation',
    'volunteer_dashboard.delete_confirm'     => 'Slet denne frivilligpost?',
    'volunteer_dashboard.modal_heading'      => 'Administrer kandidatsamtale og notater',
    'volunteer_dashboard.modal_status_label'=> 'Ansøgningsstatus:',
    'volunteer_dashboard.status_pending'     => 'Afventer gennemgang',
    'volunteer_dashboard.status_chat'        => 'Chat planlagt',
    'volunteer_dashboard.status_accepted'    => 'Godkendt',
    'volunteer_dashboard.status_rejected'    => 'Afvist',
    'volunteer_dashboard.modal_date_label'   => 'Planlagt dato og tid for chat:',
    'volunteer_dashboard.modal_notes_label'  => 'Samtatenoter / Mødenoter:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Registrer chat-feedback her...',
    'volunteer_dashboard.save_changes_btn'   => 'Gem ændringer',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Forbudt: Offentlig visning er ikke aktiveret.',
    'api_search.error_unauthorized_table' => 'Uautoriseret tabeladgang.',
    'api_search.no_records'              => 'Ingen poster fundet i dette arkiv.',
    'api_search.history_btn'             => 'Historik',
    'api_search.suggest_edit_btn'        => 'Foreslå redigering',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Tilbage til offentlig forside',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ------------------------------------------------------------------
    'feedback.hp_label'              => 'Lad stå tom',
    'feedback.first_name_label'      => 'Fornavn:',
    'feedback.surname_label'         => 'Efternavn:',
    'feedback.email_label'           => 'E-mailadresse:',
    'feedback.subject_label'         => 'Emne / Forespørgselstitel:',
    'feedback.required_title'        => 'Obligatorisk felt',
    'feedback.select_placeholder'    => '-- Vælg --',
    'feedback.multi_select_hint'     => 'Hold Ctrl eller Cmd nede for at vælge flere.',
    'feedback.submit_btn'            => 'Indsend sag',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Sikkerhedsfejl: Mistænkelig klientsignatur.',
    'security_engine.err_access_denied'    => 'Sikkerhedsfejl: Adgang nægtet.',
    'security_engine.err_rate_limit'       => 'For mange anmodninger fra denne IP-adresse. Prøv igen senere.',
    'security_engine.err_excessive_links'  => 'Indsendelse blokeret på grund af for mange links.',
    'security_engine.err_complete_captcha' => 'Fuldfør venligst CAPTCHA-sikkerhedskontrollen.',
    'security_engine.err_captcha_failed'   => 'CAPTCHA-verifikation mislykkedes. Prøv igen.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Installation fuldført',
    'install.complete_heading'           => 'Installation fuldført',
    'install.complete_desc'              => 'Dette websted er allerede konfigureret. Installeren er blevet låst for at forhindre genudførelse.',
    'install.login_link'                 => 'Log ind',
    'install.home_link'                  => 'Gå til websted',
    'install.delete_folder_hint'         => 'For øget sikkerhed kan du slette eller omdøbe mappen <code>install</code>.',
    'install.msg_db_ready'               => 'Databasen er klar. Opret din administratorkonto for at afslutte.',
    'install.err_config_load'            => 'Kunne ikke bruge den eksisterende konfiguration:',
    'install.err_write_permission'       => 'PHP kan ikke oprette filer i denne projektmappe.',
    'install.detail_prefix'              => 'Detaljer:',
    'install.err_db_required'            => 'Databasenavn og databasebrugernavn er påkrævet.',
    'install.err_db_not_empty'           => 'Denne database er ikke tom. Brug en ny tom database (eller ryd alle tabeller) og prøv igen.',
    'install.msg_schema_imported'        => 'Database tilsluttet og skema importeret. Opret din administratorkonto.',
    'install.err_complete_db_first'      => 'Fuldfør databasetrinnet først.',
    'install.err_admin_required'         => 'Alle administratorfelter er påkrævet.',
    'install.err_invalid_email'          => 'Ugyldig e-mailadresse.',
    'install.err_password_length'        => 'Adgangskoden skal være på mindst 8 tegn.',
    'install.err_passwords_match'        => 'Adgangskoderne stemmer ikke overens.',
    'install.err_admin_save_failed'      => 'Kunne ikke gemme administratorkontoen. Tjek tabelstrukturen for brugere.',
    'install.msg_installation_complete' => 'Installation fuldført.',
    'install.page_title'                 => 'Installation — Sognearkiv',
    'install.heading'                    => 'Installation',
    'install.subheading'                 => 'Første opsætning <strong>kun for denne applikationsmappe</strong>. Brug en tom MySQL-database.',
    'install.done_heading'               => 'Færdig',
    'install.done_message'               => 'Installation fuldført. Installeren er nu låst.',
    'install.admin_heading'              => 'Webstedets administratorkonto',
    'install.admin_subheading'           => 'Dette er loginoplysningerne til <strong>dette websted</strong> (ikke en databasekonto).',
    'install.admin_username_label'       => 'Administratorbrugernavn',
    'install.admin_email_label'          => 'Administratore-mail',
    'install.admin_password_label'       => 'Administratoradgangskode (min. 8 tegn)',
    'install.admin_confirm_password_label' => 'Bekræft administratoradgangskode',
    'install.finish_btn'                 => 'Fuldfør installation',
    'install.db_heading'                 => 'Databaseforbindelse',
    'install.db_hint'                    => 'Brug MySQL-oplysninger fra dit <strong>hosting-kontrolpanel</strong>. Dette er ikke dit webstedsadministratorlogin.',
    'install.db_host_label'              => 'Databasevært',
    'install.db_name_label'              => 'Databasenavn',
    'install.db_user_label'              => 'Databasebrugernavn',
    'install.db_pass_label'              => 'Databaseadgangskode',
    'install.db_submit_btn'              => 'Opret tabeller og fortsæt',
    'install.req_heading'                => '1. Krav',
    'install.req_php'                    => 'PHP 8.0+ (registreret %s)',
    'install.req_pdo'                    => 'PDO MySQL-udvidelse',
    'install.req_logs'                   => 'Skrivbar logmappe (eller projektmappe)',
    'install.req_probe'                  => 'Evne til at oprette filer i denne projektmappe',
    'install.continue_btn'               => 'Fortsæt',
    'install.req_fail_msg'               => 'Ret venligst de mislykkede tjek og genindlæs denne side.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Ranglistevisning',
    'leaderboard.heading'         => 'Fællesskabets deltagelsesrangliste',
    'leaderboard.subheading'      => 'Anerkendelse af indsatsen fra vores fællesskabsmedlemmer, der hjælper med at indsamle, transkribere eller administrere databaseposter.',
    'leaderboard.th_rank'         => 'Rang',
    'leaderboard.th_contributor'  => 'Bidragyder',
    'leaderboard.th_role'         => 'Rolle',
    'leaderboard.th_score'        => 'Score',
    'leaderboard.no_users'        => 'Ingen aktive brugere fundet på ranglisten endnu.',
    'leaderboard.medal_gold'      => 'Guldmedalje',
    'leaderboard.medal_silver'    => 'Sølvmedalje',
    'leaderboard.medal_bronze'    => 'Bronzemedalje',
    'leaderboard.medal_ribbon'    => 'Niveau 4-bånd',
    'leaderboard.medal_rosette'   => 'Niveau 5-roset',
    'leaderboard.medal_trophy'    => 'Niveau 6-trofæ',
    'leaderboard.medal_star'      => 'Niveau 7-stjerne',
    'leaderboard.medal_military'  => 'Niveau 8-militærmedalje',
    'leaderboard.medal_glowing'   => 'Niveau 9-glødende stjerne',
    'leaderboard.medal_crown'     => 'Niveau 10-krone',
    'leaderboard.you_badge'       => '(Dig)',
    'leaderboard.default_role'    => 'Bruger',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Sognearkivalier samlet fra historiske kilder i det offentlige rum.',
    'footer.software_notice'  => 'Open source-softwareplatform licenseret under MIT.',
    'footer.rights_reserved'  => 'Alle rettigheder forbeholdes.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Sognearkivdatabase',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Luk meddelelse',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Ingen post angivet.',
    'record_history.exit_not_found'        => 'Post ikke fundet.',
    'record_history.heading_prefix'        => 'Historik og aktivitetslog: Post',
    'record_history.return_btn'            => 'Tilbage',
    'record_history.directory_table_label'=> 'Arkivtabel:',
    'record_history.subheading_lifecycle' => 'Viser den sociale livscyklus af ændringer, forslag og dokumentation knyttet direkte til denne post.',
    'record_history.snapshot_heading'      => 'Øjebliksbillede af aktuelle live-værdier',
    'record_history.empty_value'           => '[Tom]',
    'record_history.timeline_heading'      => 'Hændelses- og aktivitetidslinje',
    'record_history.no_history'            => 'Ingen specifikke historiske audithændelser registreret for denne post endnu.',
    'record_history.purge_confirm'         => 'Slet denne specifikke aktivitetslogpost?',
    'record_history.purge_btn'             => 'Ryd log',
    'record_history.actor_label'           => 'Aktør:',
    'record_history.system_guest'          => 'System / Gæst',
    'record_history.target_column'         => 'Målkolonne:',
    'record_history.proposed_value'        => 'Foreslået værdi:',
    'record_history.reasoning_evidence'    => 'Begrundelse / Dokumentation:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Database opdateret succesfuldt! Anvendte %d migrationer.',
    'update_database.msg_uptodate'     => 'Databasen er allerede opdateret.',
    'update_database.err_failed'       => 'Migration mislykkedes:',
    'update_database.page_title'       => 'Systemopdatering påkrævet — Sognearkiv',
    'update_database.heading'          => '⚠️ Systemopdatering påkrævet',
    'update_database.subheading'       => 'Applikationsdatabaseskemaet er forældet og kræver en skemaopdatering, før normal drift kan fortsætte.',
    'update_database.current_version'  => 'Aktuel skemaversion:',
    'update_database.latest_version'   => 'Seneste tilgængelige version:',
    'update_database.proceed_login'    => 'Gå til login-side',
    'update_database.confirm_prompt'   => 'Har du taget backup af din database? Klik på OK for at anvende ventende skemaopdateringer.',
    'update_database.update_btn'       => 'Opdater database nu',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Ugyldige legitimationsoplysninger eller begrænset kontoadgang.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'Obligatorisk felt \'%s\' må ikke være tomt.',
    'save_data_entry.audit_created_prefix' => 'Post oprettet i tabel med ID %d.',
    'save_data_entry.msg_success'          => 'Post tilføjet succesfuldt!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Spam opdaget. Indsendelse afvist.',
    'save_public_suggestion.err_field_required' => 'Dette felt er obligatorisk og kan ikke indsendes tomt.',
    'save_public_suggestion.msg_success'        => 'Dit redigeringsforslag er blevet indsendt succesfuldt til moderationskøen. Mange tak!',
    'save_public_suggestion.err_failed_submit'  => 'Kunne ikke indsende redigeringsforslag. Prøv igen.',
    'save_public_suggestion.err_invalid_column' => 'Ugyldig kolonne angivet.',
    'save_public_suggestion.err_invalid_params' => 'Ugyldige parametre for postindsendelse.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'ÅÅÅÅ-MM-DD (eller delvist år)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/ÅÅÅÅ (eller delvist år)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/ÅÅÅÅ (eller delvist år)',
    'data_entry.no_tables_heading'    => '⚠️ Ingen databasetabeller fundet',
    'data_entry.no_tables_desc'       => 'Der er i øjeblikket ingen aktive databasetabeller konfigureret til dataintastning.',
    'data_entry.admin_tables_prompt'  => 'Som administrator skal du gå til <strong>Administrer tabeller</strong> for at oprette en tabel og tilføje en kolonne, før du indtaster poster.',
    'data_entry.go_manage_tables'     => 'Gå til Administrer tabeller',
    'data_entry.contact_admin_tables' => 'Kontakt venligst en administrator for at konfigurere tabeller og kolonner.',
    'data_entry.no_cols_heading'      => '⚠️ Ingen kolonner konfigureret',
    'data_entry.no_cols_desc'         => 'Der findes tabeller i systemet, men der er ikke defineret nogen datakolonner for den aktive tabel.',
    'data_entry.admin_cols_prompt'    => 'Som administrator skal du gå til <strong>Administrer tabeller</strong> for at tilføje mindst én kolonne.',
    'data_entry.contact_admin_cols'   => 'Kontakt venligst administrator for at konfigurere kolonner for denne tabel.',
    'data_entry.active_table_label'   => 'Aktiv dataintastningstabel:',
    'data_entry.add_entry_summary'    => '➕ Tilføj ny dataintastning (Klik for at udvide/skjule)',
    'data_entry.bool_yes_true'        => 'Ja / Sand',
    'data_entry.bool_no_false'        => 'Nej / Falsk',
    'data_entry.bool_male'            => 'Mand',
    'data_entry.bool_female'          => 'Kvinde',
    'data_entry.bool_true'            => 'Sand',
    'data_entry.bool_false'           => 'Falsk',
    'data_entry.bool_tick'            => '✔ (Tjekket)',
    'data_entry.bool_cross'           => '✘ (Kryds)',
    'data_entry.date_title_hint'      => 'Accepterer fulde eller delvise datoer (f.eks. 1842 eller 1842-05)',
    'data_entry.enter_value_placeholder' => 'Indtast værdi...',
    'data_entry.submit_data_btn'      => 'Indsend data',
    'data_entry.shortcuts_tip'        => '💡 Tip: Tryk på <strong>Ctrl + Enter</strong> for at indsendte eller <strong>Esc</strong> for at rydde det aktuelle felt.',
    'data_entry.dup_heading'          => '⚠️ Advarsel om mulig dublet',
    'data_entry.dup_desc'             => 'Vi fandt lignende poster i systemet:',
    'data_entry.dup_item_format'      => 'Post-ID: %d — Værdi: %s',
    'data_entry.dup_prompt'           => 'Vil du fortsætte og gemme denne dubletpost alligevel?',
    'data_entry.dup_confirm_btn'      => 'Ja, bekræft og gem dublet',
    'data_entry.search_summary'       => '🔍 Søg og filtrer eksisterende poster (Klik for at udvide/skjule)',
    'data_entry.date_to_label'        => 'til',
    'data_entry.filter_all_option'    => '-- Alle --',
    'data_entry.filter_placeholder'   => 'Filtrer...',
    'data_entry.apply_filters_btn'    => 'Anvend søgefiltre',
    'data_entry.reset_filter_btn'     => 'Nulstil filter',
    'data_entry.csv_entire_btn'       => 'Download hele CSV',
    'data_entry.json_entire_btn'      => 'Download hele JSON',
    'data_entry.copy_entire_btn'      => 'Kopier hele tabellen',
    'data_entry.csv_filtered_btn'     => 'Download filtreret CSV',
    'data_entry.json_filtered_btn'     => 'Download filtreret JSON',
    'data_entry.copy_filtered_btn'    => 'Kopier filtreret tabel',
    'data_entry.clipboard_alert'      => 'Tabeldata kopieret! Du kan indsætte dem i Excel eller Google Sheets.',
    'data_entry.existing_records_heading' => 'Tabel over eksisterende poster',
    'data_entry.th_added_by'          => 'Tilføjet af',
    'data_entry.th_date_created'      => 'Oprettet dato',
    'data_entry.no_records'           => 'Ingen poster fundet.',
    'data_entry.na_value'             => 'I/T',
    'data_entry.page_label'           => 'Side:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Adgangskendelsens gendannelse',
    'forgot_password.heading'         => 'Nulstil din adgangskode',
    'forgot_password.subheading'      => 'Indtast din kontos e-mailadresse nedenfor, så sender vi dig et sikkert link til nulstilling af adgangskode.',
    'forgot_password.email_label'     => 'E-mailadresse:',
    'forgot_password.submit_btn'      => 'Send nulstillingslink',
    'forgot_password.back_login_link' => 'Tilbage til login',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Brugerlogin',
    'login.heading'              => 'Log ind på konto',
    'login.username_label'       => 'Brugernavn eller e-mail:',
    'login.password_label'       => 'Adgangskode:',
    'login.submit_btn'           => 'Log ind',
    'login.forgot_password_link' => 'Glemt adgangskode?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Velkommen — Kontopsætningsguide',
    'onboarding.heading'           => 'Velkommen til holdet!',
    'onboarding.subheading'        => 'Før du går i gang, bedes du bruge et øjeblik på at konfigurere dine regionale visnings- og privatlivspræferencer. Du kan ændre dem i din profil når som helst.',
    'onboarding.timezone_label'    => 'Tidszone / Region:',
    'onboarding.date_format_label' => 'Dato-visningsformat:',
    'onboarding.time_format_label' => 'Urformat (tidsvisning):',
    'onboarding.time_24'          => '24-timers (f.eks. 16:07)',
    'onboarding.time_12'          => '12-timers AM/PM (f.eks. 04:07 PM)',
    'onboarding.time_none'        => 'Kun dato (skjul tid helt)',
    'onboarding.attribution_label' => 'Præference for ranglistevisning:',
    'onboarding.attribution_desc1' => 'Styrer, hvordan dit navn vises på den offentlige rangliste og i poster.',
    'onboarding.attr_anon_title'   => 'Anonym:',
    'onboarding.attr_anon_text'    => 'Viser initialer og et tilfældigt nummer til alle.',
    'onboarding.attr_public_title' => 'Offentlig:',
    'onboarding.attr_public_text'  => 'Viser dit fulde navn til alle.',
    'onboarding.attr_vol_title'    => 'Kun frivillige:',
    'onboarding.attr_vol_text'     => 'Viser initialer offentligt, men dit fulde navn til indloggede frivillige, moderatorer og administratorer.',
    'onboarding.attr_opt_anon'     => 'Anonym (Initialer og tilfældigt nummer)',
    'onboarding.attr_opt_public'   => 'Offentlig (Vis fulde navn)',
    'onboarding.attr_opt_vol'      => 'Kun frivillige',
    'onboarding.submit_btn'        => 'Gem præferencer og fortsæt',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Brugerprofiladministration',
    'profile.heading'              => 'Brugerprofil og sikkerhed',
    'profile.personal_details_heading' => 'Personlige oplysninger',
    'profile.language_label'       => 'Foretrukket sprog:',
    'profile.lang_site_default'    => 'Webstedets standard',
    'profile.update_details_btn'   => 'Opdater personlige oplysninger',
    'profile.email_heading'        => 'E-mailadresse',
    'profile.current_email_label'  => 'Aktuel e-mail:',
    'profile.email_verified'       => '(Bekræftet)',
    'profile.email_unverified'     => '(Ubekræftet - Tjek din indbakke)',
    'profile.change_email_label'   => 'Skift e-mailadresse:',
    'profile.aria_new_email'       => 'Ny e-mailadresse',
    'profile.update_email_btn'     => 'Opdater e-mail og bekræft',
    'profile.password_heading'     => 'Skift adgangskode',
    'profile.current_password_label' => 'Nuværende adgangskode:',
    'profile.new_password_label'   => 'Ny adgangskode (min. 8 tegn):',
    'profile.confirm_password_label' => 'Bekræft ny adgangskode:',
    'profile.show_passwords_label' => 'Vis adgangskoder med almindelig tekst',
    'profile.update_password_btn'  => 'Opdater adgangskode',
    'profile.tfa_heading'          => 'To-faktor-godkendelse (2FA)',
    'profile.tfa_status_label'     => 'Status:',
    'profile.tfa_enabled'          => 'Aktiveret',
    'profile.tfa_disabled'         => 'Deaktiveret',
    'profile.setup_tfa_btn'        => 'Opsæt Google Authenticator',
    'profile.tfa_active_desc'      => '2FA beskytter aktivt dit kontologin.',
    'profile.backup_codes_heading' => 'Dine nye sikkerhedskopikoder',
    'profile.download_codes_btn'   => 'Download nye koder som .txt-fil',
    'profile.generate_codes_confirm' => 'Er du sikker? Dette vil gøre alle eksisterende bakkoder ugyldige.',
    'profile.generate_codes_btn'   => 'Generer nye bakkoder',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Brugerregistrering',
    'register.heading'        => 'Registrer ny konto',
    'register.username_label' => 'Brugernavn:',
    'register.submit_btn'     => 'Registrer',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Opsætnings-token er ugyldigt eller mangler.',
    'set_password.exit_expired_token'        => 'Dette adgangskodelink er ugyldigt eller er udløbet.',
    'set_password.proceed_login_btn'         => 'Gå til login-side',
    'set_password.aria_region'               => 'Indstil adgangskode',
    'set_password.heading_format'            => 'Indstil adgangskode for %s',
    'set_password.subheading_format'         => 'Velkommen til din nye konto, %s! Vælg venligst din adgangskode nedenfor.',
    'set_password.new_password_label'        => 'Ny adgangskode (min. 8 tegn):',
    'set_password.confirm_password_label'    => 'Bekræft adgangskode:',
    'set_password.show_password_label'       => 'Vis adgangskode',
    'set_password.save_password_btn'         => 'Gem adgangskode',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => '2FA-opsætningsguide',
    'setup_2fa.heading'          => 'Opsæt Google Authenticator',
    'setup_2fa.subheading'       => 'Scan QR-koden nedenfor med din godkendelses-app.',
    'setup_2fa.qr_alt'           => 'QR-kode til 2FA-opsætning',
    'setup_2fa.manual_prompt'    => 'Eller indtast denne hemmelige nøgle manuelt:',
    'setup_2fa.backup_heading'   => 'Nødgenoprettelseskoder',
    'setup_2fa.backup_desc'      => 'Opbevar disse bakkoder et sikkert sted. Hver kode kan kun bruges <strong>én gang</strong>, hvis du mister adgangen til din app:',
    'setup_2fa.download_btn'     => 'Download koder som .txt-fil',
    'setup_2fa.code_label'       => 'Indtast den 6-cifrede kode fra appen for at verificere og aktivere:',
    'setup_2fa.aria_code_input'  => '6-cifret bekræftelseskode',
    'setup_2fa.submit_btn'       => 'Verificer og aktiver 2FA',
    'setup_2fa.cancel_link'      => 'Annuller og retur til profil',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Foreslå redigering',
    'suggest_edit.heading_prefix'       => 'Foreslå redigering for post',
    'suggest_edit.return_btn'           => 'Tilbage til post',
    'suggest_edit.success_msg_suffix'   => 'Du kan indsende en anden redigering nedenfor eller bruge returlinket ovenfor, når du er færdig.',
    'suggest_edit.current_values_heading' => 'Aktuelle værdier:',
    'suggest_edit.empty_label'          => '(tom)',
    'suggest_edit.submit_heading'       => 'Indsend ny foreslået værdi og dokumentation',
    'suggest_edit.confirm_prompt'       => 'Er du sikker på, at du vil indsende dette redigeringsforslag til administratorgennemgang?',
    'suggest_edit.select_column_label'  => 'Vælg kolonne der skal redigeres:',
    'suggest_edit.reasoning_label'      => 'Dokumentation / Begrundelse / Kildehenvisning:',
    'suggest_edit.reasoning_placeholder'=> 'Giv kontekst, kildecitat eller årsag til denne ændring...',
    'suggest_edit.submit_btn'           => 'Indsend forslag til gennemgang',
    'suggest_edit.proposed_value_label' => 'Foreslået ny værdi:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => '2FA-verifikation',
    'verify_2fa.heading'         => 'To-faktor-godkendelse',
    'verify_2fa.subheading'      => 'Indtast den 6-cifrede kode fra din godkendelses-app eller en sikkerhedskopikode.',
    'verify_2fa.code_label'      => 'Bekræftelseskode / Sikkerhedskode:',
    'verify_2fa.aria_code_input' => 'Indtast bekræftelseskode eller sikkerhedskode',
    'verify_2fa.submit_btn'      => 'Verificer og log ind',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Ingen bekræftelsestoken angivet.',
    'verify_email.err_invalid_token'    => 'Ugyldig bekræftelsestoken.',
    'verify_email.msg_already_verified' => 'Din e-mail er allerede bekræftet. Du kan logge ind.',
    'verify_email.err_expired_token'    => 'Dette bekræftelseslink er udløbet (24-timers grænse overskredet). Registrer dig igen eller anmod om et nyt link.',
    'verify_email.msg_success'          => 'E-mail bekræftet succesfuldt! Din konto er nu aktiv. Fortsæt venligst til login.',
    'verify_email.err_update_failed'    => 'Der opstod en fejl under bekræftelsen af din e-mail. Prøv igen.',
    'verify_email.aria_region'          => 'E-mailbekræftelsesstatus',
    'verify_email.heading'              => 'E-mailbekræftelsesstatus',
    'verify_email.login_btn'            => 'Klik her for at logge ind',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Frivilligformular',
    'volunteer.honeypot_label'       => 'Lad dette felt stå tomt:',
    'volunteer.required_field_title'=> 'Obligatorisk felt',
    'volunteer.multi_select_hint'    => 'Hold Ctrl eller Cmd nede for at vælge flere.',
    'volunteer.submit_btn'           => 'Indsend frivilligansøgning',
];
