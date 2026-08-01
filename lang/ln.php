<?php
// lang/ln.php - Lingala (Lingála)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Kɔtá',
    'nav.logout'                 => 'Kobima',
    'nav.feedback'               => 'Biteni ya makanisi',
    'nav.volunteer'              => 'Kɔtá na basungi',
    'nav.leaderboard'            => 'Molɔngɔ ya ba mabonzi',
    'nav.search'                 => 'Boluka',
    'nav.settings'               => 'Bokambi ya ebongiseli',
    'nav.high_contrast'          => 'Bokeseni ya likolo',
    'nav.low_contrast'           => 'Bokeseni ya nse',
    'nav.welcome'                => 'Mbote,',
    'nav.data_entry'             => 'Kotisa ba donné',
    'nav.moderation'             => 'Bokɛngɛli',
    'nav.invite_user'            => 'Pɛpisa moto',
    'nav.manage_users'           => 'Kokambi bato',
    'nav.manage_tables'          => 'Kokambi mesa',
    'nav.volunteer_dashboard'    => 'Esika ya basungi',
    'nav.feedback_dashboard'     => 'Esika ya makanisi',
    'nav.leaderboard_score'      => 'Mabonzi ya molɔngɔ',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Boluki ya biteni ebele',
    'search.reset'               => 'Bongola boluki',
    'search.export_csv'          => 'Bimisa na CSV',
    'search.no_records'          => 'Ezwama eloko te na mesa oyo.',
    'search.load_error'          => 'Mwa pasi ezwami na kozwa biloko. Meka lisusu.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Tinda',
    'btn.cancel'                 => ' Tiká',
    'btn.save'                   => 'Bomba',
    'btn.delete'                 => 'Kufa',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Matondi mingi! Makanisi na yo matindami malamu.',
    'feedback.error_all_fields'   => 'Ma sika nionso esengeli ezala na biloko.',
    'feedback.error_invalid_email' => 'Kotisa adresi ya email ya malamu.',
    'feedback.error_save_failed'  => 'Mwa pasi ezwami na kobomba makanisi, meka lisusu.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Mesa ya stɔru ezwama te',
    'index.no_tables_desc'             => ' Sikoyo, ezali na mesa ya stɔru te eyebani na ebongiseli.',
    'index.admin_create_table_guide'   => 'Lokola mokambi, kende na <strong>Kokambi mesa</strong> mpo na kosala mesa mpe kotya ata molɔngɔ moko liboso ya komonisa to kotisa ba mbonimboni.',
    'index.go_to_manage_tables'        => 'Kende na Kokambi mesa',
    'index.contact_admin_tables'       => 'Sɛngá mokambi asalela mesa mpe molɔngɔ.',
    'index.guest_login_tables_guide'   => 'Sɛngá <a href=":login_link">okɔtá</a> to kende epai ya mokambi mpo na kobongisa mesa.',
    'index.no_columns_heading'         => 'Molɔngɔ moko te eyebani',
    'index.no_columns_desc'            => 'Bamesa ezali na ebongiseli, kasi molɔngɔ ezali te na mesa ya sikoyo.',
    'index.admin_add_columns_guide'    => 'Lokola mokambi, kende na <strong>Kokambi mesa</strong> mpo na kotya molɔngɔ na mesa na yo.',
    'index.contact_admin_columns'      => 'Sɛngá mokambi asalela molɔngɔ ya mesa oyo.',
    'index.select_directory_database'  => 'Pɔnɔ stɔru ya ndako:',
    'index.opt_yes_true'               => 'Ee / Ya solo',
    'index.opt_no_false'               => 'Te / Ya lokuta',
    'index.opt_male'                   => 'Mobali',
    'index.opt_female'                 => 'Mwasi',
    'index.opt_true'                   => 'Ya solo',
    'index.opt_false'                  => 'Ya lokuta',
    'index.opt_tick'                   => '✔ (Emonani)',
    'index.opt_cross'                  => '✘ (Ebuki)',
    'index.option_all'                 => '-- Nionso --',
    'index.date_to_label'              => 'kaka',
    'index.search_placeholder'         => 'Boluka...',
    'index.download_entire_csv'        => 'Kita CSV mobimba',
    'index.download_entire_json'       => 'Kita JSON mobimba',
    'index.copy_entire_table'          => 'Kopa mesa mobimba',
    'index.download_filtered_csv'      => 'Kita CSV oyo eponami',
    'index.download_filtered_json'     => 'Kita JSON oyo eponami',
    'index.copy_filtered_table'        => 'Kopa mesa oyo eponami',
    'index.th_record_id'               => 'ID ya Mbonimboni',
    'index.th_created_by'              => ' Esalemi na',
    'index.th_date_added'              => 'Mikolo ebakisami',
    'index.th_actions'                 => 'Misala',
    'index.modal_heading'              => 'Tinda mbongwana',
    'index.modal_desc'                 => 'Pesa mbongwana to sango mosusu mpo na mbonimboni oyo. Bato ba bokɛngɛli bakotalela yango.',
    'index.modal_target_column'        => 'Molɔngɔ ya ntina:',
    'index.modal_proposed_value'       => 'Motuya ya kobongisa:',
    'index.modal_input_placeholder'    => 'Kotisa sango ya sika...',
    'index.modal_submit_btn'           => 'Tinda mbongwana',
    'index.clipboard_success'          => 'Sango ya mesa ekopiami na esika ya kobomba! Okoki kɔ́tisa na Excel to Google Sheets.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Fɔlɔ ya libenga ya mosungi ya sika',
    'create_user.subheading'           => 'Oyo ekozala na libenga ya mikolo 24 mpe ekotindama na email ya moto.',
    'create_user.first_name'           => 'Kombo ya liboso:',
    'create_user.surname'              => 'Kombo ya tata:',
    'create_user.username_label'       => 'Kombo ya mosangani (Soki olingi):',
    'create_user.username_placeholder' => 'Tiká goullo mpo na kosala moko na ye moko',
    'create_user.username_help'        => 'Soki otiki goullo, ebongiseli ekozala na kombo ya mosangani ya sika moko na ye moko.',
    'create_user.email_label'          => 'Adresi ya email:',
    'create_user.role_label'           => 'Mosala ya moto:',
    'create_user.submit_btn'           => 'Sala moto mpe tinda libenga',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Esika ya mikakatano na makanisi',
    'feedback_dash.subheading'           => 'Kokambi mituna ya bato, kobongisa misala mpe koloba.',
    'feedback_dash.manage_emails'        => 'Kokambi bafɔlɔ ya email',
    'feedback_dash.manage_schema'        => 'Kokambi fɔlɔ ya mituna',
    'feedback_dash.th_ticket_date'       => 'ID ya litina / Mikolo',
    'feedback_dash.th_submitter'         => 'Motindi',
    'feedback_dash.th_subject_info'      => 'Litina / Sango ya liboso',
    'feedback_dash.th_status'            => 'Bofulami',
    'feedback_dash.no_tickets'           => 'Litina moko te ezwama.',
    'feedback_dash.anonymous'            => 'Moto ya koyeba te',
    'feedback_dash.default_subject'      => 'Mituna ya mokolo na mokolo',
    'feedback_dash.open_ticket_btn'      => 'Fungola litina na maloba',
    'feedback_dash.delete_confirm'       => 'Longola litina oyo na biyano na yango nionso?',
    'feedback_dash.msg_deleted'          => 'Litina #:id elongwama malamu.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Bafɔlɔ ya email ya mituna',
    'feedback_emails.subheading'         => 'Bongisa ba email ya koya na mbala moko. Sala na bilembo ya sango.',
    'feedback_emails.back_to_dashboard' => 'Kende na esika ya mituna',
    'feedback_emails.email_subject'      => 'Motó ya likambo ya email:',
    'feedback_emails.email_body'         => 'Mwa ndenge ya email:',
    'feedback_emails.save_template_btn' => 'Bomba fɔlɔ',
    'feedback_emails.placeholders_heading' => 'Bilembo bya kokoka',
    'feedback_emails.placeholders_desc' => 'Okoki kosalela bilembo oyo esika nionso:',
    'feedback_emails.fixed_tags'         => 'Bilembo ya ntina:',
    'feedback_emails.custom_tags'        => 'Bilembo ya sika:',
    'feedback_emails.custom_tags_desc'   => 'Ebotami moko na ye moko uta na fɔlɔ:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Kokambi fɔlɔ ya makanisi',
    'feedback_schema.subheading'             => 'Bongisa ba sango, lolenge ya sango, mipaka, mpe ndenge ya komonisa.',
    'feedback_schema.settings_summary'       => 'Bongisa motó ya fɔlɔ na liloba ya bosikisoki',
    'feedback_schema.form_title_label'       => 'Motó ya fɔlɔ:',
    'feedback_schema.form_intro_label'       => 'Liloba ya liboso / Ndenge ya kolimbola:',
    'feedback_schema.save_settings_btn'      => 'Bomba mbongwana ya fɔlɔ',
    'feedback_schema.edit_field_title'       => 'Bongisa esika ya litina:',
    'feedback_schema.add_field_title'        => '+ Bakisa esika ya sika',
    'feedback_schema.field_name_label'       => 'Kombo ya esika:',
    'feedback_schema.data_type_label'        => 'Lolenge ya sango:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Mwa liloba)',
    'feedback_schema.type_text'              => 'TEXT (Liloba monene / Sango)',
    'feedback_schema.type_int'               => 'INT (Motuya ya motango)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Ee/Te)',
    'feedback_schema.type_date'              => 'DATE (Mikolo ya calandrie)',
    'feedback_schema.subtype_label'          => 'Lolenge ya moke / Ndenge ya komonisa:',
    'feedback_schema.subtype_standard'       => '-- Standard --',
    'feedback_schema.subtype_standard_lower' => 'standard',
    'feedback_schema.options_label'          => 'Makanisi (esangani na koma to moko na molɔngɔ):',
    'feedback_schema.options_help'           => 'Pesa makanisi esangani na koma.',
    'feedback_schema.allow_multiple'         => ' ndima kopona ebele (Kopona ebele)',
    'feedback_schema.boolean_format'         => 'Ndenge ya komonisa Ee/Te:',
    'feedback_schema.max_length_label'       => 'Bonene koleka / Mpaka ya bakomi:',
    'feedback_schema.is_required_label'      => 'Sala ete esika oyo ezala ya ntina mpenza',
    'feedback_schema.save_field_btn'         => 'Bomba mbongwana ya esika',
    'feedback_schema.create_field_btn'       => 'Sala esika ya litina',
    'feedback_schema.sub_email'              => 'Email',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Kitisama na nse',
    'feedback_schema.sub_radio'              => 'Bokatisi ya radio',
    'feedback_schema.sub_checkbox'           => 'Bozo ya kopona',
    'feedback_schema.sub_textarea'           => 'Esika monene ya bakomi',
    'feedback_schema.sub_number'             => 'Kotisa motango',
    'feedback_schema.existing_fields_heading' => 'Baesika ya litina ezali',
    'feedback_schema.th_move'                => 'Kende',
    'feedback_schema.th_field_name'          => 'Kombo ya esika',
    'feedback_schema.th_data_type'           => 'Lolenge ya sango',
    'feedback_schema.th_subtype'             => 'Lolenge ya moke',
    'feedback_schema.th_required'            => 'Ezali ya ntina?',
    'feedback_schema.th_max_length'          => 'Bonene koleka',
    'feedback_schema.th_created_by'          => 'Esalemi na',
    'feedback_schema.no_fields'              => 'Esika moko te eyebani.',
    'feedback_schema.system_user'            => 'Ebongiseli',
    'feedback_schema.edit_btn'               => 'Bongisa',
    'feedback_schema.delete_confirm'         => 'Longola esika oyo na biloko na yango nionso?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Kokambi bamesa na ba sgeema',
    'manage_tables.subheading'           => 'Sala, tala, bongisa to longola bamesa na molɔngɔ na bango na kimya.',
    'manage_tables.switcher_label'       => 'Pɔnɔ sgeema ya mesa ya sikoyo:',
    'manage_tables.edit_metadata_btn'    => 'Bongisa sango ya mesa',
    'manage_tables.delete_table_confirm' => 'EBENGO: Kolongola mesa oyo ekolongola molɔngɔ nionso na biloko ebombami. Ozali na boyokani?',
    'manage_tables.delete_table_btn'     => 'Longola mesa',
    'manage_tables.edit_table_summary'   => 'Bongisa ndimbola ya mesa:',
    'manage_tables.create_table_summary' => '+ Sala mesa ya sika',
    'manage_tables.table_name_label'     => 'Kombo ya malamu ya mesa:',
    'manage_tables.table_desc_label'     => 'Limbola / Ntina:',
    'manage_tables.save_table_btn'       => 'Bomba mbongwana ya mesa',
    'manage_tables.create_table_btn'     => 'Sala sgeema ya mesa',
    'manage_tables.edit_col_summary'     => 'Bongisa molɔngɔ:',
    'manage_tables.add_col_summary_prefix' => '+ Bakisa molɔngɔ ya sika na:',
    'manage_tables.col_name_label'       => 'Kombo ya molɔngɔ:',
    'manage_tables.type_text_long'       => 'TEXT (Liloba monene)',
    'manage_tables.date_behavior_label' => 'Ndenge ya koluka mikolo:',
    'manage_tables.date_bhv_manual'      => 'Mikolo ya stɔru (kaka kotisa na loboko)',
    'manage_tables.date_bhv_admin'       => 'Mikolo ya mokambi kaka',
    'manage_tables.date_bhv_all'         => 'Mikolo nionso elongo na ya mokambi',
    'manage_tables.req_toggle_label'     => 'Sala molɔngɔ oyo ezala ya ntina (kotisa sango na makasi)',
    'manage_tables.exclude_search_label'=> 'Bimisa molɔngɔ oyo na boluki ya bato nionso (index.php)',
    'manage_tables.create_col_btn'       => 'Sala molɔngɔ',
    'manage_tables.existing_cols_heading_prefix' => 'Molɔngɔ ezali mpo na:',
    'manage_tables.th_public_search'     => 'Boluki ya bato nionso?',
    'manage_tables.th_display_format'    => 'Ndenge ya komonisa',
    'manage_tables.th_date_created'      => 'Mikolo ya bokeli',
    'manage_tables.no_columns_found'     => 'Molɔngɔ moko te ezwama mpo na mesa oyo.',
    'manage_tables.status_hidden'        => 'Ebombami',
    'manage_tables.delete_col_confirm'   => 'EBENGO: Kolongola molɔngɔ oyo ekolongola mpe biloko nionso ya selile na mbonimboni nionso. Ozali na boyokani?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Kokambi bafɔlɔ ya email ya bokenisi ya bato',
    'user_emails.subheading'             => 'Bongisa ndenge ya ba email etindamaka tango ya kopɛpisa bato to kobongisa ba paswedi.',
    'user_emails.select_template_label'  => 'Pɔnɔ fɔlɔ ya kobongisa:',
    'user_emails.opt_invitation'         => 'Fɔlɔ ya libenga ya konti',
    'user_emails.opt_reset'              => 'Fɔlɔ ya kobongisa paswedi / nzela ya kokɔtá',
    'currently_editing'                  => 'Kobongisa sikoyo:',
    'user_emails.desc_invitation'        => 'Etindamaka moko na ye moko tango mokambi asali to apɛpisi moto ya sika.',
    'user_emails.desc_reset'             => 'Etindamaka tango ya kobongisa paswedi to kotinda lisusu nzela.',
    'user_emails.email_body_label'       => 'Mwa nzete ya email:',
    'user_emails.back_to_creation'       => 'Kende na bokeli ya moto',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Bafɔlɔ ya email ya basungi',
    'volunteer_emails.subheading'        => 'Bongisa ba email ya basungi. Sala na bilembo ya sango.',
    'volunteer_emails.back_to_dashboard' => 'Kende na esika ya basungi',
    'volunteer_emails.custom_tags_desc'  => 'Ebotami moko na ye moko uta na fɔlɔ:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Kokambi fɔlɔ ya basungi',
    'volunteer_schema.subheading'        => 'Bongisa ba sango, lolenge ya sango, mipaka, mpe ndenge ya komonisa.',
    'volunteer_schema.back_to_dashboard'=> 'Kende na esika ya basungi',
    'volunteer_schema.settings_summary'  => 'Bongisa motó ya fɔlɔ na liloba ya bosikisoki',
    'volunteer_schema.edit_field_title'  => 'Bongisa esika:',
    'volunteer_schema.add_field_title'   => '+ Bakisa esika ya sika ya mosungi',
    'volunteer_schema.create_field_btn'  => 'Sala esika',
    'volunteer_schema.existing_fields_heading' => 'Baesika ya fɔlɔ ya mosungi ezali',
    'volunteer_schema.no_fields'         => 'Esika moko te ya mosungi eyebani.',
    'volunteer_schema.delete_confirm'    => 'Longola esika oyo na biloko na yango nionso?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Kotalela makanisi oyo mazali kozela',
    'moderate.subheading'                => 'Tala mbongwana oyo bato basengi na mbonimboni ya solo. Ndima, sakola to boya.',
    'moderate.shortcut_label'            => 'Makanisi ya klabi:',
    'moderate.shortcut_desc'             => 'Finika Ctrl + Enter mpo na kondima noki, to Esc mpo na kolangula bokonzi!',
    'moderate.th_id_date'                => 'ID / Mikolo',
    'moderate.th_table_record'           => 'Mesa, mbonimboni, na molɔngɔ',
    'moderate.th_comparison'             => 'Kokanisa (Ya solo vs Esengi) na Mbonimboni',
    'moderate.th_actions'                => 'Misala ya mokɛngɛli',
    'moderate.no_suggestions'            => 'Makanisi ma kozela ezwama te.',
    'moderate.by_label'                  => 'Na:',
    'moderate.guest_user'                => 'Mɔngɔ́ / Migeni',
    'moderate.record_id_label'           => 'ID ya Mbonimboni:',
    'moderate.column_label'              => 'Molɔngɔ:',
    'moderate.required_badge'            => 'Ya ntina',
    'moderate.live_value_label'          => 'Motuya ya sikoyo:',
    'moderate.empty_placeholder'         => '[Goullo]',
    'moderate.proposed_value_label'      => 'Mbongwana esengi:',
    'moderate.evidence_label'            => 'Mbonimboni / Ntina:',
    'moderate.no_evidence'               => 'Mbonimboni to ntina epesameli te.',
    'moderate.override_label'            => 'Sakola motuya:',
    'moderate.select_placeholder'        => '-- Pɔnɔ --',
    'moderate.historical_dates_title'    => 'Mikolo ya kala eyambami',
    'moderate.approve_confirm'           => 'Ndima mpe sala na motuya oyo?',
    'moderate.decline_confirm'           => 'Boya mpe longola makanisi oyo?',
    'moderate.approve_btn'               => 'Ndima',
    'moderate.decline_btn'               => 'Boya',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Kokambi sango na banzembo ya site',
    'notices.subheading'                 => 'Sala bokenisi, bafɔlɔ ya boyambi to sango mpo na ba mosala moko.',
    'notices.error_blank'                => 'Motó na sango ekoki kozala goullo te.',
    'notices.msg_created'                => 'Sango esalemi malamu!',
    'notices.msg_deleted'                => 'Sango elongwama.',
    'notices.create_heading'             => 'Sala sango ya sika',
    'notices.title_label'                => 'Motó ya sango / Mokuse:',
    'notices.content_label'              => 'Sango (HTML/Bakomi endimami):',
    'notices.target_roles_label'         => 'Bato ya ntina (Pɔnɔ mosala to nionso):',
    'notices.role_everyone'              => 'Bato nionso',
    'notices.role_public'                => 'Bato nionso (Migeni)',
    'notices.role_users'                 => 'Bato',
    'notices.role_moderators'            => 'Bakɛngɛli',
    'notices.role_admins'                => 'Bakambi',
    'notices.dismissible_label'          => "Ekoki kokangama (Ezali na buzo ya 'X')",
    'notices.display_order_label'        => 'Molɔngɔ ya komonisa:',
    'notices.publish_btn'                => 'Bimisa sango',
    'notices.existing_heading'           => 'Sango ya sikoyo',
    'notices.th_order'                   => 'Molɔngɔ',
    'notices.th_title'                   => 'Motó',
    'notices.th_target_roles'            => 'Ba mosala ya ntina',
    'notices.th_dismissible'             => 'Ekoki kokangama',
    'notices.no_notices'                 => 'Sango moko te esalemi.',
    'notices.yes'                        => 'Ee',
    'notices.no_sticky'                  => 'Te (Kasi / Sticky)',
    'notices.delete_confirm'             => 'Longola sango oyo?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Bokambi ya site, ba modu na makoki',
    'settings.subheading'                => 'Kokambi biloko ya ntina, ba draivɛ ya email, bokenisi/CAPTCHA, ba modu, ndenge ya bokebi, sango, na matriki ya mosala.',
    'settings.tab_core'                  => 'Ntina na Email',
    'settings.tab_modules'               => 'Ba Modu',
    'settings.tab_maintenance'           => 'Bokebi',
    'settings.tab_notices'               => 'Sango ya Site',
    'settings.tab_permissions'           => 'Ba mosala na Makoki',
    'settings.tab_audit'                 => 'Logi ya bokenisi',
    'settings.db_updates_heading'        => 'Mbongwana ya stɔru',
    'settings.schema_current'            => 'Sgeema ya sikoyo:',
    'settings.schema_latest'             => 'Ya sika koleka:',
    'settings.download_backup_btn'       => 'Kita kobomba ya stɔru',
    'settings.download_backup_desc'      => 'Bomba faele ya .sql na kompiuta na yo. Bomba na esika ya kimya liboso ya kobongisa.',
    'settings.schema_update_notice'      => 'Mbongwana ya stɔru ezali. Kita kobomba na likolo liboso ya kokoba.',
    'settings.migration_confirm'         => 'Ozwi kobomba ya stɔru? Oyo ekozongisa mbongwana ya sgeema.',
    'settings.update_db_btn'             => 'Bongisa stɔru',
    'settings.schema_uptodate'           => 'Stɔru ezali ya sika.',
    'settings.core_sys_heading'          => 'Bokambi ya ebongiseli ya ntina',
    'settings.sys_name_label'            => 'Kombo ya ebongiseli / apiliki:',
    'settings.default_lang_label'        => 'Lokótá ya site:',
    'settings.default_lang_desc'         => 'Esalelamaka mpo na migeni na bato oyo bapɔni lokótá te. Tika ba faele na lang/ (na ndakisa ln.php) mpo na makoki ma sika.',
    'settings.captcha_heading'           => 'Bokenisi na CAPTCHA',
    'settings.captcha_provider_label'    => 'Motiki ya CAPTCHA:',
    'settings.captcha_none'              => 'Ezali te (CAPTCHA te)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Bokambi ya Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Bokambi ya Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Bokambi ya hCaptcha',
    'settings.site_key_label'            => 'Uyi ya site (Pobliki):',
    'settings.secret_key_label'          => 'Uyi ya kimya (Prɔvete):',
    'settings.mail_heading'              => 'Bokambi ya kutinda email',
    'settings.mail_domain_label'         => 'Domeni ya email ya ebongiseli:',
    'settings.mail_from_label'           => "Adresi ya email ya 'Uta' ya sika:",
    'settings.mail_from_desc'            => 'Adresi moko ya ntina esalelamaka mpo na kutinda email.',
    'settings.mail_driver_label'         => 'Motiki ya email:',
    'settings.driver_native'             => 'Email ya ndako (Postfix ya ndako)',
    'settings.driver_smtp'               => 'SMTP ya bokenisi (PHPMailer)',
    'settings.smtp_heading'              => 'Bokambi ya sƐrve ya SMTP',
    'settings.smtp_host_label'           => 'Sɛrve ya SMTP:',
    'settings.smtp_port_label'           => 'Polo (Port):',
    'settings.smtp_encryption_label'     => 'Bokangi ya sango:',
    'settings.enc_tls'                   => 'TLS (Polo 587)',
    'settings.enc_ssl'                   => 'SSL (Polo 465)',
    'settings.smtp_user_label'           => 'Kombo ya mosangani ya SMTP:',
    'settings.smtp_pass_label'           => 'Paswedi ya SMTP (tika goullo mpo na kobomba ya sikoyo):',
    'settings.save_core_mail_btn'        => 'Bomba ntina na email',
    'settings.test_mail_heading'         => 'Meka bokambi ya email',
    'settings.test_email_label'          => 'Adresi ya motindi ya email:',
    'settings.send_test_btn'             => 'Tinda email ya komeka',
    'settings.modules_heading'           => 'Ba modu na makoki ma mosala',
    'settings.modules_subheading'        => 'Kansa to longola misala mpo na kosala malamu.',
    'settings.mod_users'                 => 'Kokambi bato na bɔkɔ́ ya ebele',
    'settings.mod_users_desc'            => 'Ndima komikoma, kokambi bato na bɔkɔ́ ya ebele.',
    'settings.mod_leaderboard'           => 'Molɔngɔ ya ba mabonzi na masano',
    'settings.mod_leaderboard_desc'      => 'Zwa boyebi ya mosala na mabonzi ya sɔ́tɔ.',
    'settings.mod_leaderboard_note'      => '(Ezali na ntina ya kokambi bato na bɔkɔ́ ya ebele)',
    'settings.mod_moderation'            => 'Nzela ya bokɛngɛli',
    'settings.mod_moderation_desc'       => 'Ndima kotalela mbongwana na molɔngɔ ya bokɛngɛli.',
    'settings.mod_volunteers'            => 'Esika ya basungi na mituna',
    'settings.mod_volunteers_desc'       => 'Ndima fɔlɔ ya basungi.',
    'settings.mod_feedback'              => 'Kutinda makanisi',
    'settings.mod_feedback_desc'         => 'Ndima fɔlɔ ya makanisi ya bato nionso.',
    'settings.save_modules_btn'          => 'Bomba ba modu',
    'settings.maintenance_heading'       => 'Ndenge ya bokebi ya ebongiseli',
    'settings.maintenance_toggle'        => 'Ndima bokebi (Tika site na nse ya molɔngɔ)',
    'settings.maintenance_reason_label'  => 'Ntina / Sango mpo na bato:',
    'settings.maintenance_eta_label'     => 'Ntango ya kozonga (ETA):',
    'settings.save_maintenance_btn'      => 'Bomba bokebi',
    'settings.notices_heading'           => 'Sango na banzembo ya site',
    'settings.add_notice_btn'            => '+ Bakisa sango ya sika',
    'settings.no_notices'                => 'Sango moko te ebongisami.',
    'settings.status_active'             => 'Ezali kosala',
    'settings.status_inactive'           => 'Ezali kosala te',
    'settings.notice_content_label'      => 'Sango:',
    'settings.save_notice_btn'           => 'Bomba sango',
    'settings.permissions_heading'       => 'Matriki ya ba mosala na makoki',
    'settings.permissions_subheading'    => 'Makoki mazali na biteni ya misala. Landa biteni mpo na kobongisa.',
    'settings.th_role'                   => 'Mosala',
    'settings.th_capabilities'           => 'Makoki epesami na eteni oyo',
    'settings.save_permissions_btn'      => 'Bomba matriki ya makoki',
    'settings.audit_heading'             => 'Boluki ya logi ya bokenisi',
    'settings.audit_subheading'          => 'Tala misala ya bokenisi, kotisa sango, na bokɛngɛli.',
    'settings.purge_all_confirm'         => '⚠️ EBENGO: Oyo ekolongola LOGI NIONSO YA BOKENISI. Ozali na boyokani?',
    'settings.clear_all_audit_btn'       => 'Longola logi nionso ya bokenisi',
    'settings.purge_records_confirm'     => 'Ozali na boyokani ya kolongola logi nionso ya mbonimboni?',
    'settings.clear_records_audit_btn'   => 'Longola logi ya mbonimboni kaka',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Ntango ya sikoyo',
    'settings.th_actor'                  => 'Moto ya mosala',
    'settings.th_action'                 => 'Mosala',
    'settings.th_record_id'              => 'ID ya Mbonimboni',
    'settings.th_details'                => 'Mwa sango',
    'settings.th_ip'                     => 'Adresi ya IP',
    'settings.no_audit_logs'             => 'Logi ya bokenisi ezwama te.',
    'settings.system_guest'              => 'Ebongiseli / Migeni',
    'settings.audit_limit_note'          => 'Komonisa logi 250 ya nsuka.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Kokambi konti ya bato na bokɛngɛli ya mabonzi',
    'admin_users.subheading'             => 'Tala bofulami ya moto, pesa mosala, bongisa email, tinda kobongisa paswedi to libenga, bongisa 2FA, to kanga konti.',
    'admin_users.manage_templates_btn'   => 'Kokambi bafɔlɔ ya email',
    'admin_users.invite_user_btn'        => 'Pɛpisa moto ya sika',
    'admin_users.th_username'            => 'Kombo ya mosangani',
    'admin_users.th_email_override'      => 'Email na Sakola',
    'admin_users.th_role_assignment'     => 'Kopesa mosala',
    'admin_users.th_score'               => 'Mabonzi',
    'admin_users.th_status'              => 'Bofulami',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Misala na Bokɛngɛli',
    'admin_users.no_users'               => 'Moto moko te ezwama.',
    'admin_users.save_email_title'       => 'Bomba adresi ya email ya sika',
    'admin_users.verified_label'         => 'Esikisami:',
    'admin_users.yes'                    => 'Ee',
    'admin_users.no'                     => 'Te',
    'admin_users.protected_admin'        => 'Mokambi ya liboso ebatelami',
    'admin_users.update_btn'             => 'Bongisa',
    'admin_users.status_active'          => 'Ezali kosala',
    'admin_users.status_suspended'       => ' Ekangami mwa moke',
    'admin_users.enabled'                => 'Epesami nzela',
    'admin_users.disabled'               => 'Epekisami',
    'admin_users.set_score_btn'          => 'Tia mabonzi',
    'admin_users.resend_invite_confirm'  => 'Tinda lisusu email ya libenga na moto oyo?',
    'admin_users.resend_invite_btn'      => 'Tinda lisusu libenga',
    'admin_users.reset_pwd_confirm'      => 'Tinda nzela ya kobongisa paswedi na moto oyo?',
    'admin_users.reset_password_btn'     => 'Bongisa paswedi',
    'admin_users.suspend_confirm'        => 'Kanga konti ya moto oyo mpo ya mabe?',
    'admin_users.suspend_btn'            => 'Kanga konti',
    'admin_users.reactivate_btn'         => 'Zongisa mosala',
    'admin_users.reset_2fa_confirm'      => 'Bongisa na kolongola 2FA ya moto oyo?',
    'admin_users.reset_2fa_btn'          => 'Bongisa 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Kende na esika ya mituna',
    'view_ticket.ticket_heading_prefix' => 'Litina',
    'view_ticket.support_request'      => 'Mituna ya lisungi',
    'view_ticket.submitted_by'         => 'Epesami na:',
    'view_ticket.on_date'              => 'na mikolo',
    'view_ticket.submitted_fields'     => 'Baesika ya fɔlɔ epesami:',
    'view_ticket.ticket_status_label'  => 'Bofulami ya litina:',
    'view_ticket.status_pending'       => 'Ezali kozela',
    'view_ticket.status_progress'      => 'Ezali kosalema',
    'view_ticket.status_completed'     => 'Esili',
    'view_ticket.status_rejected'      => 'Eboyami',
    'view_ticket.dialogue_heading'     => 'Nzela ya maloba',
    'view_ticket.no_replies'           => 'Biyano ebombami te.',
    'view_ticket.admin_label'          => 'Mokambi',
    'view_ticket.staff'                => 'Basali',
    'view_ticket.post_reply_heading'   => 'Tinda biyano na kopesa sango na motindi',
    'view_ticket.reply_placeholder'    => 'Koma biyano na yo awa...',
    'view_ticket.send_reply_btn'       => 'Tinda biyano na email na motindi',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Mituna ya basungi na nzela',
    'volunteer_dashboard.subheading'         => 'Tala mituna, bongisa masolo, koma maloba ya boyebi, na ndima bato na ebongiseli.',
    'volunteer_dashboard.manage_emails_btn' => 'Kokambi bafɔlɔ ya email',
    'volunteer_dashboard.manage_schema_btn' => 'Kokambi sgeema ya fɔlɔ',
    'volunteer_dashboard.th_status'          => 'Bofulami',
    'volunteer_dashboard.th_name'            => 'Kombo',
    'volunteer_dashboard.th_interview_notes' => 'Maniɔla / Maloba',
    'volunteer_dashboard.no_submissions'     => 'Mituna ya basungi ezwama te.',
    'volunteer_dashboard.volunteer_prefix'   => 'Mosungi',
    'volunteer_dashboard.chat_label'         => 'Esika ya maloba:',
    'volunteer_dashboard.notes_label'        => 'Maloba:',
    'volunteer_dashboard.no_notes'           => 'Maloba ezali te',
    'volunteer_dashboard.chat_notes_btn'     => 'Maloba na Maloba',
    'volunteer_dashboard.accept_title'       => 'Ndima na nzela ya libenga ya konti',
    'volunteer_dashboard.accept_invite_btn'  => 'Ndima na kutinda libenga',
    'volunteer_dashboard.delete_confirm'     => 'Longola mbonimboni ya mosungi oyo?',
    'volunteer_dashboard.modal_heading'      => 'Kokambi maniɔla na maloba ya moto',
    'volunteer_dashboard.modal_status_label' => 'Bofulami ya lituna:',
    'volunteer_dashboard.status_pending'     => 'Kozela kotalela',
    'volunteer_dashboard.status_chat'        => 'Masolo ebongisami',
    'volunteer_dashboard.status_accepted'    => 'Endimami',
    'volunteer_dashboard.status_rejected'    => 'Eboyami',
    'volunteer_dashboard.modal_date_label'   => 'Mikolo na ntango ya masolo:',
    'volunteer_dashboard.modal_notes_label'  => 'Maloba ya maniɔla:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Koma maloba ya masolo awa...',
    'volunteer_dashboard.save_changes_btn'   => 'Bomba mbongwana',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Epekisami: Komona ya bato nionso epesami nzela te.',
    'api_search.error_unauthorized_table' => 'Kokɔtá na mesa epesami nzela te.',
    'api_search.no_records'              => 'Ezwama eloko te na mesa oyo.',
    'api_search.history_btn'             => 'Makambo ya kala',
    'api_search.suggest_edit_btn'        => 'Tinda mbongwana',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Zonga na ndako ya bato nionso',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ---------------------------------------------------               -------
    'feedback.hp_label'              => 'Tika goullo',
    'feedback.first_name_label'      => 'Kombo ya liboso:',
    'feedback.surname_label'         => 'Kombo ya tata:',
    'feedback.email_label'           => 'Adresi ya email:',
    'feedback.subject_label'         => 'Litina / Motó ya mituna:',
    'feedback.required_title'        => 'Esika ya ntina',
    'feedback.select_placeholder'    => '-- Pɔnɔ --',
    'feedback.multi_select_hint'     => 'Finika Ctrl to Cmd mpo na kopona ebele.',
    'feedback.submit_btn'            => 'Tinda litina',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Mpeza ya bokenisi: Motiki ya mabe.',
    'security_engine.err_access_denied'    => 'Mpeza ya bokenisi: Kokɔtá eboyami.',
    'security_engine.err_rate_limit'       => 'Mituna ebele uta na adresi ya IP oyo. Meka nsima.',
    'security_engine.err_excessive_links'  => 'Ba link ebele mpenza, kutinda eboyami.',
    'security_engine.err_complete_captcha' => 'Silisa bokenisi ya CAPTCHA.',
    'security_engine.err_captcha_failed'   => 'Bokenisi ya CAPTCHA eboyami, meka lisusu.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Bosali esili',
    'install.complete_heading'           => 'Bosali esili',
    'install.complete_desc'              => 'Site oyo ebongisami esili. Ebongisi ekangami mpo na kopeka boyei ya mbala mosusu.',
    'install.login_link'                 => 'Kɔtá',
    'install.home_link'                  => 'Kende na site',
    'install.delete_folder_hint'         => 'Mpo na bokenisi ya malamu, okoki kolongola to kobongisa kombo ya fɔlɔde <code>install</code>.',
    'install.msg_db_ready'               => 'Stɔru ezuami malamu. Sala konti ya mokambi mpo na kosilisa.',
    'install.err_config_load'            => 'Kokoka kosalela ebongiseli ya kala te:',
    'install.err_write_permission'       => 'PHP ekoki kosala faele te na fɔlɔde ya mosala oyo.',
    'install.detail_prefix'              => 'Mwa sango:',
    'install.err_db_required'            => 'Kombo ya stɔru na kombo ya mosangani ezali ya ntina.',
    'install.err_db_not_empty'           => 'Stɔru oyo ezali goullo te. Salela stɔru ya sika ya goullo to longola mesa nionso.',
    'install.msg_schema_imported'        => 'Stɔru esangani na sgeema etindami. Sala konti ya mokambi na yo.',
    'install.err_complete_db_first'      => 'Silisa eteni ya stɔru liboso.',
    'install.err_admin_required'         => 'Esika nionso ya mokambi ezali ya ntina.',
    'install.err_invalid_email'          => 'Adresi ya email ezali malamu te.',
    'install.err_password_length'        => 'Paswedi esengeli ezala na bakomi 8 to koleka.',
    'install.err_passwords_match'        => 'Paswedi mibale mazali moko te.',
    'install.err_admin_save_failed'      => 'Kobomba mokambi ekoli te, tala sgeema ya mesa ya bato.',
    'install.msg_installation_complete' => 'Bosali esili.',
    'install.page_title'                 => 'Bosali — Stɔru ya Mbonimboni ya Parase',
    'install.heading'                    => 'Bosali',
    'install.subheading'                 => 'Bokambi ya liboso <strong>mpo na fɔlɔde ya apiliki oyo kaka</strong>. Salela stɔru ya MySQL ya goullo.',
    'install.done_heading'               => 'Esili',
    'install.done_message'               => 'Bosali esili. Motiki ekangami sikoyo.',
    'install.admin_heading'              => 'Konti ya mokambi ya site',
    'install.admin_subheading'           => 'Oyo ezali ya kokɔtá mpo na <strong>site oyo</strong> (ezali stɔru te).',
    'install.admin_username_label'       => 'Kombo ya mokambi',
    'install.admin_email_label'          => 'Email ya mokambi',
    'install.admin_password_label'       => 'Paswedi ya mokambi (bakomi 8 min.)',
    'install.admin_confirm_password_label' => 'Zongisa paswedi ya mokambi',
    'install.finish_btn'                 => 'Silisa bosali',
    'install.db_heading'                 => 'Bokangami ya stɔru',
    'install.db_hint'                    => 'Salela sango ya MySQL uta na <strong>paneli ya bokambi</strong> na yo. Ezali konti ya mokambi ya site te.',
    'install.db_host_label'              => 'Sɛrve ya stɔru',
    'install.db_name_label'              => 'Kombo ya stɔru',
    'install.db_user_label'              => 'Kombo ya mosangani ya stɔru',
    'install.db_pass_label'              => 'Paswedi ya stɔru',
    'install.db_submit_btn'              => 'Sala bamesa na kokoba',
    'install.req_heading'                => '1. Makambo mazali na ntina',
    'install.req_php'                    => 'PHP 8.0+ (ezwama %s)',
    'install.req_pdo'                    => 'Stili ya PDO MySQL',
    'install.req_logs'                   => 'Fɔlɔde ya logi ekoki kokomama',
    'install.req_probe'                  => 'Ekoki kosala faele na fɔlɔde ya mosala oyo',
    'install.continue_btn'               => 'Kokoba',
    'install.req_fail_msg'               => 'Bongisa mabunga na zonga kɔtisa paje oyo.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Komonisa molɔngɔ ya ba mabonzi',
    'leaderboard.heading'         => 'Molɔngɔ ya ba mabonzi ya basungi',
    'leaderboard.subheading'      => 'Kopesa nkembo na misala ya basungi oyo bazali kosangisa, kokoma to kokambi mbonimboni ya stɔru.',
    'leaderboard.th_rank'         => 'Mokɛ',
    'leaderboard.th_contributor'  => 'Mosungi',
    'leaderboard.th_role'         => 'Mosala',
    'leaderboard.th_score'        => 'Mabonzi',
    'leaderboard.no_users'        => 'Mosungi moko te ezwama na molɔngɔ.',
    'leaderboard.medal_gold'      => 'Medali ya wolo',
    'leaderboard.medal_silver'    => 'Medali ya palata',
    'leaderboard.medal_bronze'    => 'Medali ya bronze',
    'leaderboard.medal_ribbon'    => 'Liba ya Mokɛ 4',
    'leaderboard.medal_rosette'   => 'Rozete ya Mokɛ 5',
    'leaderboard.medal_trophy'    => 'Trofɛ ya Mokɛ 6',
    'leaderboard.medal_star'      => 'Sɔ́tɔ ya Mokɛ 7',
    'leaderboard.medal_military'  => 'Medali ya soda ya Mokɛ 8',
    'leaderboard.medal_glowing'   => 'Sɔ́tɔ ya kopela ya Mokɛ 9',
    'leaderboard.medal_crown'     => 'Kɔrɔna ya Mokɛ 10',
    'leaderboard.you_badge'       => '(Yo)',
    'leaderboard.default_role'    => 'Moto',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Mbonimboni ya parase esangisami uta na biteni ya kala ya bato nionso.',
    'footer.software_notice'  => 'Ebongiseli ya software ya bopanzi na nse ya ndingisa ya MIT.',
    'footer.rights_reserved'  => 'Makoki nionso mabatelami.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Stɔru ya Mbonimboni ya Parase',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Kanga sango',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Mbonimboni eponami te.',
    'record_history.exit_not_found'        => 'Mbonimboni ezwama te.',
    'record_history.heading_prefix'        => 'Makambo ya kala na Logi: Mbonimboni',
    'record_history.return_btn'            => 'Zonga',
    'record_history.directory_table_label' => 'Mesa ya stɔru:',
    'record_history.subheading_lifecycle' => 'Komonisa bomoi ya mbongwana, makanisi na mbonimboni ya mbonimboni oyo.',
    'record_history.snapshot_heading'      => 'Fotó ya motuya ya sikoyo',
    'record_history.empty_value'           => '[Goullo]',
    'record_history.timeline_heading'      => 'Molɔngɔ ya mikolo na misala',
    'record_history.no_history'            => 'Likambo ya kala te ekomami mpo na mbonimboni oyo.',
    'record_history.purge_confirm'         => 'Longola logi oyo?',
    'record_history.purge_btn'             => 'Longola logi',
    'record_history.actor_label'           => 'Moto ya mosala:',
    'record_history.system_guest'          => 'Ebongiseli / Migeni',
    'record_history.target_column'         => 'Molɔngɔ ya ntina:',
    'record_history.proposed_value'        => 'Motuya esengi:',
    'record_history.reasoning_evidence'    => 'Ntina / Mbonimboni:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Stɔru ebongisami malamu! Ba mɔti %d esalemi.',
    'update_database.msg_uptodate'     => 'Stɔru ezali ya sika.',
    'update_database.err_failed'       => 'Mba epesi te:',
    'update_database.page_title'       => 'Ntina ya mbongwana ya ebongiseli — Stɔru ya Mbonimboni ya Parase',
    'update_database.heading'          => '⚠️ Ntina ya mbongwana ya ebongiseli',
    'update_database.subheading'       => 'Sgeema ya stɔru ya apiliki ezali ya kala, esengi mbongwana liboso ya kokoba.',
    'update_database.current_version'  => 'Sgeema ya sikoyo:',
    'update_database.latest_version'   => 'Ya sika koleka:',
    'update_database.proceed_login'    => 'Kende na paje ya kokɔtá',
    'update_database.confirm_prompt'   => 'Ozwi kobomba ya stɔru? Finika OK mpo na kosala mbongwana.',
    'update_database.update_btn'       => 'Bongisa stɔru sikoyo',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Sango ezali malamu te to kokɔtá epekisami.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'Esika ya ntina \'%s\' ekoki kozala goullo te.',
    'save_data_entry.audit_created_prefix' => 'Mbonimboni esalemi na mesa ya ID %d.',
    'save_data_entry.msg_success'          => 'Mbonimboni ebakisami malamu!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Spam ezwama, kutinda eboyami.',
    'save_public_suggestion.err_field_required' => 'Esika oyo ezali ya ntina, ekoki kozala goullo te.',
    'save_public_suggestion.msg_success'        => 'Mbongwana na yo etindamami malamu mpo na kotalela. Matondi!',
    'save_public_suggestion.err_failed_submit'  => 'Kutinda mbongwana ekoli te, meka lisusu.',
    'save_public_suggestion.err_invalid_column' => 'Molɔngɔ eponami ezali malamu te.',
    'save_public_suggestion.err_invalid_params' => 'Parametru ya kutinda mbonimboni ezali malamu te.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'YYYY-MM-DD (to mwa mobu)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/YYYY (to mwa mobu)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/YYYY (to mwa mobu)',
    'data_entry.no_tables_heading'    => '⚠️ Mesa ya stɔru ezwama te',
    'data_entry.no_tables_desc'       => 'Mesa moko te ebongisami mpo na kotisa sango.',
    'data_entry.admin_tables_prompt'  => 'Lokola mokambi, kende na <strong>Kokambi mesa</strong> mpo na kosala mesa na molɔngɔ liboso ya kotisa mbonimboni.',
    'data_entry.go_manage_tables'     => 'Kende na Kokambi mesa',
    'data_entry.contact_admin_tables' => 'Sɛngá mokambi asalela mesa na molɔngɔ.',
    'data_entry.no_cols_heading'      => '⚠️ Molɔngɔ moko te eyebani',
    'data_entry.no_cols_desc'         => 'Bamesa ezali, kasi molɔngɔ ezali te na mesa ya sikoyo.',
    'data_entry.admin_cols_prompt'    => 'Lokola mokambi, kende na <strong>Kokambi mesa</strong> mpo na kotya molɔngɔ.',
    'data_entry.contact_admin_cols'   => 'Sɛngá mokambi asalela molɔngɔ ya mesa oyo.',
    'data_entry.active_table_label'   => 'Mesa ya kotisa sango ya sikoyo:',
    'data_entry.add_entry_summary'    => '➕ Bakisa mbonimboni ya sika (Kanda mpo na kofungola)',
    'data_entry.bool_yes_true'        => 'Ee / Ya solo',
    'data_entry.bool_no_false'        => 'Te / Ya lokuta',
    'data_entry.bool_male'            => 'Mobali',
    'data_entry.bool_female'          => 'Mwasi',
    'data_entry.bool_true'            => 'Ya solo',
    'data_entry.bool_false'           => 'Ya lokuta',
    'data_entry.bool_tick'            => '✔ (Emonani)',
    'data_entry.bool_cross'           => '✘ (Ebuki)',
    'data_entry.date_title_hint'      => 'Ndima mikolo ya mobimba to ya mwa moke (na ndakisa 1842 to 1842-05)',
    'data_entry.enter_value_placeholder' => 'Kotisa motuya...',
    'data_entry.submit_data_btn'      => 'Tinda sango',
    'data_entry.shortcuts_tip'        => '💡 Makanisi: Finika <strong>Ctrl + Enter</strong> mpo na kutinda, to <strong>Esc</strong> mpo na kolangula esika ya sikoyo.',
    'data_entry.dup_heading'          => '⚠️ Likama ya kokokana',
    'data_entry.dup_desc'             => 'Ezwami mbonimboni ya kokokana na ebongiseli:',
    'data_entry.dup_item_format'      => 'ID ya Mbonimboni: %d — Motuya: %s',
    'data_entry.dup_prompt'           => 'Olingi kokoba na kobomba mbonimboni oyo ya kokokana?',
    'data_entry.dup_confirm_btn'      => 'Ee, ndima na bomba',
    'data_entry.search_summary'       => '🔍 Boluka na kosala sika mbonimboni ezali (Kanda mpo na kofungola)',
    'data_entry.date_to_label'        => 'kaka',
    'data_entry.filter_all_option'    => '-- Nionso --',
    'data_entry.filter_placeholder'   => 'Sala sika...',
    'data_entry.apply_filters_btn'    => 'Sala sika ya boluki',
    'data_entry.reset_filter_btn'     => 'Bongola sika',
    'data_entry.csv_entire_btn'       => 'Kita CSV mobimba',
    'data_entry.json_entire_btn'      => 'Kita JSON mobimba',
    'data_entry.copy_entire_btn'      => 'Kopa mesa mobimba',
    'data_entry.csv_filtered_btn'     => 'Kita CSV oyo eponami',
    'data_entry.json_filtered_btn'     => 'Kita JSON oyo eponami',
    'data_entry.copy_filtered_btn'    => 'Kopa mesa oyo eponami',
    'data_entry.clipboard_alert'      => 'Sango ya mesa ekopiami! Okoki kɔ́tisa na Excel to Google Sheets.',
    'data_entry.existing_records_heading' => 'Mesa ya mbonimboni ezali',
    'data_entry.th_added_by'          => 'Ebakisami na',
    'data_entry.th_date_created'      => 'Mikolo ya bokeli',
    'data_entry.no_records'           => 'Mbonimboni moko te ezwama.',
    'data_entry.na_value'             => 'N/A',
    'data_entry.page_label'           => 'Paje:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Kozongisa paswedi',
    'forgot_password.heading'         => 'Bongisa paswedi na yo',
    'forgot_password.subheading'      => 'Kotisa adresi ya email ya konti na yo na nse, tokotinda nzela ya bokenisi mpo na kobongisa paswedi.',
    'forgot_password.email_label'     => 'Adresi ya email:',
    'forgot_password.submit_btn'      => 'Tinda nzela ya kobongisa',
    'forgot_password.back_login_link' => 'Zonga na paje ya kokɔtá',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Kokɔtá ya moto',
    'login.heading'              => 'Kokɔtá ya moto',
    'login.username_label'       => 'Kombo ya mosangani to email:',
    'login.password_label'       => 'Paswedi:',
    'login.submit_btn'           => 'Kɔtá',
    'login.forgot_password_link' => ' Obosani paswedi?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Mbote — Fɔlɔ ya bokambi ya konti',
    'onboarding.heading'           => 'Mbote na libota!',
    'onboarding.subheading'        => 'Liboso ya kobanda, pɛpisa mwa ntina mpo na komonisa ndako na yo na bokenisi. Okoki kobongisa yango ntango nionso.',
    'onboarding.timezone_label'    => 'Ntango / Ndako:',
    'onboarding.date_format_label' => 'Ndenge ya komonisa mikolo:',
    'time_format_label'            => 'Ndenge ya ngonga:',
    'onboarding.time_24'          => 'Ngonga 24 (na ndakisa 16:07)',
    'onboarding.time_12'          => 'Ngonga 12 AM/PM (na ndakisa 04:07 PM)',
    'onboarding.time_none'        => 'Mikolo kaka (Kokanga ngonga mpenza)',
    'onboarding.attribution_label' => 'Ndenge ya komonisa kombo na mabonzi:',
    'onboarding.attribution_desc1' => 'Kokambi ndenge kombo na yo emonanaka na molɔngɔ ya mabonzi na mbonimboni.',
    'onboarding.attr_anon_title'   => 'Moto ya koyeba te:',
    'onboarding.attr_anon_text'    => 'Komonisa mwa liboso na motango ya moko na ye moko na bato nionso.',
    'onboarding.attr_public_title' => 'Pobliki:',
    'onboarding.attr_public_text'  => 'Komonisa kombo mobimba na bato nionso.',
    'onboarding.attr_vol_title'    => 'Basungi kaka:',
    'onboarding.attr_vol_text'     => 'Komonisa mwa liboso na bato nionso, kasi kombo mobimba na basungi, bakɛngɛli, na bakambi.',
    'onboarding.attr_opt_anon'     => 'Moto ya koyeba te (Mwa liboso na motango)',
    'onboarding.attr_opt_public'   => 'Pobliki (Komonisa kombo mobimba)',
    'onboarding.attr_opt_vol'      => 'Basungi kaka',
    'onboarding.submit_btn'        => 'Bomba na kokoba',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Kokambi konti ya moto',
    'profile.heading'              => 'Konti ya moto na Bokenisi',
    'profile.personal_details_heading' => 'Sango ya moto moko',
    'profile.language_label'       => 'Lokótá olingi:',
    'profile.lang_site_default'    => 'Lokótá ya site',
    'profile.update_details_btn'   => 'Bongisa sango ya moto',
    'profile.email_heading'        => 'Adresi ya email',
    'profile.current_email_label'  => 'Email ya sikoyo:',
    'profile.email_verified'       => '(Esikisami)',
    'profile.email_unverified'     => '(Esikisami te - Tala email na yo)',
    'profile.change_email_label'   => 'Bongisa adresi ya email:',
    'profile.aria_new_email'       => 'Adresi ya email ya sika',
    'profile.update_email_btn'     => 'Bongisa email na kosikisa',
    'profile.password_heading'     => 'Bongisa paswedi',
    'profile.current_password_label' => 'Paswedi ya sikoyo:',
    'profile.new_password_label'   => 'Paswedi ya sika (bakomi 8 min.):',
    'profile.confirm_password_label' => 'Zongisa paswedi ya sika:',
    'profile.show_passwords_label' => 'Moni paswedi na bakomi ya pɔtɔpɔtɔ',
    'profile.update_password_btn'  => 'Bongisa paswedi',
    'profile.tfa_heading'          => 'Bokenisi ya mbala mibale (2FA)',
    'profile.tfa_status_label'     => 'Bofulami:',
    'profile.tfa_enabled'          => 'Epesami nzela',
    'profile.tfa_disabled'         => 'Epekisami',
    'profile.setup_tfa_btn'        => 'Bongisa Google Authenticator',
    'profile.tfa_active_desc'      => '2FA ebatelaka konti na yo malamu.',
    'profile.backup_codes_heading' => 'Ba uyi ya kobomba ya bokenisi ya sika',
    'profile.download_codes_btn'   => 'Kita ba uyi lokola .txt',
    'profile.generate_codes_confirm' => 'Ozali na boyokani? Oyo ekosimba ba uyi ya kala.',
    'profile.generate_codes_btn'   => 'Sala ba uyi ya sika',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Komikoma ya moto',
    'register.heading'        => 'Komikoma konti ya sika',
    'register.username_label' => 'Kombo ya mosangani:',
    'register.submit_btn'     => 'Komikoma',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Uyi ya bokeli ezali malamu te.',
    'set_password.exit_expired_token'        => 'Nzela ya paswedi oyo esili ntango to ezali malamu te.',
    'set_password.proceed_login_btn'         => 'Kende na paje ya kokɔtá',
    'set_password.aria_region'               => 'Tia paswedi',
    'set_password.heading_format'            => 'Tia paswedi mpo na %s',
    'set_password.subheading_format'         => 'Mbote na konti na yo ya sika, %s! Pɔnɔ paswedi na yo na nse.',
    'set_password.new_password_label'        => 'Paswedi ya sika (bakomi 8 min.):',
    'set_password.confirm_password_label'    => 'Zongisa paswedi:',
    'set_password.show_password_label'       => 'Moni paswedi',
    'set_password.save_password_btn'         => 'Bomba paswedi',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => 'Fɔlɔ ya bokeli ya 2FA',
    'setup_2fa.heading'          => 'Bongisa Google Authenticator',
    'setup_2fa.subheading'       => 'Tala kóti QR na nse na apiliki ya bokenisi.',
    'setup_2fa.qr_alt'           => 'Kóti QR ya 2FA',
    'setup_2fa.manual_prompt'    => 'To kotisa uyi oyo na loboko:',
    'setup_2fa.backup_heading'   => 'Ba uyi ya kobomba ya bokenisi ya ntina',
    'setup_2fa.backup_desc'      => 'Bomba ba uyi oyo na esika ya kimya. Okoki kosalela kóti moko <strong>mbala moko kaka</strong> soki ozangi apiliki na yo:',
    'setup_2fa.download_btn'     => 'Kita ba uyi lokola .txt',
    'setup_2fa.code_label'       => 'Kotisa kóti ya biteni 6 uta na apiliki mpo na kosikisa:',
    'setup_2fa.aria_code_input'  => 'Kóti ya biteni 6',
    'setup_2fa.submit_btn'       => 'Sikisa na ndima 2FA',
    'setup_2fa.cancel_link'      => 'Tiká na zonga na konti',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Tinda mbongwana',
    'suggest_edit.heading_prefix'       => 'Tinda mbongwana mpo na mbonimboni',
    'suggest_edit.return_btn'           => 'Zonga na mbonimboni',
    'suggest_edit.success_msg_suffix'   => 'Okoki kutinda mbongwana mosusu na nse, to kosalela nzela ya kozonga na likolo.',
    'suggest_edit.current_values_heading' => 'Motuya ya sikoyo:',
    'suggest_edit.empty_label'          => '(goullo)',
    'suggest_edit.submit_heading'       => 'Tinda motuya ya sika na mbonimboni',
    'suggest_edit.confirm_prompt'       => 'Ozali na boyokani ya kutinda mbongwana oyo mpo na kotalela?',
    'suggest_edit.select_column_label'  => 'Pɔnɔ molɔngɔ ya kobongisa:',
    'suggest_edit.reasoning_label'      => 'Mbonimboni / Ntina / Maloba ya stɔru:',
    'suggest_edit.reasoning_placeholder'=> 'Pesa sango to ntina ya mbongwana oyo...',
    'suggest_edit.submit_btn'           => 'Tinda mpo na kotalela',
    'suggest_edit.proposed_value_label' => 'Motuya ya sika esengi:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => 'Sikisa 2FA',
    'verify_2fa.heading'         => 'Bokenisi ya mbala mibale',
    'verify_2fa.subheading'      => 'Kotisa kóti ya biteni 6 uta na apiliki to uyi ya kobomba.',
    'verify_2fa.code_label'      => 'Kóti ya bosikisoki / Uyi ya bokenisi:',
    'verify_2fa.aria_code_input' => 'Kotisa kóti ya bosikisoki to bokenisi',
    'verify_2fa.submit_btn'      => 'Sikisa na kɔtá',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Uyi ya bosikisoki epesami te.',
    'verify_email.err_invalid_token'    => 'Uyi ya bosikisoki ezali malamu te.',
    'verify_email.msg_already_verified' => 'Email na yo esikisami esili. Okoki kɔ́tá.',
    'verify_email.err_expired_token'    => 'Nzela ya bosikisoki esili ntango (mikolo 24 esili). Komikoma lisusu to sɛngá nzela ya sika.',
    'verify_email.msg_success'          => 'Email esikisami malamu! Konti na yo ezali kosala sikoyo. Okoki kɔ́tá.',
    'verify_email.err_update_failed'    => 'Mwa pasi ezwami na bosikisoki ya email, meka lisusu.',
    'verify_email.aria_region'          => 'Bofulami ya bosikisoki ya email',
    'verify_email.heading'              => 'Bofulami ya bosikisoki ya email',
    'verify_email.login_btn'            => 'Kanda awa mpo na kɔtá',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Fɔlɔ ya mosungi',
    'volunteer.honeypot_label'       => 'Tika esika oyo goullo:',
    'volunteer.required_field_title' => 'Esika ya ntina',
    'volunteer.multi_select_hint'    => 'Finika Ctrl to Cmd mpo na kopona ebele.',
    'volunteer.submit_btn'           => 'Tinda mituna ya mosungi',
];
