<?php
// lang/ga.php - Irish (Gaeilge)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Logáil isteach',
    'nav.logout'                 => 'Logáil amach',
    'nav.feedback'               => 'Aiseolas',
    'nav.volunteer'              => 'Cuidigh linn',
    'nav.leaderboard'            => 'Clár Scóir',
    'nav.search'                 => 'Cuardaigh',
    'nav.settings'               => 'Socruithe an Suímh',
    'nav.high_contrast'          => 'Codarsnacht Ard',
    'nav.low_contrast'           => 'Codarsnacht Íseal',
    'nav.welcome'                => 'Fáilte,',
    'nav.data_entry'             => 'Iontráil Sonraí',
    'nav.moderation'             => 'Measúnú',
    'nav.invite_user'            => 'Tabhair cuireadh d’úsáideoir',
    'nav.manage_users'           => 'Bainistigh úsáideoirí',
    'nav.manage_tables'          => 'Bainistigh táblaí',
    'nav.volunteer_dashboard'    => 'Painéal na gCuiditheoirí',
    'nav.feedback_dashboard'     => 'Painéal Aisíocaíochtaí',
    'nav.leaderboard_score'      => 'Scór an Chláir Scóir',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Scagairí Cuardaigh Ilcholúin',
    'search.reset'               => 'Athshocraigh an cuardach',
    'search.export_csv'          => 'Íoslódáil na torthaí scagtha mar CSV',
    'search.no_records'          => 'Níor friteadh aon taifead sa tábla seo.',
    'search.load_error'          => 'Níorbh fhéidir na torthaí a luchtú. Déan iarracht arís le do thoil.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Cuir isteach',
    'btn.cancel'                 => 'Cealaigh',
    'btn.save'                   => 'Sábháil',
    'btn.delete'                 => 'Scrios',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Go raibh maith agat! Cuireadh d’aiseolas isteach go rathúil.',
    'feedback.error_all_fields'   => 'Tá gach réimse éigeantach.',
    'feedback.error_invalid_email'=> 'Cuir isteach seoladh ríomhphoist bailí le do thoil.',
    'feedback.error_save_failed'  => 'Tharla earráid agus d’aiseolas á shábháil. Déan iarracht arís le do thoil.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Níor friteadh aon tábla bunachair sonraí',
    'index.no_tables_desc'             => 'Níl aon tábla bunachair sonraí gníomhach cumraithe ag an gcóras faoi láthair.',
    'index.admin_create_table_guide'   => 'Mar riarthóir, téigh go dtí an rogha roghchláir <strong>Bainistigh táblaí</strong> chun tábla a chruthú, agus cuir colún amháin ar a laghad leis sular féidir taifid a thaispeáint nó a iontráil.',
    'index.go_to_manage_tables'        => 'Téigh go dtí Bainistigh táblaí',
    'index.contact_admin_tables'       => 'Déan teagmháil le riarthóir chun táblaí agus colúin bhunachair sonraí a chumrú.',
    'index.guest_login_tables_guide'   => 'Le do thoil, <a href=":login_link">logáil isteach</a> mar riarthóir, téigh go dtí an roinn <strong>Bainistigh táblaí</strong> chun tábla a chruthú, agus ansin cuir colún amháin ar a laghad leis.',
    'index.no_columns_heading'         => 'Níl aon cholún cumraithe',
    'index.no_columns_desc'            => 'Tá táblaí ann sa chóras, ach níor sainíodh aon cholún sonraí don tábla gníomhach.',
    'index.admin_add_columns_guide'    => 'Mar riarthóir, téigh go dtí an rogha roghchláir <strong>Bainistigh táblaí</strong> chun colún amháin ar a laghad a chur le do thábla.',
    'index.contact_admin_columns'      => 'Déan teagmháil le riarthóir chun colúin a chumrú don tábla seo.',
    'index.select_directory_database'  => 'Roghnaigh bunachar sonraí an eolaire:',
    'index.opt_yes_true'               => 'Sea / Fíor',
    'index.opt_no_false'               => 'Ní hea / Bréagach',
    'index.opt_male'                   => 'Fireann',
    'index.opt_female'                 => 'Baineann',
    'index.opt_true'                   => 'Fíor',
    'index.opt_false'                  => 'Bréagach',
    'index.opt_tick'                   => '✔ (Seicmharc)',
    'index.opt_cross'                  => '✘ (Cros)',
    'index.option_all'                 => '-- Gach --',
    'index.date_to_label'              => 'go dtí',
    'index.search_placeholder'         => 'Cuardaigh...',
    'index.download_entire_csv'        => 'Íoslódáil an CSV iomlán',
    'index.download_entire_json'       => 'Íoslódáil an JSON iomlán',
    'index.copy_entire_table'          => 'Cóipeáil an tábla iomlán',
    'index.download_filtered_csv'      => 'Íoslódáil an CSV scagtha',
    'index.download_filtered_json'     => 'Íoslódáil an JSON scagtha',
    'index.copy_filtered_table'        => 'Cóipeáil an tábla scagtha',
    'index.th_record_id'               => 'ID an Taifid',
    'index.th_created_by'              => 'Cruthaithe ag',
    'index.th_date_added'              => 'Dáta curtha leis',
    'index.th_actions'                 => 'Gníomhartha',
    'index.modal_heading'              => 'Mol ceartúchán taifid',
    'index.modal_desc'                 => 'Cuir isteach ceartúchán nó faisnéis malartach don taifead seo. Déanfaidh ár bhfoireann measúnaithe athbhreithniú air.',
    'index.modal_target_column'        => 'An cholún spriocdhírithe:',
    'index.modal_proposed_value'       => 'An ceartúchán / luach molta:',
    'index.modal_input_placeholder'    => 'Cuir isteach an fhaisnéis nuashonraithe...',
    'index.modal_submit_btn'           => 'Cuir an moladh isteach',
    'index.clipboard_success'          => 'Cóipeáladh sonraí an tábla chuig an ngearrthaisce! Is féidir leat iad a ghreamú go díreach in Excel nó Google Sheets.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Foirm cuireadh d’úsáideoir nua',
    'create_user.subheading'           => 'Ginfidh sé seo nasc socruithe slán 24 uair an chloig agus seolfar chuig an úsáideoir go díreach é trí ríomhphost.',
    'create_user.first_name'           => 'Ainm:',
    'create_user.surname'              => 'Sloinne:',
    'create_user.username_label'       => 'Ainm úsáideora (Roghnach):',
    'create_user.username_placeholder' => 'Fág bán le haghaidh giniúint uathoibríoch',
    'create_user.username_help'        => 'Má fhágతరar bán é, ginfear ainm úsáideora uathúil go huathoibríoch ó a n-ainm.',
    'create_user.email_label'          => 'Seoladh ríomhphoist:',
    'create_user.role_label'           => 'Ról úsáideora:',
    'create_user.submit_btn'           => 'Cruthaigh úsáideoir & cuir cuireadh',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Painéal ticéad tacaíochta & aiseolais',
    'feedback_dash.subheading'           => 'Bainistigh iarratais tacaíochta poiblí, nuashonraigh stádais agus glac páirt i gcomhrá díreach.',
    'feedback_dash.manage_emails'        => 'Bainistigh teimpléid ríomhphoist',
    'feedback_dash.manage_schema'        => 'Bainistigh scéimre fhoirm na dticéad',
    'feedback_dash.th_ticket_date'       => 'ID an ticéid / Dáta',
    'feedback_dash.th_submitter'         => 'An seoltóir',
    'feedback_dash.th_subject_info'      => 'Ábhar / Bunfhaisnéis',
    'feedback_dash.th_status'            => 'Stádas',
    'feedback_dash.no_tickets'           => 'Níor friteadh aon ticéad aiseolais.',
    'feedback_dash.anonymous'            => 'Gan ainm',
    'feedback_dash.default_subject'      => 'Fiosrúchán ginearálta',
    'feedback_dash.open_ticket_btn'      => 'Oscail an ticéad & an comhrá',
    'feedback_dash.delete_confirm'       => 'Scrios an ticéad tacaíochta seo agus gach freagra a ghabhann leis?',
    'feedback_dash.msg_deleted'          => 'Scriosadh an ticéad #:id go rathúil.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Teimpléid ríomhphoist ticéad tacaíochta & truicears',
    'feedback_emails.subheading'         => 'Saincheap na fógraí ríomhphoist uathoibríoch a seoltar le linn thimpeallacht saibhis na dticéad. Úsáid lúibíní cuacha le haghaidh sealbhóirí áite dinimiciúla.',
    'feedback_emails.back_to_dashboard' => 'Ar ais go painéal na dticéad aiseolais',
    'feedback_emails.email_subject'      => 'Ábhar an ríomhphoist:',
    'feedback_emails.email_body'         => 'Teimpléad chorp an ríomhphoist:',
    'feedback_emails.save_template_btn' => 'Sábháil an teimpléad',
    'feedback_emails.placeholders_heading' => 'Sealbhóirí áite atá ar fáil',
    'feedback_emails.placeholders_desc' => 'Is féidir leat na clibeanna seo a úsáid in áit ar bith i do theimpléid ábhair nó coirp:',
    'feedback_emails.fixed_tags'         => 'Clibeanna lárnacha seasta:',
    'feedback_emails.custom_tags'        => 'Clibeanna scéimre saincheaptha:',
    'feedback_emails.custom_tags_desc'   => 'Ginte go huathoibríoch ó réimsí do thógálaí fhoirm na dticéad:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Bainistíocht scéimre fhoirm an aiseolais',
    'feedback_schema.subheading'             => 'Cumraigh réimsí saincheaptha, cineálacha sonraí, teorainneacha karakter, fo-chineálacha, roghanna agus socruithe cur i láthair fhoirm ginearálta.',
    'feedback_schema.settings_summary'       => 'Cumraigh teideal na foirm & téacs an tséanaigh',
    'feedback_schema.form_title_label'       => 'Teideal na foirm:',
    'feedback_schema.form_intro_label'       => 'Téacs tús / Cur síos:',
    'feedback_schema.save_settings_btn'      => 'Sábháil socruithe na foirm',
    'feedback_schema.edit_field_title'       => 'Eagraigh réimse an ticéid:',
    'feedback_schema.add_field_title'        => '+ Cuir réimse fhoirm ticéad nua leis',
    'feedback_schema.field_name_label'       => 'Lipéad / Ainm an réimse:',
    'feedback_schema.data_type_label'        => 'Cineál sonraí:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Téacs gearr)',
    'feedback_schema.type_text'              => 'TEXT (Alt fada / Teachtaireacht)',
    'feedback_schema.type_int'               => 'INT (Uimhir slán)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Bratach Sea/Ní hea)',
    'feedback_schema.type_date'              => 'DATE (Dáta féilire)',
    'feedback_schema.subtype_label'          => 'Fo-chineál réimse / Stíl rindreála ionchuir:',
    'feedback_schema.subtype_standard'       => '-- Caighdeánach --',
    'feedback_schema.subtype_standard_lower'=> 'caighdeánach',
    'feedback_schema.options_label'          => 'Roghnacháin (scartha le camóga nó ceann in aghaidh an líne):',
    'feedback_schema.options_help'           => 'Soláthair roghanna scartha le camóga nó briseadh líne.',
    'feedback_schema.allow_multiple'         => 'Ceadaigh roghanna iolracha a roghnú (Ilroghnú)',
    'feedback_schema.boolean_format'         => 'Formáid taispeána Boole:',
    'feedback_schema.max_length_label'       => 'Uasmhéid / Fad (Teorainn charachtar roghnach):',
    'feedback_schema.is_required_label'      => 'Déan an réimse seo éigeantach do sheoltóirí',
    'feedback_schema.save_field_btn'         => 'Sábháil athruithe an réimse',
    'feedback_schema.create_field_btn'       => 'Cruthaigh réimse an ticéid',
    'feedback_schema.sub_email'              => 'Ríomhphost',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Roghchlár anuas',
    'feedback_schema.sub_radio'              => 'Grúpa raidió',
    'feedback_schema.sub_checkbox'           => 'Bosca seiceála',
    'feedback_schema.sub_textarea'           => 'Bosca alt',
    'feedback_schema.sub_number'             => 'Ionchur uimhriúil',
    'feedback_schema.existing_fields_heading'=> 'Réimsí ticéid atá ann cheana',
    'feedback_schema.th_move'                => 'Bog',
    'feedback_schema.th_field_name'          => 'Ainm an réimse',
    'feedback_schema.th_data_type'           => 'Cineál sonraí',
    'feedback_schema.th_subtype'             => 'Fo-chineál',
    'feedback_schema.th_required'            => 'Éigeantach?',
    'feedback_schema.th_max_length'          => 'Fad uasta',
    'feedback_schema.th_created_by'          => 'Cruthaithe ag',
    'feedback_schema.no_fields'              => 'Níl aon réimse ticéad saincheaptha sainithe fós.',
    'feedback_schema.system_user'            => 'Córas',
    'feedback_schema.edit_btn'               => 'Eagraigh',
    'feedback_schema.delete_confirm'         => 'Scrios an réimse seo agus gach luach freagra gaolmhar?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Bainistíocht dinimiciúil táblaí & scéimrí',
    'manage_tables.subheading'           => 'Cruthaigh, iniúch, modhnaigh nó dífhostaigh go sábháilte táblaí dinimiciúla an fheidhmchláir agus a scéimrí colún bunúsacha.',
    'manage_tables.switcher_label'       => 'Roghnaigh scéimre tábla gníomhach:',
    'manage_tables.edit_metadata_btn'    => 'Eagraigh meiteashonraí an tábla',
    'manage_tables.delete_table_confirm'=> 'RABHADH: Scriosfaidh scriosadh an tábla seo gach colún agus ábhar sábháilte go buan. An bhfuil tú cinnte go hiomlán?',
    'manage_tables.delete_table_btn'     => 'Scrios an tábla',
    'manage_tables.edit_table_summary'   => 'Eagraigh sainmhíniú an tábla:',
    'manage_tables.create_table_summary'=> '+ Cruthaigh tábla dinimiciúil nua',
    'manage_tables.table_name_label'     => 'Ainm cairdiúil an tábla:',
    'manage_tables.table_desc_label'     => 'Cur síos / Cuspóir:',
    'manage_tables.save_table_btn'       => 'Sábháil athruithe an tábla',
    'manage_tables.create_table_btn'     => 'Cruthaigh scéimre an tábla',
    'manage_tables.edit_col_summary'     => 'Eagraigh colún dinimiciúil:',
    'manage_tables.add_col_summary_prefix' => '+ Cuir colún tábla nua leis le haghaidh',
    'manage_tables.col_name_label'       => 'Ainm an cholúin:',
    'manage_tables.type_text_long'       => 'TEXT (Alt fada)',
    'manage_tables.date_behavior_label' => 'Iompar cuardaigh dáta:',
    'manage_tables.date_bhv_manual'      => 'Dátaí sa bhunachar sonraí (iontráil láimhe amháin)',
    'manage_tables.date_bhv_admin'       => 'Dátaí riaracháin amháin',
    'manage_tables.date_bhv_all'         => 'Gach dáta lena n-áirítear riarachán',
    'manage_tables.req_toggle_label'     => 'Déan an colún seo éigeantach (iontráil sonraí éigeantach)',
    'manage_tables.exclude_search_label'=> 'Eiscigh an colún seo ón gcuardach poiblí (index.php)',
    'manage_tables.create_col_btn'       => 'Cruthaigh an colún',
    'manage_tables.existing_cols_heading_prefix' => 'Colúin atá ann cheana le haghaidh',
    'manage_tables.th_public_search'     => 'Cuardach poiblí?',
    'manage_tables.th_display_format'    => 'Formáid taispeána',
    'manage_tables.th_date_created'      => 'Dáta cruthaithe',
    'manage_tables.no_columns_found'     => 'Níl aon cholún dinimiciúil sainithe don tábla seo fós.',
    'manage_tables.status_hidden'        => 'Faoi rún',
    'manage_tables.delete_col_confirm'   => 'RABHADH: Bainfidh scriosadh an cholúin seo gach sonra cille gaolmhar i ngach taifead freisin. An bhfuil tú cinnte?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Bainistigh teimpléid ríomhphoist fógraí úsáideora',
    'user_emails.subheading'             => 'Saincheap na leagan amach ríomhphoist a seoltar nuair a thugtar cuireadh d’úsáideoirí nó nuair a seoltar naisc athshocraithe pasfhocail.',
    'user_emails.select_template_label'=> 'Roghnaigh teimpléad le heagrú:',
    'user_emails.opt_invitation'         => 'Teimpléad cuireadh cuntas úsáideora',
    'user_emails.opt_reset'              => 'Teimpléad athshocraithe pasfhocail / nasc rochtana',
    'currently_editing'                  => 'Á eagrú faoi láthair:',
    'user_emails.desc_invitation'        => 'Seoltar go huathoibríoch é nuair a chruthaíonn riarthóir cuntas úsáideora nua nó nuair a thugann sé cuireadh dó.',
    'user_emails.desc_reset'             => 'Seoltar é nuair a thionscain riarthóir athshocrú pasfhocail nó nuair a athsheolann sé nasc rochtana.',
    'user_emails.email_body_label'       => 'Corp an ríomhphoist:',
    'user_emails.back_to_creation'       => 'Ar ais go cruthú úsáideoirí',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Teimpléid ríomhphoist cuiditheoirí & truicears',
    'volunteer_emails.subheading'        => 'Saincheap na freagraí ríomhphoist uathoibríoch a seoltar chuig cuiditheoirí le linn céimeanna éagsúla den tsruthoibríocht. Úsáid lúibíní cuacha le haghaidh sealbhóirí áite dinimiciúla.',
    'volunteer_emails.back_to_dashboard'=> 'Ar ais chuig iarratais na gcuiditheoirí',
    'volunteer_emails.custom_tags_desc'  => 'Ginte go huathoibríoch ó réimsí do thógálaí fhoirm:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Bainistíocht scéimre fhoirm na gcuiditheoirí',
    'volunteer_schema.subheading'        => 'Cumraigh réimsí saincheaptha, cineálacha sonraí, fo-chineálacha, roghanna agus socruithe cur i láthair fhoirm ginearálta.',
    'volunteer_schema.back_to_dashboard'=> 'Ar ais chuig iarratais na gcuiditheoirí',
    'volunteer_schema.settings_summary'  => 'Cumraigh teideal na foirm & téacs an tséanaigh',
    'volunteer_schema.edit_field_title'  => 'Eagraigh an réimse:',
    'volunteer_schema.add_field_title'   => '+ Cuir réimse fhoirm cuiditheora nua leis',
    'volunteer_schema.create_field_btn'  => 'Cruthaigh an réimse',
    'volunteer_schema.existing_fields_heading' => 'Réimsí fhoirm cuiditheoirí atá ann cheana',
    'volunteer_schema.no_fields'         => 'Níl aon réimse cuiditheora saincheaptha sainithe fós.',
    'volunteer_schema.delete_confirm'    => 'Scrios an réimse seo agus gach luach freagra gaolmhar?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Athbhreithniú ar mholtaí feithimh',
    'moderate.subheading'                => 'Déan comparáid idir athruithe a mhol úsáideoirí agus taifid bheo ar do tháblaí ceadaithe. Ceadaigh moltaí, sáigh luachanna nó diúltaigh moltaí.',
    'moderate.shortcut_label'            => 'Leip sin aicearra méarchláir:',
    'moderate.shortcut_desc'             => 'Brúigh Ctrl + Enter chun ceadú go tapa nó Esc chun an bosca sáite a ghlanadh!',
    'moderate.th_id_date'                => 'ID / Dáta',
    'moderate.th_table_record'           => 'Tábla, taifead & colún',
    'moderate.th_comparison'             => 'Comparáid (Beo vs Molta) & Fianaise',
    'moderate.th_actions'                => 'Gníomhartha an Mheasúnóra',
    'moderate.no_suggestions'            => 'Níor friteadh aon moladh feithimh do do tháblaí measúnaithe ceadaithe.',
    'moderate.by_label'                  => 'Le:',
    'moderate.guest_user'                => 'Amharcán / Aoi',
    'moderate.record_id_label'           => 'ID an Taifid:',
    'moderate.column_label'              => 'Colún:',
    'moderate.required_badge'            => 'Éigeantach',
    'moderate.live_value_label'          => 'Luach beo reatha:',
    'moderate.empty_placeholder'         => '[Folamh]',
    'moderate.proposed_value_label'      => 'Athrú molta:',
    'moderate.evidence_label'            => 'Fianaise / Údar:',
    'moderate.no_evidence'               => 'Níor soláthraíodh aon fhianaise / údar.',
    'moderate.override_label'            => 'Sáraigh an luach:',
    'moderate.select_placeholder'        => '-- Roghnaigh --',
    'moderate.historical_dates_title'    => 'Dátaí stairiúla a thacaítear leo',
    'moderate.approve_confirm'           => 'Ceadaigh agus cuir i feidhm an luach seo?',
    'moderate.decline_confirm'           => 'Diúltaigh agus caith uait an moladh seo?',
    'moderate.approve_btn'               => 'Ceadaigh',
    'moderate.decline_btn'               => 'Diúltaigh',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Bainisteoir fógraí & fógraí an suímh',
    'notices.subheading'                 => 'Cruthaigh foláirimh dinimiciúla, bratacha fáilte nó fógraí spriocdhírithe do róil úsáideora sonracha.',
    'notices.error_blank'                => 'Ní féidir leis an teideal agus an t-ábhar bheith bán.',
    'notices.msg_created'                => 'Cruthaíodh an fógra go rathúil!',
    'notices.msg_deleted'                => 'Scriosadh an fógra.',
    'notices.create_heading'             => 'Cruthaigh fógra nua',
    'notices.title_label'                => 'Teideal / Ceannteideal an fhógra:',
    'notices.content_label'              => 'Ábhar an fhógra (Ceadaítear HTML/Téacs):',
    'notices.target_roles_label'         => 'Pobal sprice (Roghnaigh róil nó gach duine):',
    'notices.role_everyone'              => 'Gach duine',
    'notices.role_public'                => 'Poiblí (Aoi)',
    'notices.role_users'                 => 'Úsáideoirí',
    'notices.role_moderators'            => 'Measúnóirí',
    'notices.role_admins'                => 'Riarthóirí',
    'notices.dismissible_label'          => "Inscortha (San áireamh cnaipe dúnta 'X')",
    'notices.display_order_label'        => 'Ord taispeána:',
    'notices.publish_btn'                => 'Foilsigh an fógra',
    'notices.existing_heading'           => 'Fógraí gníomhacha & atá ann cheana',
    'notices.th_order'                   => 'Ord',
    'notices.th_title'                   => 'Teideal',
    'notices.th_target_roles'            => 'Róil sprice',
    'notices.th_dismissible'             => 'Inscortha',
    'notices.no_notices'                 => 'Níl aon fhógra cruthaithe fós.',
    'notices.yes'                        => 'Sea',
    'notices.no_sticky'                  => 'Ní hea (Greamaitheach / Sticky)',
    'notices.delete_confirm'             => 'Scrios an fógra seo?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Socruithe domhanda an suímh, modúil & ceadanna',
    'settings.subheading'                => 'Bainistigh cumraíochtaí lárnacha, tiománaithe ríomhphoist, roghanna slándála/CAPTCHA, modúil gnéithe, modh cothabhála, fógraí an suímh agus cumas na ról.',
    'settings.tab_core'                  => 'Lárnach & Ríomhphost',
    'settings.tab_modules'               => 'Modúil',
    'settings.tab_maintenance'           => 'Cothabháil',
    'settings.tab_notices'               => 'Fógraí an Suímh',
    'settings.tab_permissions'           => 'Róil & Ceadanna',
    'settings.tab_audit'                 => 'Logáil Iniúchadh',
    'settings.db_updates_heading'        => 'Nuashonruithe an bhunachair sonraí',
    'settings.schema_current'            => 'Leagan reatha an scéimre:',
    'settings.schema_latest'             => 'An ceann is déanaí ar fáil:',
    'settings.download_backup_btn'       => 'Íoslódáil cúltaca an bhunachair sonraí',
    'settings.download_backup_desc'      => 'Sábhálann sé comhad .sql iomlán ar do ríomhaire. Coinnigh in áit shábháilte é sula rithfidh tú nuashonruithe.',
    'settings.schema_update_notice'      => 'Tá nuashonrú bunachair sonraí ar fáil. Íoslódáil cúltaca thuas sula leanann tú ar aghaidh le do thoil.',
    'settings.migration_confirm'         => 'An bhfuil cúltaca den bhunachar sonraí íoslódáilte agat? Cuirfidh sé seo na nuashonruithe scéimre atá ar feitheamh i bhfeidhm.',
    'settings.update_db_btn'             => 'Nuashonraigh an bunachar sonraí',
    'settings.schema_uptodate'           => 'Tá an bunachar sonraí cothrom le dáta.',
    'settings.core_sys_heading'          => 'Socruithe lárnacha an chórais',
    'settings.sys_name_label'            => 'Ainm an chórais / fheidhmchláir:',
    'settings.default_lang_label'        => 'Teanga réamhshocraithe an suímh:',
    'settings.default_lang_desc'         => 'Úsáidtear é d’aíonna agus d’úsáideoirí nár roghnaigh teanga. Cuir comhaid leis faoi lang/ (m.sh. ga.php) chun roghanna breise a thairiscint.',
    'settings.captcha_heading'           => 'Cumraíocht slándála & CAPTCHA',
    'settings.captcha_provider_label'    => 'Inneall soláthraí CAPTCHA:',
    'settings.captcha_none'              => 'Díchumasaithe (Gan CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Socruithe Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Socruithe Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Socruithe hCaptcha',
    'settings.site_key_label'            => 'Eochair an Suímh (Poiblí):',
    'settings.secret_key_label'          => 'Eochair Rúnda (Príobháideach):',
    'settings.mail_heading'              => 'Cumraíocht seachadta ríomhphoist',
    'settings.mail_domain_label'         => 'Fearann ríomhphoist an chórais (Fallback):',
    'settings.mail_from_label'           => "Seoladh ríomhphoist 'Ó' saincheaptha:",
    'settings.mail_from_desc'            => 'Seoladh sainiúil a úsáidtear mar sheoltóir do ríomhphoist amach.',
    'settings.mail_driver_label'         => 'Tiománaí / Inneall ríomhphoist:',
    'settings.driver_native'             => 'Ríomhphost dúchais (Post-relay Postfix áitiúil)',
    'settings.driver_smtp'               => 'SMTP fíordheimhnithe (PHPMailer)',
    'settings.smtp_heading'              => 'Cumraíochtaí freastalaí SMTP',
    'settings.smtp_host_label'           => 'Óstach SMTP:',
    'settings.smtp_port_label'           => 'Port:',
    'settings.smtp_encryption_label'     => 'Criptiú:',
    'settings.enc_tls'                   => 'TLS (Port 587)',
    'settings.enc_ssl'                   => 'SSL (Port 465)',
    'settings.smtp_user_label'           => 'Ainm úsáideora SMTP:',
    'settings.smtp_pass_label'           => 'Pasfhocal SMTP (fág bán chun an ceann reatha a choinneáil):',
    'settings.save_core_mail_btn'        => 'Sábháil socruithe lárnacha & ríomhphoist',
    'settings.test_mail_heading'         => 'Tástáil cumraíocht an ríomhphoist',
    'settings.test_email_label'          => 'Seoladh ríomhphoist an fhaighteora:',
    'settings.send_test_btn'             => 'Seol ríomhphost tástála',
    'settings.modules_heading'           => 'Lasca modúil an fheidhmchláir & rialuithe éifeachtúlachta',
    'settings.modules_subheading'        => 'Cumasú nó díchumasaigh gnéithe chun éifeachtúlacht rith an fheidhmchláir a bharrfheabhsú agus PRD a oiriúnú do do riachtanais imlonnaithe sainiúla.',
    'settings.mod_users'                 => 'Bainistíocht úsáideoirí & rochtain ilúsáideora',
    'settings.mod_users_desc'            => 'Cumasú clárúcháin, bainistíocht úsáideoirí agus fíordheimhniú ilúsáideora. (Fanann rochtain ar phróifíl ar fáil do shlándáil aonúsáideora).',
    'settings.mod_leaderboard'           => 'Clár Scóir & Gamification',
    'settings.mod_leaderboard_desc'      => 'Aithníonn sé iarrachtaí trasscríbhinní agus scóir réalta.',
    'settings.mod_leaderboard_note'      => '(Éilíonn bainistíocht úsáideoirí & rochtain ilúsáideora)',
    'settings.mod_moderation'            => 'Sruth oibre measúnaithe',
    'settings.mod_moderation_desc'       => 'Cumasú athbhreithniú moltaí eagar agus scuaine measúnaithe.',
    'settings.mod_volunteers'            => 'Tairseach na gcuiditheoirí & iarratais',
    'settings.mod_volunteers_desc'       => 'Cumasú fhoirm leasa phoiblí na gcuiditheoirí agus painéal bainistíochta an riarthóra.',
    'settings.mod_feedback'              => 'Aisíocaíochtaí curtha isteach',
    'settings.mod_feedback_desc'         => 'Cumasú fhoirm aiseolais phoiblí agus painéal riek an riarthóra.',
    'settings.save_modules_btn'          => 'Sábháil cumraíochtaí na modúl',
    'settings.maintenance_heading'       => 'Modh cothabhála an chórais',
    'settings.maintenance_toggle'        => 'Cumasaigh an modh cothabhála (Cuir an suíomh as líne)',
    'settings.maintenance_reason_label'  => 'Cúis / Teachtaireacht d’úsáideoirí:',
    'settings.maintenance_eta_label'     => 'Am tuartha chun filleadh (ETA):',
    'settings.save_maintenance_btn'      => 'Sábháil socruithe cothabhála',
    'settings.notices_heading'           => 'Fógraí & fógraí an suímh',
    'settings.add_notice_btn'            => '+ Cuir fógra nua leis',
    'settings.no_notices'                => 'Níl aon fhógra cumraithe fós.',
    'settings.status_active'             => 'Gníomhach',
    'settings.status_inactive'           => 'Neamhghníomhach',
    'settings.notice_content_label'      => 'Ábhar:',
    'settings.save_notice_btn'           => 'Sábháil an fógra',
    'settings.permissions_heading'       => 'Maingin dinimiciúil ról & ceadanna',
    'settings.permissions_subheading'    => 'Tá ceadanna grúpáilte de réir fheidhmeanna an chórais. Leathnaigh ranna chun cumais a chumrú, agus ansin sábháil do nuashonruithe thíos.',
    'settings.th_role'                   => 'Ról',
    'settings.th_capabilities'           => 'Cumais sannta sa ghrúpa seo',
    'settings.save_permissions_btn'      => 'Sábháil maingin na gceadanna',
    'settings.audit_heading'             => 'Taiscéalaí logála iniúchta an chórais',
    'settings.audit_subheading'          => 'Athbhreithniú ar ghníomhartha taifeadta slándála, iontrála sonraí agus measúnaithe. Úsáid na roghanna cothabhála thíos chun loganna a ghlanadh más gá.',
    'settings.purge_all_confirm'         => '⚠️ RABHADH: Scriosfaidh sé seo LOGÁIL INIÚCHTA AN CHÓRAIS UUILE go buan. An bhfuil tú cinnte gur mian leat leanúint ar aghaidh?',
    'settings.clear_all_audit_btn'       => 'Glan gach logáil iniúchta',
    'settings.purge_records_confirm'     => 'An bhfuil tú cinnte gur mian leat gach iontráil iniúchta a bhaineann le taifid a ghlanadh?',
    'settings.clear_records_audit_btn'   => 'Glan logáil na dtaifead amháin',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Stampa ama',
    'settings.th_actor'                  => 'Gníomhaire',
    'settings.th_action'                 => 'Gníomh',
    'settings.th_record_id'              => 'ID an Taifid',
    'settings.th_details'                => 'Mionsonraí',
    'settings.th_ip'                     => 'Seoladh IP',
    'settings.no_audit_logs'             => 'Níor friteadh aon iontráil logála iniúchta.',
    'settings.system_guest'              => 'Córas / Aoi',
    'settings.audit_limit_note'          => 'Ag taispeáint an 250 iontráil logála iniúchta deiridh.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Bainistíocht cuntas úsáideora & measúnú an chláir scóir',
    'admin_users.subheading'             => 'Iniúch stádais úsáideora, sann róil, sáraigh ríomhphoist, tús cuir athshocrú pasfhocail nó cuirí, athshocraigh 2FA nó cuir cuntais ar fionraí.',
    'admin_users.manage_templates_btn'   => 'Bainistigh teimpléid ríomhphoist',
    'admin_users.invite_user_btn'        => 'Tabhair cuireadh d’úsáideoir nua',
    'admin_users.th_username'            => 'Ainm úsáideora',
    'admin_users.th_email_override'      => 'Ríomhphost & Sárú',
    'admin_users.th_role_assignment'     => 'Sannadh róil',
    'admin_users.th_score'               => 'Scór',
    'admin_users.th_status'              => 'Stádas',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Gníomhartha & Measúnú',
    'admin_users.no_users'               => 'Níor friteadh aon úsáideoir.',
    'admin_users.save_email_title'       => 'Sábháil seoladh ríomhphoist nua',
    'admin_users.verified_label'         => 'Fíoraithe:',
    'admin_users.yes'                    => 'Sea',
    'admin_users.no'                     => 'Ní hea',
    'admin_users.protected_admin'        => 'Riarthóir príomhúil cosanta',
    'admin_users.update_btn'             => 'Nuashonraigh',
    'admin_users.status_active'          => 'Gníomhach',
    'admin_users.status_suspended'       => 'Ar fionraí',
    'admin_users.enabled'                => 'Cumasaithe',
    'admin_users.disabled'               => 'Díchumasaithe',
    'admin_users.set_score_btn'          => 'Socraigh an scór',
    'admin_users.resend_invite_confirm' => 'Athsheol ríomhphost cuireadh an chuntais chuig an úsáideoir seo?',
    'admin_users.resend_invite_btn'      => 'Athsheol an cuireadh',
    'admin_users.reset_pwd_confirm'      => 'Seol nasc athshocraithe pasfhocail chuig an úsáideoir seo?',
    'admin_users.reset_password_btn'     => 'Athshocraigh an pasfhocal',
    'admin_users.suspend_confirm'        => 'Cuir an t-úsáideoir ar fionraí agus dún an rochtain mar gheall ar chalaois/sárú?',
    'admin_users.suspend_btn'            => 'Cuir ar fionraí',
    'admin_users.reactivate_btn'         => 'Athghníomhaigh',
    'admin_users.reset_2fa_confirm'      => 'Athshocraigh agus díchumasaigh 2FA don úsáideoir seo?',
    'admin_users.reset_2fa_btn'          => 'Athshocraigh 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Ar ais chuig painéal na dticéad',
    'view_ticket.ticket_heading_prefix'=> 'Ticéad',
    'view_ticket.support_request'      => 'Iarratas tacaíochta',
    'view_ticket.submitted_by'         => 'Curtha isteach ag:',
    'view_ticket.on_date'              => 'ar an',
    'view_ticket.submitted_fields'     => 'Réimsí fhoirm curtha isteach:',
    'view_ticket.ticket_status_label'  => 'Stádas an ticéid:',
    'view_ticket.status_pending'       => 'Ar feitheamh',
    'view_ticket.status_progress'      => 'Ar siúl',
    'view_ticket.status_completed'     => 'Críochnaithe',
    'view_ticket.status_rejected'      => 'Diúltaithe',
    'view_ticket.dialogue_heading'     => 'Snáithe comhrá',
    'view_ticket.no_replies'           => 'Níl aon fhreagra taifeadta fós.',
    'view_ticket.admin_label'          => 'Riarthóir',
    'view_ticket.staff'                => 'Foireann',
    'view_ticket.post_reply_heading'   => 'Postáil freagra & cuir in iúl don seoltóir',
    'view_ticket.reply_placeholder'    => 'Scríobh do fhreagra anseo...',
    'view_ticket.send_reply_btn'       => 'Seol freagra & cuir in iúl don seoltóir trí ríomhphost',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Iarratais cuiditheoirí & sruthoibríocht',
    'volunteer_dashboard.subheading'         => 'Athbhreithniú a dhéanamh ar iarratais, comhráite cuiditheoirí a sceidealú, nótaí interbhia a ghlacadh agus iarratasóirí a ghlacadh isteach sa chóras.',
    'volunteer_dashboard.manage_emails_btn' => 'Bainistigh teimpléid ríomhphoist',
    'volunteer_dashboard.manage_schema_btn' => 'Bainistigh scéimre fhoirm',
    'volunteer_dashboard.th_status'          => 'Stádas',
    'volunteer_dashboard.th_name'            => 'Ainm',
    'volunteer_dashboard.th_interview_notes'=> 'Interbhia / Nótaí',
    'volunteer_dashboard.no_submissions'     => 'Níor friteadh aon iarratas ó chuiditheoirí.',
    'volunteer_dashboard.volunteer_prefix'   => 'Cuiditheoir',
    'volunteer_dashboard.chat_label'         => 'Comhrá:',
    'volunteer_dashboard.notes_label'        => 'Nótaí:',
    'volunteer_dashboard.no_notes'           => 'Níl aon nóta fós',
    'volunteer_dashboard.chat_notes_btn'     => 'Comhrá & Nótaí',
    'volunteer_dashboard.accept_title'       => 'Glac isteach i gcóras cuireadh úsáideoirí',
    'volunteer_dashboard.accept_invite_btn'  => 'Glac & Cuir cuireadh',
    'volunteer_dashboard.delete_confirm'     => 'Scrios an taifead cuiditheora seo?',
    'volunteer_dashboard.modal_heading'      => 'Bainistigh interbhia & nótaí an iarratasóra',
    'volunteer_dashboard.modal_status_label'=> 'Stádas an iarratais:',
    'volunteer_dashboard.status_pending'     => 'Athbhreithniú ar feitheamh',
    'volunteer_dashboard.status_chat'        => 'Comhrá sceidealta',
    'volunteer_dashboard.status_accepted'    => 'Glactha',
    'volunteer_dashboard.status_rejected'    => 'Diúltaithe',
    'volunteer_dashboard.modal_date_label'   => 'Dáta & am an chomhrá / interbhia sceidealta:',
    'volunteer_dashboard.modal_notes_label'  => 'Nótaí interbhia / cruinnithe:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Taifead aiseolas ón gcomhrá anseo...',
    'volunteer_dashboard.save_changes_btn'   => 'Sábháil na hathruithe',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Toirmiscití: Níl an t-amharc poiblí cumasaithe.',
    'api_search.error_unauthorized_table' => 'Rochtain neamhúdaraithe ar thábla.',
    'api_search.no_records'              => 'Níor friteadh aon taifead sa tábla seo.',
    'api_search.history_btn'             => 'Stair',
    'api_search.suggest_edit_btn'        => 'Mol eagar',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Ar ais chuig an mbaile poiblí',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ------------------------------------------------------------------
    'feedback.hp_label'              => 'Fág bán',
    'feedback.first_name_label'      => 'Ainm:',
    'feedback.surname_label'         => 'Sloinne:',
    'feedback.email_label'           => 'Seoladh ríomhphoist:',
    'feedback.subject_label'         => 'Ábhar / Teideal an fhiosrúcháin:',
    'feedback.required_title'        => 'Réimse éigeantach',
    'feedback.select_placeholder'    => '-- Roghnaigh --',
    'feedback.multi_select_hint'     => 'Coinnigh Ctrl nó Cmd síos chun roinnt a roghnú.',
    'feedback.submit_btn'            => 'Cuir an ticéad isteach',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Teip ar sheiceáil slándála: Sínithe cliant amhrasach.',
    'security_engine.err_access_denied'    => 'Teip ar sheiceáil slándála: Rochtain diúltaithe.',
    'security_engine.err_rate_limit'       => 'An iomarca cur isteach ón seoladh IP seo. Déan iarracht arís níos déanaí le do thoil.',
    'security_engine.err_excessive_links'  => 'Diúltaíodh don chur isteach de bharr an iomarca nasc a braitheadh.',
    'security_engine.err_complete_captcha' => 'Comhlánaigh an dúshlán slándála CAPTCHA le do thoil.',
    'security_engine.err_captcha_failed'   => 'Teip ar fhíorú CAPTCHA. Déan iarracht arís le do thoil.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Suiteáil críochnaithe',
    'install.complete_heading'           => 'Suiteáil críochnaithe',
    'install.complete_desc'              => 'Tá an suíomh seo cumraithe cheana féin. Tá an suiteálaí faoi ghlas ionas nach féidir é a rith arís de thaisme.',
    'install.login_link'                 => 'Logáil isteach',
    'install.home_link'                  => 'Téigh chuig an suíomh',
    'install.delete_folder_hint'         => 'Is féidir leat an fillteán <code>install</code> a scriosadh nó a athainmniú ar mhaithe le slándáil bhreise.',
    'install.msg_db_ready'               => 'Tá an bunachar sonraí réidh. Cruthaigh do chuntas riarthóra chun an tsuiteáil a chríochnú.',
    'install.err_config_load'            => 'Níorbh fhéidir an chumraíocht atá ann cheana a úsáid:',
    'install.err_write_permission'       => 'Ní féidir le PHP comhaid a chruthú san fhillteán tionscadail seo.',
    'install.detail_prefix'              => 'Mionsonra:',
    'install.err_db_required'            => 'Tá ainm an bhunachair sonraí agus ainm úsáideora an bhunachair sonraí éigeantach.',
    'install.err_db_not_empty'           => 'Níl an bunachar sonraí seo folamh. Úsáid bunachar sonraí nua folamh (nó scrios gach tábla) agus déan iarracht arís.',
    'install.msg_schema_imported'        => 'Bunachar sonraí nasctha agus scéimre iompórtáilte. Cruthaigh do chuntas riarthóra.',
    'install.err_complete_db_first'      => 'Críochnaigh céim an bhunachair sonraí ar dtús.',
    'install.err_admin_required'         => 'Tá gach réimse riarthóra éigeantach.',
    'install.err_invalid_email'          => 'Seoladh ríomhphoist neamhbhailí.',
    'install.err_password_length'        => 'Caithfidh an pasfhocal a bheith 8 gcarachtar ar a laghad.',
    'install.err_passwords_match'        => 'Ní hionann na pasfhocail.',
    'install.err_admin_save_failed'      => 'Níor sábháladh an t-úsáideoir riarthóra. Seiceáil struchtúr an tábla úsáideoirí.',
    'install.msg_installation_complete' => 'Suiteáil críochnaithe.',
    'install.page_title'                 => 'Suiteáil — Eolaire Taifead Paróiste',
    'install.heading'                    => 'Suiteáil',
    'install.subheading'                 => 'Socruithe don chéad uair <strong>don fhillteán feidhmchláir seo amháin</strong>. Úsáid bunachar sonraí MySQL folamh.',
    'install.done_heading'               => 'Déanta',
    'install.done_message'               => 'Suiteáil críochnaithe. Tá an suiteálaí faoi ghlas anois.',
    'install.admin_heading'              => 'Cuntas riarthóra an suímh',
    'install.admin_subheading'           => 'Is é seo an logáil isteach do <strong>an suíomh Gréasáin seo</strong> (ní don bhunachar sonraí).',
    'install.admin_username_label'       => 'Ainm úsáideora an riarthóra',
    'install.admin_email_label'          => 'Ríomhphost an riarthóra',
    'install.admin_password_label'       => 'Pasfhocal an riarthóra (min. 8 gcarachtar)',
    'install.admin_confirm_password_label' => 'Deimhnigh pasfhocal an riarthóra',
    'install.finish_btn'                 => 'Críochnaigh an tsuiteáil',
    'install.db_heading'                 => 'Nasc an bhunachair sonraí',
    'install.db_hint'                    => 'Úsáid na mionsonraí MySQL ó do <strong>phainéal rialuithe óstála</strong>. Ní hé seo logáil isteach riarthóir an suímh Ghréasáin (tiocfaidh sé sin ina dhiaidh sin).',
    'install.db_host_label'              => 'Óstach an bhunachair sonraí',
    'install.db_name_label'              => 'Ainm an bhunachair sonraí',
    'install.db_user_label'              => 'Ainm úsáideora an bhunachair sonraí',
    'install.db_pass_label'              => 'Pasfhocal an bhunachair sonraí',
    'install.db_submit_btn'              => 'Cruthaigh táblaí &amp; lean ort',
    'install.req_heading'                => '1. Riachtanais',
    'install.req_php'                    => 'PHP 8.0+ (aimsitethe %s)',
    'install.req_pdo'                    => 'Breiseán PDO MySQL',
    'install.req_logs'                   => 'Fillteán logála inúsáidte (nó fillteán an tionscadail)',
    'install.req_probe'                  => 'Is féidir le comhaid a chruthú san fhillteán tionscadail seo',
    'install.continue_btn'               => 'Lean ort',
    'install.req_fail_msg'               => 'Ceartaigh na seiceálacha a thektear agus ansin athlódáil an leathanach seo.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Amharc an Chláir Scóir',
    'leaderboard.heading'         => 'Clár Scóir Rannpháirtíochta Pobail',
    'leaderboard.subheading'      => 'Aithint d’iarrachtaí chomhaltaí ár bpobail a cuidíonn le taifid bhunachair sonraí a chur le chéile, a thrasscríobh agus/nó a bhainistiú.',
    'leaderboard.th_rank'         => 'Rang',
    'leaderboard.th_contributor'  => 'Rannpháirtí',
    'leaderboard.th_role'         => 'Ról',
    'leaderboard.th_score'        => 'Scór',
    'leaderboard.no_users'        => 'Níor friteadh aon úsáideoir gníomhach ar an gClár Scóir fós.',
    'leaderboard.medal_gold'      => 'Bonn óir',
    'leaderboard.medal_silver'    => 'Bonn airgid',
    'leaderboard.medal_bronze'    => 'Bonn cré-umha',
    'leaderboard.medal_ribbon'    => 'Ribín duaise Rang 4',
    'leaderboard.medal_rosette'   => 'Rósáid Rang 5',
    'leaderboard.medal_trophy'    => 'Corn Rang 6',
    'leaderboard.medal_star'      => 'Réalta Rang 7',
    'leaderboard.medal_military'  => 'Bonn míleata Rang 8',
    'leaderboard.medal_glowing'   => 'Réalta lonr indexPath Rang 9',
    'leaderboard.medal_crown'     => 'Coróin Rang 10',
    'leaderboard.you_badge'       => '(Tú)',
    'leaderboard.default_role'    => 'Úsáideoir',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Taifid pharóiste tiomsaithe ó foinsí stairiúla san fhearann poiblí.',
    'footer.software_notice'  => 'Ardán bogearraí foinse oscailte faoi cheadúnas MIT.',
    'footer.rights_reserved'  => 'Gach ceart ar cosaint.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Bunachar Sonraí Taifead Paróiste',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Dún an fógra',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Gan taifead sonraithe.',
    'record_history.exit_not_found'        => 'Taifead gan aimsiú.',
    'record_history.heading_prefix'        => 'Stair & Cosán Iniúchta: Taifead',
    'record_history.return_btn'            => 'Ar ais',
    'record_history.directory_table_label'=> 'Tábla an Eolaire:',
    'record_history.subheading_lifecycle' => 'Taispeánann sé timpeallacht shóisialta na hathruithe, na moltaí agus na n-údair a bhaineann go díreach leis an taifead seo.',
    'record_history.snapshot_heading'      => 'Grianghraf sciobtha de luachanna beo reatha',
    'record_history.empty_value'           => '[Folamh]',
    'record_history.timeline_heading'      => 'Líne ama timpeallachta & ghníomhaíochta',
    'record_history.no_history'            => 'Níl aon imeacht iniúchta stairiúil taifeadta go sainiúil don taifead seo fós.',
    'record_history.purge_confirm'         => 'Scrios an iontráil logála iniúchta shainiúil seo?',
    'record_history.purge_btn'             => 'Glan an logáil',
    'record_history.actor_label'           => 'Gníomhaire:',
    'record_history.system_guest'          => 'Córas / Aoi',
    'record_history.target_column'         => 'An cholún spriocdhírithe:',
    'record_history.proposed_value'        => 'An luach molta:',
    'record_history.reasoning_evidence'    => 'Údar / Fianaise:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Nuashonraíodh an bunachar sonraí go rathúil! Cuireadh %d imimirc i bhfeidhm.',
    'update_database.msg_uptodate'     => 'Tá an bunachar sonraí cothrom le dáta cheana féin.',
    'update_database.err_failed'       => 'Teip ar an imimirc:',
    'update_database.page_title'       => 'Nuashonrú córais de dhíth — Eolaire Taifead Paróiste',
    'update_database.heading'          => '⚠️ Nuashonrú córais de dhíth',
    'update_database.subheading'       => 'Tá struchtúr bhunachar sonraí an fheidhmchláir as dáta agus éilíonn sé nuashonrú scéimre sula féidir gnáthoibriú a atosú.',
    'update_database.current_version'  => 'Leagan reatha an scéimre:',
    'update_database.latest_version'   => 'An leagan is déanaí ar fáil:',
    'update_database.proceed_login'    => 'Lean ar aghaidh chuig an logáil isteach',
    'update_database.confirm_prompt'   => 'An bhfuil cúltaca de do bhunachar sonraí déanta agat? Cliceáil OK chun nuashonruithe scéimre ar feitheamh a chur i bhfeidhm.',
    'update_database.update_btn'       => 'Nuashonraigh an bunachar sonraí anois',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Dintiúir neamhbhailí nó rochtain ar an gcuntas srianta.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'Ní féidir an réimse éigeantach \'%s\' a fhágáil bán.',
    'save_data_entry.audit_created_prefix' => 'Cruthaíodh taifead sa tábla ID %d.',
    'save_data_entry.msg_success'          => 'Cuireadh an taifead leis go rathúil!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Braitheadh turscar. Diúltaíodh don chur isteach.',
    'save_public_suggestion.err_field_required' => 'Tá an réimse seo éigeantach agus ní féidir é a chur isteach ina bhán.',
    'save_public_suggestion.msg_success'        => 'Cuireadh do mholadh eagar isteach go rathúil agus seoladh chuig an scuaine measúnaithe é lena athbhreithniú. Go raibh maith agat!',
    'save_public_suggestion.err_failed_submit'  => 'Teip agus an moladh eagar á chur isteach. Déan iarracht arís le do thoil.',
    'save_public_suggestion.err_invalid_column' => 'Colún neamhbhailí sonraithe.',
    'save_public_suggestion.err_invalid_params' => 'Paraiméadair cur isteach taifid neamhbhailí.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'YYYY-MM-DD (nó bliain pháirteach)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/YYYY (nó bliain pháirteach)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/YYYY (nó bliain pháirteach)',
    'data_entry.no_tables_heading'    => '⚠️ Níor friteadh aon tábla bunachair sonraí',
    'data_entry.no_tables_desc'       => 'Níl aon tábla bunachair sonraí gníomhach cumraithe ag an gcóras le haghaidh iontráil sonraí faoi láthair.',
    'data_entry.admin_tables_prompt'  => 'Mar riarthóir, téigh go dtí an rogha roghchláir <strong>Bainistigh táblaí</strong> chun tábla a chruthú, agus cuir colún amháin ar a laghad leis sula n-iontrálann tú taifid.',
    'data_entry.go_manage_tables'     => 'Téigh go dtí Bainistigh táblaí',
    'data_entry.contact_admin_tables' => 'Déan teagmháil le riarthóir chun táblaí agus colúin bhunachair sonraí a chumrú.',
    'data_entry.no_cols_heading'      => '⚠️ Níl aon cholún cumraithe',
    'data_entry.no_cols_desc'         => 'Tá táblaí ann sa chóras, ach níor sainíodh aon cholún sonraí don tábla gníomhach.',
    'data_entry.admin_cols_prompt'    => 'Mar riarthóir, téigh go dtí an rogha roghchláir <strong>Bainistigh táblaí</strong> chun colún amháin ar a laghad a chur le do thábla.',
    'data_entry.contact_admin_cols'   => 'Déan teagmháil le riarthóir chun colúin a chumrú don tábla seo.',
    'data_entry.active_table_label'   => 'Tábla iontrála sonraí gníomhach:',
    'data_entry.add_entry_summary'    => '➕ Cuir iontráil sonraí nua leis (Cliceáil chun leathnú/laghdú)',
    'data_entry.bool_yes_true'        => 'Sea / Fíor',
    'data_entry.bool_no_false'        => 'Ní hea / Bréagach',
    'data_entry.bool_male'            => 'Fireann',
    'data_entry.bool_female'          => 'Baineann',
    'data_entry.bool_true'            => 'Fíor',
    'data_entry.bool_false'           => 'Bréagach',
    'data_entry.bool_tick'            => '✔ (Seicmharc)',
    'data_entry.bool_cross'           => '✘ (Cros)',
    'data_entry.date_title_hint'      => 'Glacann sé le dátaí iomlána nó páirteacha (m.sh. 1842 nó 1842-05)',
    'data_entry.enter_value_placeholder' => 'Cuir isteach luach...',
    'data_entry.submit_data_btn'      => 'Cuir na sonraí isteach',
    'data_entry.shortcuts_tip'        => '💡 Leideanna: Brúigh <strong>Ctrl + Enter</strong> chun cur isteach, nó <strong>Esc</strong> chun an réimse reatha a ghlanadh.',
    'data_entry.dup_heading'          => '⚠️ Rabhadh faoi dhúblach féideartha',
    'data_entry.dup_desc'             => 'Fuireamar iontrálacha comhoiriúnacha cheana féin sa chóras:',
    'data_entry.dup_item_format'      => 'ID an Taifid: %d — Luach: %s',
    'data_entry.dup_prompt'           => 'An mian leat leanúint ar aghaidh agus an iontráil dúblach seo a shábháil barainneach?',
    'data_entry.dup_confirm_btn'      => 'Sea, deimhnigh agus sábháil an dúblach',
    'data_entry.search_summary'       => '🔍 Cuardaigh & scag taifid atá ann cheana (Cliceáil chun leathnú/laghdú)',
    'data_entry.date_to_label'        => 'go dtí',
    'data_entry.filter_all_option'    => '-- Gach --',
    'data_entry.filter_placeholder'   => 'Scag...',
    'data_entry.apply_filters_btn'    => 'Cuir scagairí cuardaigh i bhfeidhm',
    'data_entry.reset_filter_btn'     => 'Athshocraigh an scagaire',
    'data_entry.csv_entire_btn'       => 'Íoslódáil an CSV iomlán',
    'data_entry.json_entire_btn'      => 'Íoslódáil an JSON iomlán',
    'data_entry.copy_entire_btn'      => 'Cóipeáil an tábla iomlán',
    'data_entry.csv_filtered_btn'     => 'Íoslódáil an CSV scagtha',
    'data_entry.json_filtered_btn'     => 'Íoslódáil an JSON scagtha',
    'data_entry.copy_filtered_btn'    => 'Cóipeáil an tábla scagtha',
    'data_entry.clipboard_alert'      => 'Cóipeáladh sonraí an tábla chuig an ngearrthaisce! Is féidir leat iad a ghreamú go díreach in Excel nó Google Sheets.',
    'data_entry.existing_records_heading' => 'Tábla de thafid atá ann cheana',
    'data_entry.th_added_by'          => 'Curtha leis ag',
    'data_entry.th_date_created'      => 'Dáta cruthaithe',
    'data_entry.no_records'           => 'Níor friteadh aon taifead.',
    'data_entry.na_value'             => 'N/A',
    'data_entry.page_label'           => 'Leathanach:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Athshlánú pasfhocail',
    'forgot_password.heading'         => 'Athshocraigh do phasfhocal',
    'forgot_password.subheading'      => 'Cuir isteach seoladh ríomhphoist do chuntais thíos, agus seolfaimid nasc slán chugat chun do phasfhocal a athshocrú.',
    'forgot_password.email_label'     => 'Seoladh ríomhphoist:',
    'forgot_password.submit_btn'      => 'Seol an nasc athshocraithe',
    'forgot_password.back_login_link' => 'Ar ais go dtí an logáil isteach',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Logáil isteach úsáideora',
    'login.heading'              => 'Logáil isteach úsáideora',
    'login.username_label'       => 'Ainm úsáideora nó ríomhphost:',
    'login.password_label'       => 'Pasfhocal:',
    'login.submit_btn'           => 'Logáil isteach',
    'login.forgot_password_link' => 'Ar dearmad ar do phasfhocal?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Fáilte - Treoraí Socraithe Cuntais',
    'onboarding.heading'           => 'Fáilte go dtí an foireann!',
    'onboarding.subheading'        => 'Sula dtosaíonn tú, tóg nóiméad le do thoil chun do roghanna réigiúnacha taispeána agus príobháideachta a chumrú. Is féidir leat iad a nuashonrú tráth ar bith i do phróifíl.',
    'onboarding.timezone_label'    => 'Crios ama / Réigiún:',
    'onboarding.date_format_label' => 'Formáid taispeána dáta:',
    'onboarding.time_format_label' => 'Formáid an chloig (Taispeáint ama):',
    'onboarding.time_24'          => '24 uair an chloig (m.sh. 16:07)',
    'onboarding.time_12'          => '12 uair an chloig AM/PM (m.sh. 04:07 PM)',
    'onboarding.time_none'        => 'Dáta amháin (Folaigh an t-am go hiomlán)',
    'onboarding.attribution_label' => 'Rogha taispeána ar an gclár scóir & leithdháileadh:',
    'onboarding.attribution_desc1' => 'Rialaíonn sé conas a thaispeántar d’ainm ar an gclár scóir poiblí agus i loganna taifid.',
    'onboarding.attr_anon_title'   => 'Gan ainm:',
    'onboarding.attr_anon_text'    => 'Taispeánann sé céadlitreacha & uimhir randamach do gach duine.',
    'onboarding.attr_public_title' => 'Poiblí:',
    'onboarding.attr_public_text'  => 'Taispeánann sé d’ainm iomlán do gach duine.',
    'onboarding.attr_vol_title'   => 'Cuiditheoirí amháin:',
    'onboarding.attr_vol_text'     => 'Taispeánann sé céadlitreacha don phobal, ach d’ainm iomlán do chuiditheoirí, measúnóirí agus riarthóirí atá logáilte isteach.',
    'onboarding.attr_opt_anon'     => 'Gan ainm (Céadlitreacha & uimhir randamach)',
    'onboarding.attr_opt_public'   => 'Poiblí (Taispeáin ainm iomlán)',
    'onboarding.attr_opt_vol'      => 'Cuiditheoirí amháin',
    'onboarding.submit_btn'        => 'Sábháil roghanna & lean ort',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Bainistíocht phróifíl úsáideora',
    'profile.heading'              => 'Próifíl úsáideora & Slándáil',
    'profile.personal_details_heading' => 'Mionsonraí pearsanta',
    'profile.language_label'       => 'An teanga is fearr leat:',
    'profile.lang_site_default'    => 'Réamhshocrú an suímh',
    'profile.update_details_btn'   => 'Nuashonraigh mionsonraí pearsanta',
    'profile.email_heading'        => 'Seoladh ríomhphoist',
    'profile.current_email_label'  => 'Ríomhphost reatha:',
    'profile.email_verified'       => '(Fíoraithe)',
    'profile.email_unverified'     => '(Neamhfhíoraithe - Seiceáil do bhosca isteach)',
    'profile.change_email_label'   => 'Athraigh an seoladh ríomhphoist:',
    'profile.aria_new_email'       => 'Seoladh ríomhphoist nua',
    'profile.update_email_btn'     => 'Nuashonraigh an ríomhphost & fíoraigh',
    'profile.password_heading'     => 'Athraigh an pasfhocal',
    'profile.current_password_label' => 'Pasfhocal reatha:',
    'profile.new_password_label'   => 'Pasfhocal nua (min. 8 gcarachtar):',
    'profile.confirm_password_label' => 'Deimhnigh an pasfhocal nua:',
    'profile.show_passwords_label' => 'Taispeáin pasfhocail i téacs soiléir',
    'profile.update_password_btn'  => 'Nuashonraigh an pasfhocal',
    'profile.tfa_heading'          => 'Fíordheimhniú Dé-Fhachtóir (2FA)',
    'profile.tfa_status_label'     => 'Stádas:',
    'profile.tfa_enabled'          => 'Cumasaithe',
    'profile.tfa_disabled'         => 'Díchumasaithe',
    'profile.setup_tfa_btn'        => 'Socraigh Google Authenticator',
    'profile.tfa_active_desc'      => 'Cosnaíonn 2FA logáil isteach do chuntais go gníomhach.',
    'profile.backup_codes_heading' => 'Do chódanna cúltaca nua',
    'profile.download_codes_btn'   => 'Íoslódáil códanna nua mar .txt',
    'profile.generate_codes_confirm' => 'An bhfuil tú cinnte? Cuirfidh sé seo aon chóid cúltaca atá ann cheana ar neamhní.',
    'profile.generate_codes_btn'   => 'Gin códanna cúltaca nua',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Clárú úsáideora',
    'register.heading'        => 'Cláraigh cuntas nua',
    'register.username_label' => 'Ainm úsáideora:',
    'register.submit_btn'     => 'Cláraigh',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Tóin socruithe neamhbhailí nó ar iarraidh.',
    'set_password.exit_expired_token'        => 'Tá an nasc socruithe pasfhocail seo neamhbhailí nó tá sé in éag.',
    'set_password.proceed_login_btn'         => 'Lean ar aghaidh chuig an logáil isteach',
    'set_password.aria_region'               => 'Socraigh an pasfhocal',
    'set_password.heading_format'            => 'Socraigh do phasfhocal le haghaidh %s',
    'set_password.subheading_format'         => 'Fáilte go dtí do chuntas nua, %s! Roghnaigh do phasfhocal thíos le do thoil.',
    'set_password.new_password_label'        => 'Pasfhocal nua (8 gcarachtar ar a laghad):',
    'set_password.confirm_password_label'    => 'Deimhnigh an pasfhocal:',
    'set_password.show_password_label'       => 'Taispeáin an pasfhocal',
    'set_password.save_password_btn'         => 'Sábháil an pasfhocal',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => 'Treoraí socruithe 2FA',
    'setup_2fa.heading'          => 'Socraigh Google Authenticator',
    'setup_2fa.subheading'       => 'Scanáil an cód QR thíos le d’aip fhíordheimhnithe.',
    'setup_2fa.qr_alt'           => 'Cód QR le haghaidh socruithe 2FA',
    'setup_2fa.manual_prompt'    => 'Nó cuir isteach an eochair rúnda seo de láimh:',
    'setup_2fa.backup_heading'   => 'Códanna aisghabhála cúltaca éigeandála',
    'setup_2fa.backup_desc'      => 'Coinnigh na códanna cúltaca seo in áit shábháilte. Is féidir gach cód a úsáid <strong>uair amháin</strong> má chailleann tú rochtain ar d’aip fhíordheimhnithe:',
    'setup_2fa.download_btn'     => 'Íoslódáil na códanna mar .txt',
    'setup_2fa.code_label'       => 'Cuir isteach an cód 6 dhigit ón aip chun deimhniú & gníomhachtú:',
    'setup_2fa.aria_code_input'  => 'Cód fíordheimhnithe 6 dhigit',
    'setup_2fa.submit_btn'       => 'Fíoraigh & cumasaigh 2FA',
    'setup_2fa.cancel_link'      => 'Cealaigh & fill ar an phróifíl',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Mol eagar',
    'suggest_edit.heading_prefix'       => 'Mol eagar don taifead',
    'suggest_edit.return_btn'           => 'Fill ar an taifead',
    'suggest_edit.success_msg_suffix'   => 'Ná bíodh drogall ort eagar eile a chur isteach thíos, nó úsáid an nasc fáis thuas nuair a bheidh tú críochnaithe.',
    'suggest_edit.current_values_heading' => 'Luachanna reatha:',
    'suggest_edit.empty_label'          => '(folamh)',
    'suggest_edit.submit_heading'       => 'Cuir isteach luach molta nua & fianaise',
    'suggest_edit.confirm_prompt'       => 'An bhfuil tú cinnte go bhfuil tú réidh an moladh eagar seo a chur isteach le haghaidh athbhreithniú an riarthóra?',
    'suggest_edit.select_column_label'  => 'Roghnaigh an colún le heagrú:',
    'suggest_edit.reasoning_label'      => 'Fianaise / Údar / Nótaí foinse:',
    'suggest_edit.reasoning_placeholder'=> 'Soláthair comhthéacs, luachana foinse nó údar don athrú seo...',
    'suggest_edit.submit_btn'           => 'Cuir an moladh isteach lena athbhreithniú',
    'suggest_edit.proposed_value_label' => 'Luach nua molta:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => 'Fíorú 2FA',
    'verify_2fa.heading'         => 'Fíordheimhniú Dé-Fhachtóir',
    'verify_2fa.subheading'      => 'Cuir isteach an cód 6 dhigit ó d’aip fhíordheimhnithe nó úsáid cód aisghabhála cúltaca éigeandála.',
    'verify_2fa.code_label'      => 'Cód fíoraithe / Cód cúltaca:',
    'verify_2fa.aria_code_input' => 'Cuir isteach an cód fíordheimhnithe nó cúltaca',
    'verify_2fa.submit_btn'      => 'Fíoraigh & Logáil isteach',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Gan tóin fíoraithe curtha ar fáil.',
    'verify_email.err_invalid_token'    => 'Tóin fíoraithe neamhbhailí.',
    'verify_email.msg_already_verified' => 'Tá do ríomhphost fíoraithe cheana féin. Is féidir leat logáil isteach.',
    'verify_email.err_expired_token'    => 'Tá an nasc fíoraithe seo in éag (sáraíodh an tréimhse 24 uair an chloig). Cláraigh arís le do thoil nó iarr nasc nua.',
    'verify_email.msg_success'          => 'Ríomhphost fíoraithe go rathúil! Tá do chuntas gníomhach anois. Is féidir leat leanúint ar aghaidh chuig an logáil isteach.',
    'verify_email.err_update_failed'    => 'Tharla earráid agus do ríomhphost á fhíorú. Déan iarracht arís le do thoil.',
    'verify_email.aria_region'          => 'Stádas fíoraithe ríomhphoist',
    'verify_email.heading'              => 'Stádas fíoraithe ríomhphoist',
    'verify_email.login_btn'            => 'Cliceáil anseo le logáil isteach',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Foirm cuiditheora',
    'volunteer.honeypot_label'       => 'Fág an réimse seo bán:',
    'volunteer.required_field_title'=> 'Réimse éigeantach',
    'volunteer.multi_select_hint'    => 'Coinnigh Ctrl nó Cmd síos chun roinnt a roghnú.',
    'volunteer.submit_btn'           => 'Cuir isteach suim sa chúnamh',
];
