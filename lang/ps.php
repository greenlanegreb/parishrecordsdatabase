<?php
// lang/ps.php - Pashto (پښتو)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'ننوتل',
    'nav.logout'                 => 'وتل',
    'nav.feedback'               => 'نظرونه',
    'nav.volunteer'              => 'رضاکار کېدل',
    'nav.leaderboard'            => 'مخکښان',
    'nav.search'                 => 'پلټنه',
    'nav.settings'               => 'د سیستم تنظیمات',
    'nav.high_contrast'          => 'لوړ توپیر',
    'nav.low_contrast'           => 'ټیټ توپیر',
    'nav.welcome'                => 'ښه راغلاست،',
    'nav.data_entry'             => 'د ډاټا داخلول',
    'nav.moderation'             => 'منظوري',
    'nav.invite_user'            => 'کاروونکی بلل',
    'nav.manage_users'           => 'د کاروونکو مدیریت',
    'nav.manage_tables'          => 'د جدولونو مدیریت',
    'nav.volunteer_dashboard'    => 'د رضاکارانو ډشبورډ',
    'nav.feedback_dashboard'     => 'د نظرونو ډشبورډ',
    'nav.leaderboard_score'      => 'نمرې',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'د څو ستنو ګډه پلټنه',
    'search.reset'               => 'پلټنه بیا تنظیمول',
    'search.export_csv'          => 'فلټر شوي پایلې د CSV په بڼه راښکته کول',
    'search.no_records'          => 'پدې جدول کې هېڅ ریکارډ ونه موندل شو.',
    'search.load_error'          => 'پایلې بار نش شوې. مهرباني وکړئ بیا هڅه وکړئ.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'لېږل',
    'btn.cancel'                 => 'لغوه کول',
    'btn.save'                   => 'خوندیتوب',
    'btn.delete'                 => 'ړنګول',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'مننه! ستاسو نظر په بریا سره ولسول شو.',
    'feedback.error_all_fields'   => 'ټول برخې باید ډکې شي.',
    'feedback.error_invalid_email'=> 'مهرباني وکړئ یو سم برېښنالیک ولیکئ.',
    'feedback.error_save_failed'  => 'د نظر په خوندي کولو کې تېروتنه وشوه، مهرباني وکړئ بیا هڅه وکړئ.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'هېڅ ډیټابیس جدول ونه موندل شو',
    'index.no_tables_desc'             => 'اوس مهال په سیستم کې هېڅ فعال ډیټابیس جدول نه دی تنظیم شوی.',
    'index.admin_create_table_guide'   => 'د مدیر په توګه، مهرباني وکړئ د ریکارډونو ښودلو یا داخلولو لپاره <strong>د جدولونو مدیریت</strong> ته لاړ شئ او یو جدول جوړ کړئ او لږترلږه یوه ستون اضافه کړئ.',
    'index.go_to_manage_tables'        => 'د جدولونو مدیریت ته تلل',
    'index.contact_admin_tables'       => 'مهرباني وکړئ د جدولونو او ستنو د تنظیمولو لپاره له مدیر سره اړیکه ونیسئ.',
    'index.guest_login_tables_guide'   => 'مهرباني وکړئ <a href=":login_link">ننوتل</a> یا د جدولونو او ستنو د تنظیمولو لپاره له مدیر سره اړیکه ونیسئ.',
    'index.no_columns_heading'         => 'هېڅ ستون نه ده تنظیم شوې',
    'index.no_columns_desc'            => 'په سیستم کې جدولونه شتون لري، مګر د اوسني فعال جدول لپاره هېڅ ستون نه ده تعریف شوې.',
    'index.admin_add_columns_guide'    => 'د مدیر په توګه، مهرباني وکړئ <strong>د جدولونو مدیریت</strong> ته لاړ شئ او خپل جدول ته لږترلږه یوه ستون اضافه کړئ.',
    'index.contact_admin_columns'      => 'مهرباني وکړئ د دې جدول د ستنو د تنظیمولو لپاره له مدیر سره اړیکه ونیسئ.',
    'index.select_directory_database'  => 'د لارښود ډیټابیس وټاکئ:',
    'index.opt_yes_true'               => 'هو / رښتیا',
    'index.opt_no_false'               => 'نه / دروغ',
    'index.opt_male'                   => 'نارینه',
    'index.opt_female'                 => 'ښځینه',
    'index.opt_true'                   => 'رښتیا',
    'index.opt_false'                  => 'دروغ',
    'index.opt_tick'                   => '✔ (نښه)',
    'index.opt_cross'                  => '✘ (تېروتنه)',
    'index.option_all'                 => '-- ټول --',
    'index.date_to_label'              => 'تر',
    'index.search_placeholder'         => 'پلټنه...',
    'index.download_entire_csv'        => 'بشپړ CSV راښکته کول',
    'index.download_entire_json'       => 'بشپړ JSON راښکته کول',
    'index.copy_entire_table'          => 'بشپړ جدول کاپي کول',
    'index.download_filtered_csv'      => 'فلټر شوی CSV راښکته کول',
    'index.download_filtered_json'     => 'فلټر شوی JSON راښکته کول',
    'index.copy_filtered_table'        => 'فلټر شوی جدول کاپي کول',
    'index.th_record_id'               => 'د ریکارډ ID',
    'index.th_created_by'              => 'جوړونکی',
    'index.th_date_added'              => 'د زیاتولو نېټه',
    'index.th_actions'                 => 'کړنې',
    'index.modal_heading'              => 'د ریکارډ د سمون وړاندیز',
    'index.modal_desc'                 => 'د دې ریکارډ لپاره اصلاح یا بدیل معلومات وړاندې کړئ. زموږ د بیاکتنې ټیم به پرې غور وکړي.',
    'index.modal_target_column'        => 'پام وړ ستون:',
    'index.modal_proposed_value'       => 'وړاندیز شوی ارزښت / اصلاح:',
    'index.modal_input_placeholder'    => 'تازه شوي معلومات ولیکئ...',
    'index.modal_submit_btn'           => 'وړاندیز لېږل',
    'index.clipboard_success'          => 'د جدول ډاټا کلېپ بورډ ته کاپي شوه! تاسو کولی شئ دا په مستقیم ډول په Excel یا Google Sheets کې ولګوئ.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'د نوي کاروونکي د بلنې فورمه',
    'create_user.subheading'           => 'دا به د 24 ساعتونو لپاره یو خوندي لینک جوړ کړي او په مستقیم ډول به یې کاروونکي ته په برېښنالیک کې واستوي.',
    'create_user.first_name'           => 'نوم:',
    'create_user.surname'              => 'تخلص:',
    'create_user.username_label'       => 'کارن نوم (اختياري):',
    'create_user.username_placeholder' => 'خالي پریږدئ ترڅو په خپله جوړ شي',
    'create_user.username_help'        => 'که خالي پاتې شي، سیستم به د نوم پر بنسټ په خپله یو ځانګړی کارن نوم جوړ کړي.',
    'create_user.email_label'          => 'برېښنالیک پته:',
    'create_user.role_label'           => 'د کاروونکي رول:',
    'create_user.submit_btn'           => 'کاروونکی جوړول او بلنه لېږل',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'د ملاتړ ټکټونو او نظرونو ډشبورډ',
    'feedback_dash.subheading'           => 'د عامه ملاتړ غوښتنې اداره کول، د حالتونو تازه کول، او په خبرو اترو کې برخه اخیستل.',
    'feedback_dash.manage_emails'        => 'د برېښنالیک کينډۍ مدیریت',
    'feedback_dash.manage_schema'        => 'د ټکټ فورمیټ جوړښت مدیریت',
    'feedback_dash.th_ticket_date'       => 'د ټکټ ID / نېټه',
    'feedback_dash.th_submitter'         => 'لېږونکی',
    'feedback_dash.th_subject_info'      => 'موضوع / بنسټیز معلومات',
    'feedback_dash.th_status'            => 'حالت',
    'feedback_dash.no_tickets'           => 'هېڅ نظر یا ټکټ ونه موندل شو.',
    'feedback_dash.anonymous'            => 'نامعلوم',
    'feedback_dash.default_subject'      => 'عمومي پوښتنه',
    'feedback_dash.open_ticket_btn'      => 'ټکټ او خبرې اترې پرانیستل',
    'feedback_dash.delete_confirm'       => 'آیا غواړئ دا د ملاتړ ټکټ او اړوند ټول ځوابونه ړنګ کړئ؟',
    'feedback_dash.msg_deleted'          => 'ټکټ #:id په بریا سره ړنګ شو.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'د ملاتړ ټکټونو د برېښنالیک کينډۍ',
    'feedback_emails.subheading'         => 'د ټکټ پروسې پر مهال په اتوماتيک ډول لېږل کېدونکي برېښنالیکونه تنظیم کړئ. د متحرک ځایونو لپاره له تار بندونو کار واخلئ.',
    'feedback_emails.back_to_dashboard' => 'د نظرونو ډشبورډ ته بېرته ستنېدل',
    'feedback_emails.email_subject'      => 'د برېښنالیک موضوع:',
    'feedback_emails.email_body'         => 'د برېښنالیک متن کينډۍ:',
    'feedback_emails.save_template_btn' => 'کينډۍ خوندي کول',
    'feedback_emails.placeholders_heading' => 'شبه ځایونه (Placeholders)',
    'feedback_emails.placeholders_desc' => 'تاسو کولی شئ دا ټاګونه په موضوع یا متن کې هرچیرې وکاروئ:',
    'feedback_emails.fixed_tags'         => 'اساسي ثابت ټاګونه:',
    'feedback_emails.custom_tags'        => 'د جوړښت ځانګړي ټاګونه:',
    'feedback_emails.custom_tags_desc'   => 'د ټکټ فورمیټ له برخو څخه په اتوماتيک ډول جوړیږي:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'د نظرونو د فورمې جوړښت مدیریت',
    'feedback_schema.subheading'             => 'ځانګړي برخې، د ډاټا ډولونه، د تورو حد، فرعي ډولونه، اختیارونه او ښودلو تنظیمات تنظیم کړئ.',
    'feedback_schema.settings_summary'       => 'د فورمې سرلیک او د معافیت متن تنظیم کړئ',
    'feedback_schema.form_title_label'       => 'د فورمې سرلیک:',
    'feedback_schema.form_intro_label'       => 'پیرېدنه / د پرانیستې متن:',
    'feedback_schema.save_settings_btn'      => 'د فورمې تنظیمات خوندي کول',
    'feedback_schema.edit_field_title'       => 'د ټکټ برخه سمول:',
    'feedback_schema.add_field_title'        => '+ د ټکټ د فورمې نوې برخه اضافه کول',
    'feedback_schema.field_name_label'       => 'نښه / د برخې نوم:',
    'feedback_schema.data_type_label'        => 'د ډاټا ډول:',
    'feedback_schema.type_varchar'           => 'VARCHAR (لنډ متن)',
    'feedback_schema.type_text'              => 'TEXT (اوږد متن / پیغام)',
    'feedback_schema.type_int'               => 'INT (بشپړ عدد)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (هو/نه نښه)',
    'feedback_schema.type_date'              => 'DATE (کالندري نېټه)',
    'feedback_schema.subtype_label'          => 'د برخې فرعي ډول / سټایل:',
    'feedback_schema.subtype_standard'       => '-- معیاري --',
    'feedback_schema.subtype_standard_lower'=> 'معیاري',
    'feedback_schema.options_label'          => 'اختيارونه (په کامه جلا شوي یا هر یو په یوه کرښه کې):',
    'feedback_schema.options_help'           => 'اختيارونه په کامه یا نوي کرښه سره جلا کړئ.',
    'feedback_schema.allow_multiple'         => 'د څو اختیارونو د ټاکلو اجازه ورکول',
    'feedback_schema.boolean_format'         => 'د هو/نه د ښودلو بڼه:',
    'feedback_schema.max_length_label'       => 'تر ټولو اوږدوالی / د تورو حد (اختياري):',
    'feedback_schema.is_required_label'      => 'دا برخه د لېږونکي لپاره لازمي ګرځول',
    'feedback_schema.save_field_btn'         => 'د برخې بدلونونه خوندي کول',
    'feedback_schema.create_field_btn'       => 'د ټکټ برخه جوړول',
    'feedback_schema.sub_email'              => 'برېښنالیک',
    'feedback_schema.sub_url'                => 'وېب پاڼه (URL)',
    'feedback_schema.sub_select'             => 'راښکته کېدونکی مینو',
    'feedback_schema.sub_radio'              => 'د راډیو تڼیو ګروپ',
    'feedback_schema.sub_checkbox'           => 'د انتخاب باکس',
    'feedback_schema.sub_textarea'           => 'د څو کرښو متن ځای',
    'feedback_schema.sub_number'             => 'د شمېرې داخلول',
    'feedback_schema.existing_fields_heading'=> 'شته ټکټ برخې',
    'feedback_schema.th_move'                => 'خوځول',
    'feedback_schema.th_field_name'          => 'د برخې نوم',
    'feedback_schema.th_data_type'           => 'د ډاټا ډول',
    'feedback_schema.th_subtype'             => 'فرعي ډول',
    'feedback_schema.th_required'            => 'لازمی دی؟',
    'feedback_schema.th_max_length'          => 'لوړ اوږدوالی',
    'feedback_schema.th_created_by'          => 'جوړونکی',
    'feedback_schema.no_fields'              => 'تر اوسه هېڅ ځانګړې ټکټ برخه نه ده تعریف شوې.',
    'feedback_schema.system_user'            => 'سیستم',
    'feedback_schema.edit_btn'               => 'سمول',
    'feedback_schema.delete_confirm'         => 'آیا غواړئ دا برخه او ټول اړوند ارزښتونه ړنګ کړئ؟',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'د جدولونو او جوړښتونو مدیریت',
    'manage_tables.subheading'           => 'د غوښتنلیک متحرک جدولونه او د هغو ستنې په خوندي توګه جوړې، وپلټئ، بدلې یا ړنګې کړئ.',
    'manage_tables.switcher_label'       => 'فعال جدول وټاکئ:',
    'manage_tables.edit_metadata_btn'    => 'د جدول معلومات سمول',
    'manage_tables.delete_table_confirm'=> 'خبرداری: د دې جدول ړنګول به ټولې ستنې او خوندي شوي منځپانګې په بشپړ ډول له منځه یوسي. آیا تاسو په بشپړ ډول ډاډه یاست؟',
    'manage_tables.delete_table_btn'     => 'جدول ړنګول',
    'manage_tables.edit_table_summary'   => 'د جدول تعریف سمول:',
    'manage_tables.create_table_summary'=> '+ نوی متحرک جدول جوړول',
    'manage_tables.table_name_label'     => 'د جدول دوستانه نوم:',
    'manage_tables.table_desc_label'     => 'تشریح / موخه:',
    'manage_tables.save_table_btn'       => 'د جدول بدلونونه خوندي کول',
    'manage_tables.create_table_btn'     => 'د جدول جوړښت جوړول',
    'manage_tables.edit_col_summary'     => 'متحرک ستون سمول:',
    'manage_tables.add_col_summary_prefix' => '+ د دې لپاره نوې ستون اضافه کول:',
    'manage_tables.col_name_label'       => 'د ستنې نوم:',
    'manage_tables.type_text_long'       => 'TEXT (اوږد پارګراف)',
    'manage_tables.date_behavior_label' => 'د نېټې د لټون چلند:',
    'manage_tables.date_bhv_manual'      => 'په ډیټابیس کې نېټه (يوازې په لاسي ډول)',
    'manage_tables.date_bhv_admin'       => 'یوازې د مدیر نېټې',
    'manage_tables.date_bhv_all'         => 'ټولې نېټې (د مدیر په ګډون)',
    'manage_tables.req_toggle_label'     => 'دا ستون لازمي ګرځول (د ډاټا داخلول اړین دي)',
    'manage_tables.exclude_search_label'=> 'دا ستون د عامه پلټنې څخه وباسئ (index.php)',
    'manage_tables.create_col_btn'       => 'ستون جوړول',
    'manage_tables.existing_cols_heading_prefix' => 'شته ستنې:',
    'manage_tables.th_public_search'     => 'عامه پلټنه؟',
    'manage_tables.th_display_format'    => 'د ښودلو بڼه',
    'manage_tables.th_date_created'      => 'د جوړولو نېټه',
    'manage_tables.no_columns_found'     => 'د دې جدول لپاره تر اوسه هېڅ متحرک ستون نه ده تعریف شوې.',
    'manage_tables.status_hidden'        => 'پټ',
    'manage_tables.delete_col_confirm'   => 'خبرداری: د دې ستنې ړنګول به په هر ریکارډ کې اړوند حجرو ټول معلومات هم له منځه یوسي. آیا ډاډه یاست؟',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'د کاروونکو د خبرتیا برېښنالیکونو کينډۍ',
    'user_emails.subheading'             => 'هغه برېښنالیکونه تنظیم کړئ چې د کاروونکو د بللو یا د پټنوم د بیا تنظیمولو پر مهال لېږل کیږي.',
    'user_emails.select_template_label'=> 'د سمولو لپاره کينډۍ وټاکئ:',
    'user_emails.opt_invitation'         => 'د کاروونکي اکاونټ د بلنې کينډۍ',
    'user_emails.opt_reset'              => 'د پټنوم د بیا تنظیمولو / لاسرسي لینک کينډۍ',
    'currently_editing'                  => 'اوس مهال سمول:',
    'user_emails.desc_invitation'        => 'کله چې مدیر نوی کاروونکی جوړ کړي یا بلنه ورکړي، په اتوماتيک ډول لېږل کیږي.',
    'user_emails.desc_reset'             => 'کله چې د پټنوم د بیا تنظیمولو غوښتنه وشي، لېږل کیږي.',
    'user_emails.email_body_label'       => 'د برېښنالیک متن:',
    'user_emails.back_to_creation'       => 'د کاروونکي جوړولو ته بېرته ستنېدل',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'د رضاکارانو د برېښنالیک کينډۍ او محرکونه',
    'volunteer_emails.subheading'        => 'هغه اتوماتیک برېښنالیکونه تنظیم کړئ چې رضاکارانو ته په بېلابېلو پړاوونو کې لېږل کیږي.',
    'volunteer_emails.back_to_dashboard'=> 'د رضاکارانو د غوښتنو ډشبورډ ته بېرته ستنېدل',
    'volunteer_emails.custom_tags_desc'  => 'د فورمې د جوړوونکي لخوا په اتوماتيک ډول جوړیږي:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'د رضاکارانو د فورمې د جوړښت مدیریت',
    'volunteer_schema.subheading'        => 'ځانګړي برخې، د ډاټا ډولونه، فرعي ډولونه، اختیارونه او د فورمې عام تنظیمات تنظیم کړئ.',
    'volunteer_schema.back_to_dashboard'=> 'د رضاکارانو د غوښتنو ډشبورډ ته بېرته ستنېدل',
    'volunteer_schema.settings_summary'  => 'د فورمې سرلیک او د معافیت متن تنظیم کړئ',
    'volunteer_schema.edit_field_title'  => 'برخه سمول:',
    'volunteer_schema.add_field_title'   => '+ د رضاکارانو فورمې ته نوې برخه اضافه کول',
    'volunteer_schema.create_field_btn'  => 'برخه جوړول',
    'volunteer_schema.existing_fields_heading' => 'د رضاکارانو د فورمې شته برخې',
    'volunteer_schema.no_fields'         => 'تر اوسه د رضاکارانو هېڅ ځانګړې برخه نه ده تعریف شوې.',
    'volunteer_schema.delete_confirm'    => 'آیا غواړئ دا برخه او ټول اړوند ځوابونه ړنګ کړئ؟',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'د پاتې وړاندیزونو بیاکتنه',
    'moderate.subheading'                => 'د کاروونکو وړاندیز شوي بدلونونه د خپلو منل شویو جدولونو له فعالو ریکارډونو سره پرتله کړئ. وړاندیزونه ومنئ، بدل کړئ یا رد کړئ.',
    'moderate.shortcut_label'            => 'د کیبورډ شارټ کټ لارښوونه:',
    'moderate.shortcut_desc'             => 'د چټک تایید لپاره Ctrl + Enter کېکاږئ، یا د پاکولو لپاره Esc کېکاږئ!',
    'moderate.th_id_date'                => 'ID / نېټه',
    'moderate.th_table_record'           => 'جدول، ریکارډ او ستون',
    'moderate.th_comparison'             => 'پرتله کول (فعال vs وړاندیز شوی) او سند',
    'moderate.th_actions'                => 'د مدیر کړنې',
    'moderate.no_suggestions'            => 'پدې جدول کې د بیاکتنې لپاره هېڅ وړاندیز نشته.',
    'moderate.by_label'                  => 'د دې لخوا:',
    'moderate.guest_user'                => 'مېلمه / لیدونکی',
    'moderate.record_id_label'           => 'د ریکارډ ID:',
    'moderate.column_label'              => 'ستون:',
    'moderate.required_badge'            => 'لازمی',
    'moderate.live_value_label'          => 'اوسنی فعال ارزښت:',
    'moderate.empty_placeholder'         => '[خالي]',
    'moderate.proposed_value_label'      => 'وړاندیز شوی بدلون:',
    'moderate.evidence_label'            => 'سند / دلیل:',
    'moderate.no_evidence'               => 'هېڅ سند یا دلیل نه دی وړاندې شوی.',
    'moderate.override_label'            => 'ارزښت بدلول:',
    'moderate.select_placeholder'        => '-- وټاکئ --',
    'moderate.historical_dates_title'    => 'ملاتړ شوې تاریخي نېټې',
    'moderate.approve_confirm'           => 'آیا دا ارزښت ومنئ او تطبیق یې کړئ؟',
    'moderate.decline_confirm'           => 'آیا دا وړاندیز رد او لغوه کړئ؟',
    'moderate.approve_btn'               => 'منل',
    'moderate.decline_btn'               => 'رد کول',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'د وېبپاڼې د خبرتیاوو مدیریت',
    'notices.subheading'                 => 'متحرک خبرتیاوې، د ښه راغلاست برېښنايي بانېرونه، یا د ځانګړو کاروونکو لپاره اعلانونه جوړ کړئ.',
    'notices.error_blank'                => 'سرلیک او منځپانګه نشي کولی خالي وي.',
    'notices.msg_created'                => 'خبرتیا په بریا سره جوړه شوه!',
    'notices.msg_deleted'                => 'خبرتیا ړنګه شوه.',
    'notices.create_heading'             => 'نوی خبرتیا جوړول',
    'notices.title_label'                => 'د خبرتیا سرلیک:',
    'notices.content_label'              => 'د خبرتیا منځپانګه (HTML/متن اجازه لري):',
    'notices.target_roles_label'         => 'مطلوب لیدونکي (رولونه یا ټول وټاکئ):',
    'notices.role_everyone'              => 'ټول کسان',
    'notices.role_public'                => 'عامه (مېلمانه)',
    'notices.role_users'                 => 'کاروونکي',
    'notices.role_moderators'            => 'مدیران (مډرېټران)',
    'notices.role_admins'                => 'لاسلرسي لرونکي (ایډمنان)',
    'notices.dismissible_label'          => "د بندولو وړ (د 'X' تڼۍ سره)",
    'notices.display_order_label'        => 'د ښودلو ترتیب:',
    'notices.publish_btn'                => 'خبرتیا خپرول',
    'notices.existing_heading'           => 'فعاله او شته خبرتیاوې',
    'notices.th_order'                   => 'ترتیب',
    'notices.th_title'                   => 'سرلیک',
    'notices.th_target_roles'            => 'مطلوب رولونه',
    'notices.th_dismissible'             => 'د بندولو وړ',
    'notices.no_notices'                 => 'تر اوسه هېڅ خبرتیا نه ده جوړه شوې.',
    'notices.yes'                        => 'هو',
    'notices.no_sticky'                  => 'نه (سټیکي / Sticky)',
    'notices.delete_confirm'             => 'آیا غواړئ دا خبرتیا ړنګ کړئ؟',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'د وېبپاڼې عمومي تنظیمات، ماډلونه او واکونه',
    'settings.subheading'                => 'اصلي تنظیمات، د برېښنالیک ډرایورونه، امنیتي/CAPTCHA اختیارونه، د ځانګړتیاوو ماډلونه، د ساتنې حالت، خبرتیاوې او د رولونو مټریکس اداره کړئ.',
    'settings.tab_core'                  => 'اصلي او برېښنالیک',
    'settings.tab_modules'               => 'ماډلونه',
    'settings.tab_maintenance'           => 'ساتنه',
    'settings.tab_notices'               => 'د وېبپاڼې خبرتیاوې',
    'settings.tab_permissions'           => 'رولونه او واکونه',
    'settings.tab_audit'                 => 'د پلټنې لاګ (Audit Log)',
    'settings.db_updates_heading'        => 'د ډیټابیس تازه معلومات',
    'settings.schema_current'            => 'اوسنی جوړښت (Schema) نسخه:',
    'settings.schema_latest'             => 'تر ټولو وروستۍ نسخه:',
    'settings.download_backup_btn'       => 'د ډیټابیس بیک اپ راښکته کول',
    'settings.download_backup_desc'      => 'بشپړ .sql فایل په خپل کمپیوټر کې خوندي کړئ. د تازه معلوماتو له پلي کولو دمخه یې په خوندي ځای کې وساتئ.',
    'settings.schema_update_notice'      => 'د ډیټابیس تازه معلومات شتون لري. د پرمخ تلو دمخه پورته بیک اپ راښکته کړئ.',
    'settings.migration_confirm'         => 'آیا تاسو د ډیټابیس بیک اپ راښکته کړی دی؟ دا به پاتې تازه معلومات پلي کړي.',
    'settings.update_db_btn'             => 'ډیټابیس تازه کول',
    'settings.schema_uptodate'           => 'ډیټابیس تازه دی.',
    'settings.core_sys_heading'          => 'د سیستم اصلي تنظیمات',
    'settings.sys_name_label'            => 'د سیستم / غوښتنلیک نوم:',
    'settings.default_lang_label'        => 'د وېبپاڼې اصلي ژبه:',
    'settings.default_lang_desc'         => 'د مېلمنو او هغو کاروونکو لپاره کارول کیږي چې ژبه یې نه ده ټاکلې. د نورو ژبو لپاره فایلونه په lang/ كې کېږدئ (لکه ps.php).',
    'settings.captcha_heading'           => 'د امنیت او CAPTCHA تنظیمات',
    'settings.captcha_provider_label'    => 'د CAPTCHA انجن:',
    'settings.captcha_none'              => 'غیر فعال (بې CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'د Cloudflare Turnstile تنظیمات',
    'settings.recaptcha_heading'         => 'د Google reCAPTCHA تنظیمات',
    'settings.hcaptcha_heading'          => 'د hCaptcha تنظیمات',
    'settings.site_key_label'            => 'د وېبپاڼې کیلي (عامه):',
    'settings.secret_key_label'          => 'پټه کیلي (شخصي):',
    'settings.mail_heading'              => 'د برېښنالیک لېږلو تنظیمات',
    'settings.mail_domain_label'         => 'د سیستم برېښنالیک ډومین:',
    'settings.mail_from_label'           => "د 'لخوا' (From) ځانګړی برېښنالیک:",
    'settings.mail_from_desc'            => 'هغه ځانګړې پته چې د وتونکو برېښنالیکونو د لېږونکي په توګه کارول کیږي.',
    'settings.mail_driver_label'         => 'د برېښنالیک ډرایور / انجن:',
    'settings.driver_native'             => 'اصلي برېښنالیک (ځايي Postfix)',
    'settings.driver_smtp'               => 'تایید شوی SMTP (PHPMailer)',
    'settings.smtp_heading'              => 'د SMTP سرور تنظیمات',
    'settings.smtp_host_label'           => 'د SMTP کورنوم (Host):',
    'settings.smtp_port_label'           => 'پورټ (Port):',
    'settings.smtp_encryption_label'     => 'سخت خوندیتوب (Encryption):',
    'settings.enc_tls'                   => 'TLS (پورټ 587)',
    'settings.enc_ssl'                   => 'SSL (پورټ 465)',
    'settings.smtp_user_label'           => 'د SMTP کارن نوم:',
    'settings.smtp_pass_label'           => 'د SMTP پټنوم (که خالي پاتې شي اوسنی به وساتل شي):',
    'settings.save_core_mail_btn'        => 'اصلي او برېښنالیک تنظیمات خوندي کول',
    'settings.test_mail_heading'         => 'د برېښنالیک ازموينه',
    'settings.test_email_label'          => 'د اخيستونکي برېښنالیک پته:',
    'settings.send_test_btn'             => 'د ازموينې برېښنالیک لېږل',
    'settings.modules_heading'           => 'د غوښتنلیک د ماډلونو چالان/بندول او د اغېزمنتیا کنټرول',
    'settings.modules_subheading'        => 'د غوښتنلیک د اجرا د ښه کولو لپاره ځانګړتیاوې فعالې یا غیر فعالې کړئ.',
    'settings.mod_users'                 => 'د کاروونکو مدیریت او څو کاروونکي لاسرسی',
    'settings.mod_users_desc'            => 'نوملیکنه، د کاروونکو مدیریت او د څو کاروونکو تایید فعالول.',
    'settings.mod_leaderboard'           => 'مخکښان او لوبوونکی سیستم (Gamification)',
    'settings.mod_leaderboard_desc'      => 'د نقل کولو هڅې پېژندل او د ستورو نمرې ورکول.',
    'settings.mod_leaderboard_note'      => '(د کاروونکو مدیریت او څو کاروونکي لاسرسي ته اړتیا لري)',
    'settings.mod_moderation'            => 'د بیاکتنې بهیر (Moderation)',
    'settings.mod_moderation_desc'       => 'د بدلون وړاندیزونو بیاکتنه او د بیاکتنې صف فعالول.',
    'settings.mod_volunteers'            => 'د رضاکارانو پورټل او غوښتنې',
    'settings.mod_volunteers_desc'       => 'د عامه رضاکارانو د غوښتنې فورمه او د مدیر ډشبورډ فعالول.',
    'settings.mod_feedback'              => 'د نظرونو لېږل',
    'settings.mod_feedback_desc'         => 'عامه نظرونو فورمه او اړوند مدیر ډشبورډ فعالول.',
    'settings.save_modules_btn'          => 'د ماډلونو تنظیمات خوندي کول',
    'settings.maintenance_heading'       => 'د سیستم د ساتنې حالت (Maintenance)',
    'settings.maintenance_toggle'        => 'د ساتنې حالت فعالول (وېبپاڼه له کرښې وباسئ / آفلاین)',
    'settings.maintenance_reason_label'  => 'د کاروونکو لپاره لامل / پیغام:',
    'settings.maintenance_eta_label'     => 'د بیرته پرانیستلو اټکل شوې وخت (ETA):',
    'settings.save_maintenance_btn'      => 'د ساتنې تنظیمات خوندي کول',
    'settings.notices_heading'           => 'د وېبپاڼې خبرتیاوې او اعلانونه',
    'settings.add_notice_btn'            => '+ نوی خبرتیا اضافه کول',
    'settings.no_notices'                => 'هېڅ خبرتیا نه ده تنظیم شوې.',
    'settings.status_active'             => 'فعال',
    'settings.status_inactive'           => 'غیر فعال',
    'settings.notice_content_label'      => 'منځپانګه:',
    'settings.save_notice_btn'           => 'خبرتیا خوندي کول',
    'settings.permissions_heading'       => 'متغیر رولونه او د واکونو مټریکس',
    'settings.permissions_subheading'    => 'واکونه د سیستم د دندو له مخې ویشل شوي دي. د واکونو د تنظیمولو لپاره برخې پراخې کړئ.',
    'settings.th_role'                   => 'رول',
    'settings.th_capabilities'           => 'پدې ډله کې شامل واکونه',
    'settings.save_permissions_btn'      => 'د واکونو مټریکس خوندي کول',
    'settings.audit_heading'             => 'د سیستم د پلټنې لاګ لټونگر',
    'settings.audit_subheading'          => 'ثبت شوي امنیتي کړنې، د ډاټا داخلول او بیاکتنې وپلټئ. د اړتیا په صورت کې لاګونه پاکولی شئ.',
    'settings.purge_all_confirm'         => '⚠️ خبرداری: دا به د سیستم ټولې پلټنې لاګونه په بشپړ ډول ړنګ کړي. آیا ډاډه یاست؟',
    'settings.clear_all_audit_btn'       => 'ټولې پلټنې لاګونه پاکول',
    'settings.purge_records_confirm'     => 'آیا غواړئ د ریکارډونو اړوند ټولې پلټنې لاګونه پاک کړئ؟',
    'settings.clear_records_audit_btn'   => 'یوازې د ریکارډونو لاګونه پاکول',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'نېټه او وخت',
    'settings.th_actor'                  => 'عمل کونکی',
    'settings.th_action'                 => 'کړنه',
    'settings.th_record_id'              => 'د ریکارډ ID',
    'settings.th_details'                => 'مفصل معلومات',
    'settings.th_ip'                     => 'د IP پته',
    'settings.no_audit_logs'             => 'هېڅ د پلټنې لاګ ونه موندل شو.',
    'settings.system_guest'              => 'سیستم / مېلمه',
    'settings.audit_limit_note'          => 'وروستي 250 د پلټنې لاګونه ښودل کیږي.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'د کاروونکو اکاونټونو مدیریت او د مخکښانو بیاکتنه',
    'admin_users.subheading'             => 'د کاروونکو حالت وګورئ، رولونه ورکړئ، برېښنالیکونه بدل کړئ، د پټنوم بیا تنظیمول پیل کړئ، یا اکاونټونه وځلوئ.',
    'admin_users.manage_templates_btn'   => 'د برېښنالیک کينډۍ مدیریت',
    'admin_users.invite_user_btn'        => 'نوی کاروونکی بلل',
    'admin_users.th_username'            => 'کارن نوم',
    'admin_users.th_email_override'      => 'برېښنالیک او بدلون',
    'admin_users.th_role_assignment'     => 'د رول ټاکل',
    'admin_users.th_score'               => 'نمرې',
    'admin_users.th_status'              => 'حالت',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'کړنې او بیاکتنې',
    'admin_users.no_users'               => 'هېڅ کاروونکی ونه موندل شو.',
    'admin_users.save_email_title'       => 'نوی برېښنالیک خوندي کول',
    'admin_users.verified_label'         => 'تایید شوی:',
    'admin_users.yes'                    => 'هو',
    'admin_users.no'                     => 'نه',
    'admin_users.protected_admin'        => 'ساتل شوی اصلي ایډمن',
    'admin_users.update_btn'             => 'تازه کول',
    'admin_users.status_active'          => 'فعال',
    'admin_users.status_suspended'       => 'ځنډول شوی',
    'admin_users.enabled'                => 'فعال شوی',
    'admin_users.disabled'               => 'غیر فعال شوی',
    'admin_users.set_score_btn'          => 'نمرې ټاکل',
    'admin_users.resend_invite_confirm' => 'آیا دې کاروونکي ته د بلنې برېښنالیک بیا ولېږل شي؟',
    'admin_users.resend_invite_btn'      => 'بلنه بیا لېږل',
    'admin_users.reset_pwd_confirm'      => 'آیا دې کاروونکي ته د پټنوم د بیا تنظیمولو لینک ولېږل شي؟',
    'admin_users.reset_password_btn'     => 'پټنوم بیا تنظیمول',
    'admin_users.suspend_confirm'        => 'آیا د ناوړه ګټې اخیستنې له امله کاروونکی وځلوئ او لاسرسی یې بند کړئ؟',
    'admin_users.suspend_btn'            => 'ځنډول',
    'admin_users.reactivate_btn'         => 'بیا فعالول',
    'admin_users.reset_2fa_confirm'      => 'آیا د دې کاروونکي 2FA بیا تنظیم او غیر فعال کړئ؟',
    'admin_users.reset_2fa_btn'          => 'د 2FA بیا تنظیمول',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'د ټکټونو ډشبورډ ته بېرته ستنېدل',
    'view_ticket.ticket_heading_prefix'=> 'ټکټ',
    'view_ticket.support_request'      => 'د ملاتړ غوښتنه',
    'view_ticket.submitted_by'         => 'لېږونکی:',
    'view_ticket.on_date'              => 'نېټه:',
    'view_ticket.submitted_fields'     => 'لېږل شوي برخې:',
    'view_ticket.ticket_status_label'  => 'د ټکټ حالت:',
    'view_ticket.status_pending'       => 'تر کار لاندې / په تمه',
    'view_ticket.status_progress'      => 'پرمختګ روان دی',
    'view_ticket.status_completed'     => 'بشپړ شو',
    'view_ticket.status_rejected'      => 'رد شو',
    'view_ticket.dialogue_heading'     => 'د خبرو اترو لړۍ',
    'view_ticket.no_replies'           => 'تر اوسه هېڅ ځواب نه دی ثبت شوی.',
    'view_ticket.admin_label'          => 'مدیر',
    'view_ticket.staff'                => 'کارکونکی',
    'view_ticket.post_reply_heading'   => 'ځواب ولېږئ او لېږونکي ته خبر ورکړئ',
    'view_ticket.reply_placeholder'    => 'دلته خپل ځواب ولیکئ...',
    'view_ticket.send_reply_btn'       => 'ځواب ولېږئ او برېښنالیک ورسوئ',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'د رضاکارانو غوښتنې او بهیر',
    'volunteer_dashboard.subheading'         => 'غوښتنې وپلټئ، خبرې اترې تنظیم کړئ، د مرکو یادښتونه ثبت کړئ او کاندیدان استخدام کړئ.',
    'volunteer_dashboard.manage_emails_btn' => 'د برېښنالیک کينډۍ مدیریت',
    'volunteer_dashboard.manage_schema_btn' => 'د فورمې جوړښت مدیریت',
    'volunteer_dashboard.th_status'          => 'حالت',
    'volunteer_dashboard.th_name'            => 'نوم',
    'volunteer_dashboard.th_interview_notes'=> 'مرکه / یادښتونه',
    'volunteer_dashboard.no_submissions'     => 'هېڅ د رضاکار غوښتنه ونه موندل شوه.',
    'volunteer_dashboard.volunteer_prefix'   => 'رضاکار',
    'volunteer_dashboard.chat_label'         => 'خبرې اترې:',
    'volunteer_dashboard.notes_label'        => 'یادښتونه:',
    'volunteer_dashboard.no_notes'           => 'هېڅ یادښت نشته',
    'volunteer_dashboard.chat_notes_btn'     => 'خبرې او یادښتونه',
    'volunteer_dashboard.accept_title'       => 'د کاروونکي د بلنې له لارې منل',
    'volunteer_dashboard.accept_invite_btn'  => 'منل او بلنه لېږل',
    'volunteer_dashboard.delete_confirm'     => 'آیا دا د رضاکار ریکارډ ړنګ کړئ؟',
    'volunteer_dashboard.modal_heading'      => 'د کاندید مرکه او یادښتونه اداره کړئ',
    'volunteer_dashboard.modal_status_label'=> 'د غوښتنې حالت:',
    'volunteer_dashboard.status_pending'     => 'د بیاکتنې په تمه',
    'volunteer_dashboard.status_chat'        => 'خبرې اترې تنظیم شوې',
    'volunteer_dashboard.status_accepted'    => 'منل شوی',
    'volunteer_dashboard.status_rejected'    => 'رد شوی',
    'volunteer_dashboard.modal_date_label'   => 'د ټاکل شوې مرکې نېټه او وخت:',
    'volunteer_dashboard.modal_notes_label'  => 'د مرکې یادښتونه:',
    'volunteer_dashboard.modal_notes_placeholder' => 'دلته د خبرو اترو نظرونه ثبت کړئ...',
    'volunteer_dashboard.save_changes_btn'   => 'بدلونونه خوندي کول',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 منع دی: عامه لیدنه فعاله نه ده.',
    'api_search.error_unauthorized_table' => 'جدول ته د لاسرسي اجازه نشته.',
    'api_search.no_records'              => 'پدې جدول کې هېڅ ریکارډ ونه موندل شو.',
    'api_search.history_btn'             => 'تاریخچه',
    'api_search.suggest_edit_btn'        => 'د سمون وړاندیز',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'د کور پاڼې ته بېرته ستنېدل',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ---------------------------------------------------               -------
    'feedback.hp_label'              => 'خالي پریږدئ',
    'feedback.first_name_label'      => 'نوم:',
    'feedback.surname_label'         => 'تخلص:',
    'feedback.email_label'           => 'برېښنالیک پته:',
    'feedback.subject_label'         => 'موضوع / د پوښتنې سرلیک:',
    'feedback.required_title'        => 'لازمي برخه',
    'feedback.select_placeholder'    => '-- وټاکئ --',
    'feedback.multi_select_hint'     => 'د څو توکو د ټاکلو لپاره Ctrl یا Cmd کېکاږئ.',
    'feedback.submit_btn'            => 'ټکټ لېږل',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'د امنیت تېروتنه: مشکوک لاسرسي نښه.',
    'security_engine.err_access_denied'    => 'د امنیت تېروتنه: لاسرسی رد شو.',
    'security_engine.err_rate_limit'       => 'د دې IP پتې څخه ډېرې غوښتنې شوې دي، مهرباني وکړئ وروسته بیا هڅه وکړئ.',
    'security_engine.err_excessive_links'  => 'د ډېرو لینکونو له امله لېږل رد شول.',
    'security_engine.err_complete_captcha' => 'مهرباني وکړئ د CAPTCHA امنیتي تایید بشپړ کړئ.',
    'security_engine.err_captcha_failed'   => 'د CAPTCHA تایید ناکام شو، مهرباني وکړئ بیا هڅه وکړئ.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'نصب بشپړ شو',
    'install.complete_heading'           => 'نصب بشپړ شو',
    'install.complete_desc'              => 'دا وېبپاڼه دمخه تنظیم شوې ده. د بیا ځلي چلولو د مخنیوي لپاره نصب کونکی بند شوی دی.',
    'install.login_link'                 => 'ننوتل',
    'install.home_link'                  => 'وېبپاڼې ته تلل',
    'install.delete_folder_hint'         => 'د لا ډېر امنیت لپاره، تاسو کولی شئ <code>install</code> پوښۍ (folder) ړنګه یا نوم بدله کړئ.',
    'install.msg_db_ready'               => 'ډیټابیس چمتو دی. د نصب بشپړولو لپاره خپل د مدیر اکاونټ جوړ کړئ.',
    'install.err_config_load'            => 'د شته تنظیماتو کارول نشي کېدای:',
    'install.err_write_permission'       => 'PHP نشي کولی پدې پروژه کې فایلونه جوړ کړي.',
    'install.detail_prefix'              => 'تفصیل:',
    'install.err_db_required'            => 'د ډیټابیس نوم او د ډیټابیس کارن نوم دواړه لازمي دي.',
    'install.err_db_not_empty'           => 'دا ډیټابیس خالي نه دی. مهرباني وکړئ نوی خالي ډیټابیس وکاروئ (یا ټول جدولونه پاک کړئ) او بیا هڅه وکړئ.',
    'install.msg_schema_imported'        => 'ډیټابیس وصل شو او جوړښت یې وارد شو. مهرباني وکړئ خپل د مدیر اکاونټ جوړ کړئ.',
    'install.err_complete_db_first'      => 'مهرباني وکړئ لومړی د ډیټابیس پړاو بشپړ کړئ.',
    'install.err_admin_required'         => 'د مدیر ټولې برخې لازمي دي.',
    'install.err_invalid_email'          => 'برېښنالیک پته ناسمه ده.',
    'install.err_password_length'        => 'پټنوم باید لږترلږه 8 توري وي.',
    'install.err_passwords_match'        => 'واړه پټنومونه سره نه لګیږي.',
    'install.err_admin_save_failed'      => 'د مدیر کاروونکي خوندي کولو کې پاتې راتلل، مهرباني وکړئ د کاروونکو جدول جوړښت وګورئ.',
    'install.msg_installation_complete' => 'نصب بشپړ شو.',
    'install.page_title'                 => 'نصب — د کلیسا د ریکارډونو لارښود',
    'install.heading'                    => 'نصب',
    'install.subheading'                 => 'لومړنۍ تنظیمات <strong>یوازې د دې غوښتنلیک پوښۍ لپاره دي</strong>. مهرباني وکړئ یو خالي MySQL ډیټابیس وکاروئ.',
    'install.done_heading'               => 'بشپړ شو',
    'install.done_message'               => 'نصب بشپړ شو. نصب کونکی اوس بند دی.',
    'install.admin_heading'              => 'د وېبپاڼې د مدیر اکاونټ',
    'install.admin_subheading'           => 'دا د <strong>دې وېبپاڼې</strong> د ننوتلو اکاونټ دی (د ډیټابیس اکاونټ نه دی).',
    'install.admin_username_label'       => 'د مدیر کارن نوم',
    'install.admin_email_label'          => 'د مدیر برېښنالیک',
    'install.admin_password_label'       => 'د مدیر پټنوم (لږترلږه 8 توري)',
    'install.admin_confirm_password_label' => 'د مدیر پټنوم تایید',
    'install.finish_btn'                 => 'نصب بشپړول',
    'install.db_heading'                 => 'د ډیټابیس پیوستون',
    'install.db_hint'                    => 'مهرباني وکړئ د خپل <strong>هوسټ کنټرول پینل</strong> څخه د MySQL معلومات وکاروئ. دا د وېبپاڼې ایډمن لوګین نه دی.',
    'install.db_host_label'              => 'د ډیټابیس کورنوم (Host)',
    'install.db_name_label'              => 'د ډیټابیس نوم',
    'install.db_user_label'              => 'د ډیټابیس کارن نوم',
    'install.db_pass_label'              => 'د ډیټابیس پټنوم',
    'install.db_submit_btn'              => 'جدولونه جوړول او مخکې تلل',
    'install.req_heading'                => '1. اړتیاوې',
    'install.req_php'                    => 'PHP 8.0+ (موندل شوی %s)',
    'install.req_pdo'                    => 'د PDO MySQL غځونه',
    'install.req_logs'                   => 'د لیکلو وړ لاګ پوښۍ (یا د پروژې پوښۍ)',
    'install.req_probe'                  => 'پدې پروژه کې د فایلونو د جوړولو وړتیا شتون لري',
    'install.continue_btn'               => 'مخکې تلل',
    'install.req_fail_msg'               => 'مهرباني وکړئ نامکملې ازموينې سمې کړئ او بیا دا پاڼه تازه کړئ.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'د مخکښانو لید',
    'leaderboard.heading'         => 'د ټولنې د ګډون مخکښان',
    'leaderboard.subheading'      => 'هغو غړو ته درناوی چې د ډیټابیس ریکارډونو په راټولولو، نقل کولو او اداره کولو کې یې برخه اخیستې ده.',
    'leaderboard.th_rank'         => 'درجه',
    'leaderboard.th_contributor'  => 'همکار',
    'leaderboard.th_role'         => 'رول',
    'leaderboard.th_score'        => 'نمرې',
    'leaderboard.no_users'        => 'تر اوسه په مخکښانو کې هېڅ فعال کاروونکی ونه موندل شو.',
    'leaderboard.medal_gold'      => 'د طلا مډال',
    'leaderboard.medal_silver'    => 'د سپینو زرو مډال',
    'leaderboard.medal_bronze'    => 'د مسو مډال',
    'leaderboard.medal_ribbon'    => 'څلورم درجې ربن',
    'leaderboard.medal_rosette'   => 'پنځم درجې ګلابي نښان',
    'leaderboard.medal_trophy'    => 'شپږم درجې جام',
    'leaderboard.medal_star'      => 'اووم درجې ستوری',
    'leaderboard.medal_military'  => 'اتم درجې پوځي مډال',
    'leaderboard.medal_glowing'   => 'نهم درجې ځلیدونکی ستوری',
    'leaderboard.medal_crown'     => 'لسم درجې تاج',
    'leaderboard.you_badge'       => '(تاسو)',
    'leaderboard.default_role'    => 'کاروونکی',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'د کلیسا ریکارډونه د عامه تاریخي سرچینو څخه راټول شوي دي.',
    'footer.software_notice'  => 'خلاصې سرچینې سافټویر، د MIT جواز لاندې خپور شوی.',
    'footer.rights_reserved'  => 'ټول حقوق خوندي دي.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'د کلیسا د ریکارډونو ډیټابیس',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'خبرتیا بندول',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'هېڅ ریکارډ نه دی ټاکل شوی.',
    'record_history.exit_not_found'        => 'ریکارډ ونه موندل شو.',
    'record_history.heading_prefix'        => 'تاریخچه او د پلټنې لاګ: ریکارډ',
    'record_history.return_btn'            => 'بېرته',
    'record_history.directory_table_label'=> 'د لارښود جدول:',
    'record_history.subheading_lifecycle' => 'د دې ریکارډ اړوند بدلونونه، وړاندیزونه او اسناد ښيي.',
    'record_history.snapshot_heading'      => 'د اوسني فعال ارزښت انځور',
    'record_history.empty_value'           => '[خالي]',
    'record_history.timeline_heading'      => 'د پېښو او کړنو مهال ویش',
    'record_history.no_history'            => 'تر اوسه د دې ریکارډ لپاره ځانګړې پېښې نه دي ثبت شوې.',
    'record_history.purge_confirm'         => 'آیا دا ځانګړی د پلټنې لاګ ړنګ کړئ؟',
    'record_history.purge_btn'             => 'لاګ پاکول',
    'record_history.actor_label'           => 'عمل کونکی:',
    'record_history.system_guest'          => 'سیستم / مېلمه',
    'record_history.target_column'         => 'پام وړ ستون:',
    'record_history.proposed_value'        => 'وړاندیز شوی ارزښت:',
    'record_history.reasoning_evidence'    => 'دلیل / سند:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'ډیټابیس په بریا سره تازه شو! %d مهاجرتونه پلي شول.',
    'update_database.msg_uptodate'     => 'ډیټابیس دمخه تازه دی.',
    'update_database.err_failed'       => 'تازه کول ناکام شول:',
    'update_database.page_title'       => 'د سیستم تازه کولو ته اړتیا ده — د کلیسا ریکارډونه',
    'update_database.heading'          => '⚠️ د سیستم تازه کولو ته اړتیا ده',
    'update_database.subheading'       => 'د غوښتنلیک د ډیټابیس جوړښت زوړ شوی دی، د عادي کار د دوام لپاره نوې بڼې ته اړتیا ده.',
    'update_database.current_version'  => 'اوسنی جوړښت نسخه:',
    'update_database.latest_version'   => 'تر ټولو وروستۍ نسخه:',
    'update_database.proceed_login'    => 'د ننوتلو پاڼې ته تلل',
    'update_database.confirm_prompt'   => 'آیا تاسو د خپل ډیټابیس بیک اپ کړی دی؟ د پاتې بدلونونو د پلي کولو لپاره OK کېکاږئ.',
    'update_database.update_btn'       => 'اوس ډیټابیس تازه کول',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'اسم یا پټنوم سم نه دی یا اکاونټ محدود شوی دی.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'لازمي برخه \'%s\' نشي کولی خالي وي.',
    'save_data_entry.audit_created_prefix' => 'په جدول کې د ID %d سره ریکارډ جوړ شو.',
    'save_data_entry.msg_success'          => 'ریکارډ په بریا سره اضافه شو!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ---------------------------------------------------               -------
    'save_public_suggestion.err_spam_detected'  => 'سپام وپیژندل شو، لېږل رد شول.',
    'save_public_suggestion.err_field_required' => 'دا برخه لازمي ده او نشي کولی خالي ولېږل شي.',
    'save_public_suggestion.msg_success'        => 'ستاسو د سمون وړاندیز په بریا سره ولسول شو او د بیاکتنې صف ته واستول شو. مننه!',
    'save_public_suggestion.err_failed_submit'  => 'د سمون وړاندیز په لېږلو کې پاتې راتلل، مهرباني وکړئ بیا هڅه وکړئ.',
    'save_public_suggestion.err_invalid_column' => 'ټاکل شوې ستون سمه نه ده.',
    'save_public_suggestion.err_invalid_params' => 'د ریکارډ د لېږلو پارامترونه ناسم دي.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'YYYY-MM-DD (یا ځینې کلونه)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/YYYY (یا ځینې کلونه)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/YYYY (یا ځینې کلونه)',
    'data_entry.no_tables_heading'    => '⚠️ هېڅ ډیټابیس جدول ونه موندل شو',
    'data_entry.no_tables_desc'       => 'اوس مهال د ډاټا داخلولو لپاره هېڅ فعال جدول نه دی تنظیم شوی.',
    'data_entry.admin_tables_prompt'  => 'د مدیر په توګه، د ریکارډونو د داخلولو لپاره <strong>د جدولونو مدیریت</strong> ته لاړ شئ او یو جدول او ستون جوړ کړئ.',
    'data_entry.go_manage_tables'     => 'د جدولونو مدیریت ته تلل',
    'data_entry.contact_admin_tables' => 'مهرباني وکړئ د جدولونو او ستنو د تنظیمولو لپاره له مدیر سره اړیکه ونیسئ.',
    'data_entry.no_cols_heading'      => '⚠️ هېڅ ستون نه ده تنظیم شوې',
    'data_entry.no_cols_desc'         => 'په سیستم کې جدولونه شتون لري، مګر د اوسني فعال جدول لپاره هېڅ ستون نه ده تعریف شوې.',
    'data_entry.admin_cols_prompt'    => 'د مدیر په توګه، مهرباني وکړئ <strong>د جدولونو مدیریت</strong> ته لاړ شئ او لږترلږه یوه ستون اضافه کړئ.',
    'data_entry.contact_admin_cols'   => 'مهرباني وکړئ د دې جدول د ستنو د تنظیمولو لپاره له مدیر سره اړیکه ونیسئ.',
    'data_entry.active_table_label'   => 'د ډاټا داخلولو فعال جدول:',
    'data_entry.add_entry_summary'    => '➕ نوې ډاټا اضافه کول (د پراخولو/تړلو لپاره کلیک وکړئ)',
    'data_entry.bool_yes_true'        => 'هو / رښتیا',
    'data_entry.bool_no_false'        => 'نه / دروغ',
    'data_entry.bool_male'            => 'نارینه',
    'data_entry.bool_female'          => 'ښځینه',
    'data_entry.bool_true'            => 'رښتیا',
    'data_entry.bool_false'           => 'دروغ',
    'data_entry.bool_tick'            => '✔ (نښه)',
    'data_entry.bool_cross'           => '✘ (تېروتنه)',
    'data_entry.date_title_hint'      => 'بشپړې یا ځینې نېټې منل کیږي (لکه 1842 یا 1842-05)',
    'data_entry.enter_value_placeholder' => 'ارزښت ولیکئ...',
    'data_entry.submit_data_btn'      => 'ډاټا لېږل',
    'data_entry.shortcuts_tip'        => '💡 لارښوونه: د لېږلو لپاره <strong>Ctrl + Enter</strong> او د پاکولو لپاره <strong>Esc</strong> کېکاږئ.',
    'data_entry.dup_heading'          => '⚠️ د تکرار احتمال خبرداری',
    'data_entry.dup_desc'             => 'موږ په سیستم کې ورته ریکارډ موندلی دی:',
    'data_entry.dup_item_format'      => 'د ریکارډ ID: %d — ارزښت: %s',
    'data_entry.dup_prompt'           => 'آیا غواړئ دا تکراري ریکارډ بیا هم خوندي کړئ؟',
    'data_entry.dup_confirm_btn'      => 'هو، تایید او خوندي یې کړئ',
    'data_entry.search_summary'       => '🔍 شته ریکارډونه ولټوئ او فلټر کړئ (د پرانیستلو/تړلو لپاره کلیک وکړئ)',
    'data_entry.date_to_label'        => 'تر',
    'data_entry.filter_all_option'    => '-- ټول --',
    'data_entry.filter_placeholder'   => 'فلټر کول...',
    'data_entry.apply_filters_btn'    => 'فلټرونه تطبیق کول',
    'data_entry.reset_filter_btn'     => 'فلټر بیا تنظیمول',
    'data_entry.csv_entire_btn'       => 'بشپړ CSV راښکته کول',
    'data_entry.json_entire_btn'      => 'بشپړ JSON راښکته کول',
    'data_entry.copy_entire_btn'      => 'بشپړ جدول کاپي کول',
    'data_entry.csv_filtered_btn'     => 'فلټر شوی CSV راښکته کول',
    'data_entry.json_filtered_btn'     => 'فلټر شوی JSON راښکته کول',
    'data_entry.copy_filtered_btn'    => 'فلټر شوی جدول کاپي کول',
    'data_entry.clipboard_alert'      => 'د جدول ډاټا کلېپ بورډ ته کاپي شوه! تاسو کولی شئ دا په Excel یا Google Sheets کې ولګوئ.',
    'data_entry.existing_records_heading' => 'د شته ریکارډونو جدول',
    'data_entry.th_added_by'          => 'زیاتونکی',
    'data_entry.th_date_created'      => 'د جوړولو نېټه',
    'data_entry.no_records'           => 'هېڅ ریکارډ ونه موندل شو.',
    'data_entry.na_value'             => 'نلري',
    'data_entry.page_label'           => 'پاڼه:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'د پټنوم بیا موندل',
    'forgot_password.heading'         => 'خپل پټنوم بیا تنظیم کړئ',
    'forgot_password.subheading'      => 'لاندې خپل برېښنالیک ولیکئ ترڅو موږ تاسو ته د پټنوم د بیا تنظیمولو خوندي لینک درکړو.',
    'forgot_password.email_label'     => 'برېښنالیک پته:',
    'forgot_password.submit_btn'      => 'د بیا تنظیمولو لینک لېږل',
    'forgot_password.back_login_link' => 'د ننوتلو پاڼې ته بېرته ستنېدل',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'کارن ننوتل',
    'login.heading'              => 'اکاونټ ته ننوتل',
    'login.username_label'       => 'کارن نوم یا برېښنالیک:',
    'login.password_label'       => 'پټنوم:',
    'login.submit_btn'           => 'ننوتل',
    'login.forgot_password_link'     => 'پټنوم مو هېر شوی دی؟',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'ښه راغلاست — د اکاونټ تنظیمات',
    'onboarding.heading'           => 'ټیم ته ښه راغلاست!',
    'onboarding.subheading'        => 'د پيل کولو دمخه، مهرباني وکړئ خپل سیمه ایز او محرمیت تنظیمات وټاکئ. تاسو کولی شئ دا هر وخت په خپل پروفایل کې بدل کړئ.',
    'onboarding.timezone_label'    => 'د وخت زون / سیمه:',
    'onboarding.date_format_label' => 'د نېټې بڼه:',
    'onboarding.time_format_label' => 'د ساعت بڼه:',
    'onboarding.time_24'          => '24 ساعته (لکه 16:07)',
    'onboarding.time_12'          => '12 ساعته AM/PM (لکه 04:07 PM)',
    'onboarding.time_none'        => 'یوازې نېټه (ساعت په بشپړ ډول پټول)',
    'onboarding.attribution_label' => 'په مخکښانو کې د نوم ښودلو خوښونه:',
    'onboarding.attribution_desc1' => 'دا کنټرولوي چې ستاسو نوم په مخکښانو او ریکارډونو کې څنګه وښودل شي.',
    'onboarding.attr_anon_title'   => 'نامعلوم:',
    'onboarding.attr_anon_text'    => 'ټولو ته لومړني توري او تصادفي شمېرې ښيي.',
    'onboarding.attr_public_title' => 'عامه:',
    'onboarding.attr_public_text'  => 'ټولو ته ستاسو بشپړ نوم ښيي.',
    'onboarding.attr_vol_title'    => 'یوازې رضاکاران:',
    'onboarding.attr_vol_text'     => 'خلکو ته لومړني توري ښيي، مګر ننوتلو رضاکارانو او مدیرانو ته ستاسو بشپړ نوم ښيي.',
    'onboarding.attr_opt_anon'     => 'نامعلوم (لومړني توري او تصادفي شمېرې)',
    'onboarding.attr_opt_public'   => 'عامه (بشپړ نوم ښودل)',
    'onboarding.attr_opt_vol'      => 'یوازې رضاکاران',
    'onboarding.submit_btn'        => 'تنظیمات خوندي کول او مخکې تلل',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ---------------------------------------------------               -------
    'profile.aria_region'          => 'د کاروونکي پروفایل مدیریت',
    'profile.heading'              => 'د کاروونکي پروفایل او امنیت',
    'profile.personal_details_heading' => 'شخصي معلومات',
    'profile.language_label'       => 'خوښه ژبه:',
    'profile.lang_site_default'    => 'د وېبپاڼې اصلي ژبه',
    'profile.update_details_btn'   => 'شخصي معلومات تازه کول',
    'profile.email_heading'        => 'برېښنالیک پته',
    'profile.current_email_label'  => 'اوسنی برېښنالیک:',
    'profile.email_verified'       => '(تایید شوی)',
    'profile.email_unverified'     => '(تایید شوی نه دی - خپل ان باکس وګورئ)',
    'profile.change_email_label'   => 'برېښنالیک بدلول:',
    'profile.aria_new_email'       => 'نوی برېښنالیک',
    'profile.update_email_btn'     => 'برېښنالیک تازه کول او تایید',
    'profile.password_heading'     => 'پټنوم بدلول',
    'profile.current_password_label' => 'اوسنی پټنوم:',
    'profile.new_password_label'   => 'نوی پټنوم (لږترلږه 8 توري):',
    'profile.confirm_password_label' => 'نوی پټنوم تایید کړئ:',
    'profile.show_passwords_label' => 'پټنوم په څرګند متن ښودل',
    'profile.update_password_btn'  => 'پټنوم تازه کول',
    'profile.tfa_heading'          => 'دوه‌ګونی تایید (2FA)',
    'profile.tfa_status_label'     => 'حالت:',
    'profile.tfa_enabled'          => 'فعال شوی',
    'profile.tfa_disabled'         => 'غیر فعال شوی',
    'profile.setup_tfa_btn'        => 'د Google Authenticator تنظیمول',
    'profile.tfa_active_desc'      => '2FA ستاسو د اکاونټ امنیت په فعاله توګه ساتي.',
    'profile.backup_codes_heading' => 'ستاسو نوي امنیتي بیک اپ کوډونه',
    'profile.download_codes_btn'   => 'نوي کوډونه د .txt په بڼه راښکته کول',
    'profile.generate_codes_confirm' => 'آیا ډاډه یاست؟ دا به پخواني بیک اپ کوډونه لغوه کړي.',
    'profile.generate_codes_btn'   => 'نوي بیک اپ کوډونه جوړول',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'کاروونکي نوملیکنه',
    'register.heading'        => 'نوی اکاونټ جوړول',
    'register.username_label' => 'کارن نوم:',
    'register.submit_btn'     => 'نوملیکنه',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ---------------------------------------------------               -------
    'set_password.exit_invalid_token'        => 'د تنظیمولو نښه (Token) سمه نه ده یا له لاسه تللې ده.',
    'set_password.exit_expired_token'        => 'دا د پټنوم لینک ناسم دی یا یې وخت پای ته رسېدلی دی.',
    'set_password.proceed_login_btn'         => 'د ننوتلو پاڼې ته تلل',
    'set_password.aria_region'               => 'پټنوم ټاکل',
    'set_password.heading_format'            => 'د %s لپاره پټنوم وټاکئ',
    'set_password.subheading_format'         => 'ښه راغلاست %s! مهرباني وکړئ لاندې خپل پټنوم وټاکئ.',
    'set_password.new_password_label'        => 'نوی پټنوم (لږترلږه 8 توري):',
    'set_password.confirm_password_label'    => 'پټنوم تایید کړئ:',
    'set_password.show_password_label'       => 'پټنوم ښودل',
    'set_password.save_password_btn'         => 'پټنوم خوندي کول',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ---------------------------------------------------               -------
    'setup_2fa.aria_region'      => 'د 2FA تنظیمولو لارښود',
    'setup_2fa.heading'          => 'د Google Authenticator تنظیمول',
    'setup_2fa.subheading'       => 'د خپل تایید اپلیکیشن په واسطه لاندې QR کوډ سکین کړئ.',
    'setup_2fa.qr_alt'           => 'د 2FA QR کوډ',
    'setup_2fa.manual_prompt'    => 'یا دا کیلي په لاسي ډول ولیکئ:',
    'setup_2fa.backup_heading'   => 'د بیړني امنیت بیا رغونې کوډونه',
    'setup_2fa.backup_desc'      => 'دا بیک اپ کوډونه په خوندي ځای کې وساتئ. که تاسو خپل اپلیکیشن ته لاسرسی له لاسه ورکړئ، هر کوډ یوازې <strong>یو ځل</strong> کارول کیدی شي:',
    'setup_2fa.download_btn'     => 'کوډونه د .txt په بڼه راښکته کول',
    'setup_2fa.code_label'       => 'د تایید او فعالولو لپاره له اپلیکیشن څخه 6 شمېره کوډ ولیکئ:',
    'setup_2fa.aria_code_input'  => '6 شمېره د تایید کوډ',
    'setup_2fa.submit_btn'       => 'تایید او د 2FA فعالول',
    'setup_2fa.cancel_link'      => 'لغوه کول او پروفایل ته بېرته ستنېدل',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ---------------------------------------------------               -------
    'suggest_edit.aria_region'          => 'د سمون وړاندیز',
    'suggest_edit.heading_prefix'       => 'د ریکارډ د سمون وړاندیز',
    'suggest_edit.return_btn'           => 'ریکارډ ته بېرته ستنېدل',
    'suggest_edit.success_msg_suffix'   => 'تاسو کولی شئ لاندې بل سمون ولېږئ، یا د پورتني لینک په واسطه بېرته لاړ شئ.',
    'suggest_edit.current_values_heading' => 'اوسني ارزښتونه:',
    'suggest_edit.empty_label'          => '(خالي)',
    'suggest_edit.submit_heading'       => 'نوی وړاندیز شوی ارزښت او سند لېږل',
    'suggest_edit.confirm_prompt'       => 'آیا غواړئ دا د سمون وړاندیز د مدیر د بیاکتنې لپاره ولېږئ؟',
    'suggest_edit.select_column_label'  => 'د سمولو لپاره ستون وټاکئ:',
    'suggest_edit.reasoning_label'      => 'سند / دلیل / د سرچینې یادښتونه:',
    'suggest_edit.reasoning_placeholder'=> 'متن، د سرچینې حواله، یا د دې بدلون لامل ولیکئ...',
    'suggest_edit.submit_btn'           => 'د بیاکتنې لپاره وړاندیز لېږل',
    'suggest_edit.proposed_value_label' => 'وړاندیز شوی نوی ارزښت:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ---------------------------------------------------               -------
    'verify_2fa.aria_region'     => 'د 2FA تایید',
    'verify_2fa.heading'         => 'دوه‌ګونی تایید',
    'verify_2fa.subheading'      => 'مهرباني وکړئ د خپل تایید اپلیکیشن 6 شمېره کوډ یا د امنیت بیک اپ کوډ ولیکئ.',
    'verify_2fa.code_label'      => 'د تایید کوډ / امنیتي کوډ:',
    'verify_2fa.aria_code_input' => 'د تایید یا امنیتي کوډ ولیکئ',
    'verify_2fa.submit_btn'      => 'تایید او ننوتل',

    // ------------------------------------------------------------------
    // Verify Email
    // ---------------------------------------------------               -------
    'verify_email.err_no_token'         => 'د تایید نښه (Token) نه ده ورکړل شوې.',
    'verify_email.err_invalid_token'    => 'د تایید نښه سمه نه ده.',
    'verify_email.msg_already_verified' => 'ستاسو برېښنالیک دمخه تایید شوی دی، تاسو کولی شئ ننوتل.',
    'verify_email.err_expired_token'    => 'د دې تایید لینک وخت پای ته رسېدلی دی (24 ساعته محدودیت). مهرباني وکړئ بیا نوملیکنه وکړئ یا نوی لینک وغواړئ.',
    'verify_email.msg_success'          => 'برېښنالیک په بریا سره تایید شو! ستاسو اکاونټ اوس فعال شو، تاسو کولی شئ ننوتل.',
    'verify_email.err_update_failed'    => 'د برېښنالیک په تایید کې تېروتنه وشوه، مهرباني وکړئ بیا هڅه وکړئ.',
    'verify_email.aria_region'          => 'د برېښنالیک د تایید حالت',
    'verify_email.heading'              => 'د برېښنالیک د تایید حالت',
    'verify_email.login_btn'            => 'د ننوتلو لپاره دلته کلیک وکړئ',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'د رضاکار فورمه',
    'volunteer.honeypot_label'       => 'مهرباني وکړئ دا برخه خالي پریږدئ:',
    'volunteer.required_field_title'=> 'لازمي برخه',
    'volunteer.multi_select_hint'    => 'د څو توکو د ټاکلو لپاره Ctrl یا Cmd کېکاږئ.',
    'volunteer.submit_btn'           => 'د رضاکار غوښتنه لېږل',
];
