<?php
// lang/uk.php - Ukrainian (Українська)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Увійти',
    'nav.logout'                 => 'Вийти',
    'nav.feedback'               => 'Зворотний зв’язок',
    'nav.volunteer'              => 'Стати волонтером',
    'nav.leaderboard'            => 'Таблиця лідерів',
    'nav.search'                 => 'Пошук',
    'nav.settings'               => 'Системні налаштування',
    'nav.high_contrast'          => 'Високий контракт',
    'nav.low_contrast'           => 'Низький контраст',
    'nav.welcome'                => 'Ласкаво просимо,',
    'nav.data_entry'             => 'Введення даних',
    'nav.moderation'             => 'Модерація',
    'nav.invite_user'            => 'Запросити користувача',
    'nav.manage_users'           => 'Керування користувачами',
    'nav.manage_tables'          => 'Керування таблицями',
    'nav.volunteer_dashboard'    => 'Панель волонтерів',
    'nav.feedback_dashboard'     => 'Панель зворотного зв’язку',
    'nav.leaderboard_score'      => 'Бали таблиці лідерів',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Багатоколонковий складений пошук',
    'search.reset'               => 'Скинути пошук',
    'search.export_csv'          => 'Експортувати відфільтровані результати в CSV',
    'search.no_records'          => 'У цьому довіднику не знайдено жодного запису.',
    'search.load_error'          => 'Не вдалося завантажити результати. Спробуйте ще раз.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Надіслати',
    'btn.cancel'                 => 'Скасувати',
    'btn.save'                   => 'Зберегти',
    'btn.delete'                 => 'Видалити',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Дякуємо! Ваш відгук успішно надіслано.',
    'feedback.error_all_fields'   => 'Будь ласка, заповніть усі поля.',
    'feedback.error_invalid_email'=> 'Будь ласка, введіть дійсну адресу електронної пошти.',
    'feedback.error_save_failed'  => 'Сталася помилка під час збереження вашого відгуку. Спробуйте ще раз.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Таблиці бази даних не знайдено',
    'index.no_tables_desc'             => 'Наразі в системі не налаштовано жодної активної таблиці бази даних.',
    'index.admin_create_table_guide'   => 'Як адміністратор, перейдіть до розділу <strong>Керування таблицями</strong>, щоб створити таблицю та додати принаймні одну колонку перед переглядом або введенням записів.',
    'index.go_to_manage_tables'        => 'Перейти до керування таблицями',
    'index.contact_admin_tables'       => 'Зверніться до адміністратора для налаштування таблиць і колонок бази даних.',
    'index.guest_login_tables_guide'   => 'Будь ласка, <a href=":login_link">увійдіть</a> або зверніться до адміністратора для налаштування таблиць.',
    'index.no_columns_heading'         => 'Колонки не налаштовано',
    'index.no_columns_desc'            => 'У системі є таблиці, але для активної таблиці не визначено жодної колонки даних.',
    'index.admin_add_columns_guide'    => 'Як адміністратор, перейдіть до розділу <strong>Керування таблицями</strong>, щоб додати принаймні одну колонку до вашої таблиці.',
    'index.contact_admin_columns'      => 'Зверніться до адміністратора для налаштування колонок цієї таблиці.',
    'index.select_directory_database'  => 'Виберіть базу даних довідника:',
    'index.opt_yes_true'               => 'Так / Істина',
    'index.opt_no_false'               => 'Ні / Хиба',
    'index.opt_male'                   => 'Чоловічий',
    'index.opt_female'                 => 'Жіночий',
    'index.opt_true'                   => 'Істина',
    'index.opt_false'                  => 'Хиба',
    'index.opt_tick'                   => '✔ (Позначка)',
    'index.opt_cross'                  => '✘ (Хрестик)',
    'index.option_all'                 => '-- Усі --',
    'index.date_to_label'              => 'до',
    'index.search_placeholder'         => 'Пошук...',
    'index.download_entire_csv'        => 'Завантажити весь CSV',
    'index.download_entire_json'       => 'Завантажити весь JSON',
    'index.copy_entire_table'          => 'Скопіювати всю таблицю',
    'index.download_filtered_csv'      => 'Завантажити відфільтрований CSV',
    'index.download_filtered_json'     => 'Завантажити відфільтрований JSON',
    'index.copy_filtered_table'        => 'Скопіювати відфільтровану таблицю',
    'index.th_record_id'               => 'ID запису',
    'index.th_created_by'              => 'Створено ким',
    'index.th_date_added'              => 'Дата додавання',
    'index.th_actions'                 => 'Дії',
    'index.modal_heading'              => 'Запропонувати виправлення запису',
    'index.modal_desc'                 => 'Надайте виправлення або альтернативну інформацію для цього запису. Наша команда модераторів перевірить його.',
    'index.modal_target_column'        => 'Цільова колонка:',
    'index.modal_proposed_value'       => 'Пропоноване значення / Виправлення:',
    'index.modal_input_placeholder'    => 'Введіть оновлену інформацію...',
    'index.modal_submit_btn'           => 'Надіслати пропозицію',
    'index.clipboard_success'          => 'Дані таблиці скопійовано до буфера обміну! Ви можете вставити їх безпосередньо в Excel або Google Таблиці.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Форма запрошення нового користувача',
    'create_user.subheading'           => 'Це створить безпечне посилання налаштування терміном на 24 години та надішле його безпосередньо користувачу електронною поштою.',
    'create_user.first_name'           => 'Ім’я:',
    'create_user.surname'              => 'Прізвище:',
    'create_user.username_label'       => 'Ім’я користувача (необов’язково):',
    'create_user.username_placeholder' => 'Залиште порожнім для автогенерації',
    'create_user.username_help'        => 'Якщо залишити порожнім, унікальне ім’я користувача буде згенеровано автоматично на основі імені.',
    'create_user.email_label'          => 'Адреса електронної пошти:',
    'create_user.role_label'           => 'Роль користувача:',
    'create_user.submit_btn'           => 'Створити користувача та надіслати запрошення',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Панель тікетів підтримки та відгуків',
    'feedback_dash.subheading'           => 'Керуйте публічними запитами на підтримку, оновлюйте статуси та беріть участь в обговоренні.',
    'feedback_dash.manage_emails'        => 'Керувати шаблонами листів',
    'feedback_dash.manage_schema'        => 'Керувати схемою форми тікетів',
    'feedback_dash.th_ticket_date'       => 'ID тікета / Дата',
    'feedback_dash.th_submitter'         => 'Відправник',
    'feedback_dash.th_subject_info'      => 'Тема / Основна інформація',
    'feedback_dash.th_status'            => 'Статус',
    'feedback_dash.no_tickets'           => 'Тікетів зворотного зв’язку не знайдено.',
    'feedback_dash.anonymous'            => 'Анонімно',
    'feedback_dash.default_subject'      => 'Загальне питання',
    'feedback_dash.open_ticket_btn'      => 'Відкрити тікет та обговорення',
    'feedback_dash.delete_confirm'       => 'Видалити цей тікет підтримки та всі пов’язані відповіді?',
    'feedback_dash.msg_deleted'          => 'Тікет #:id успішно видалено.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Шаблони листів для тікетів підтримки',
    'feedback_emails.subheading'         => 'Налаштуйте автоматичні сповіщення електронною поштою, що надсилаються під час роботи з тікетами. Використовуйте фігурні дужки для динамічних значень.',
    'feedback_emails.back_to_dashboard' => 'Повернутися до панелі тікетів',
    'feedback_emails.email_subject'      => 'Тема листа:',
    'feedback_emails.email_body'         => 'Шаблон тіла листа:',
    'feedback_emails.save_template_btn' => 'Зберегти шаблон',
    'feedback_emails.placeholders_heading' => 'Доступні плейсхолдери',
    'feedback_emails.placeholders_desc' => 'Ви можете використовувати ці теги в будь-якому місці теми чи тіла листа:',
    'feedback_emails.fixed_tags'         => 'Основні фіксовані теги:',
    'feedback_emails.custom_tags'        => 'Користувацькі теги схеми:',
    'feedback_emails.custom_tags_desc'   => 'Генеруються автоматично з полів конструктора форми тікетів:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Керування схемою форми зворотного зв’язку',
    'feedback_schema.subheading'             => 'Налаштуйте користувацькі поля, типи даних, обмеження довжини, підтипи, опції та відображення.',
    'feedback_schema.settings_summary'       => 'Налаштування заголовка форми та тексту відмови від відповідальності',
    'feedback_schema.form_title_label'       => 'Заголовок форми:',
    'feedback_schema.form_intro_label'       => 'Вступний текст / Опис:',
    'feedback_schema.save_settings_btn'      => 'Зберегти налаштування форми',
    'feedback_schema.edit_field_title'       => 'Редагувати поле тікета:',
    'feedback_schema.add_field_title'        => '+ Додати нове поле форми тікета',
    'feedback_schema.field_name_label'       => 'Мітка / Назва поля:',
    'feedback_schema.data_type_label'        => 'Тип даних:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Короткий текст)',
    'feedback_schema.type_text'              => 'TEXT (Довгий абзац / Повідомлення)',
    'feedback_schema.type_int'               => 'INT (Ціле число)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Прапорець Так/Ні)',
    'feedback_schema.type_date'              => 'DATE (Календарна дата)',
    'feedback_schema.subtype_label'          => 'Підтип поля / Стиль рендерингу введення:',
    'feedback_schema.subtype_standard'       => '-- Стандартний --',
    'feedback_schema.subtype_standard_lower'=> 'стандартний',
    'feedback_schema.options_label'          => 'Опції (через кому або по одній в рядок):',
    'feedback_schema.options_help'           => 'Вкажіть опції, розділені комами або переносів рядків.',
    'feedback_schema.allow_multiple'         => 'Дозволити вибір кількох варіантів (Множинний вибір)',
    'feedback_schema.boolean_format'         => 'Формат відображення булевого значення:',
    'feedback_schema.max_length_label'       => 'Максимальна довжина / Ліміт символів (необов’язково):',
    'feedback_schema.is_required_label'      => 'Зробити це поле обов’язковим для відправників',
    'feedback_schema.save_field_btn'         => 'Зберегти зміни поля',
    'feedback_schema.create_field_btn'       => 'Створити поле тікета',
    'feedback_schema.sub_email'              => 'Електронна пошта',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Випадаючий список',
    'feedback_schema.sub_radio'              => 'Група радіокнопок',
    'feedback_schema.sub_checkbox'           => 'Прапорець (Чекбокс)',
    'feedback_schema.sub_textarea'           => 'Багаторядкове текстове поле',
    'feedback_schema.sub_number'             => 'Числове введення',
    'feedback_schema.existing_fields_heading'=> 'Існуючі поля тікета',
    'feedback_schema.th_move'                => 'Перемістити',
    'feedback_schema.th_field_name'          => 'Назва поля',
    'feedback_schema.th_data_type'           => 'Тип даних',
    'feedback_schema.th_subtype'             => 'Підтип',
    'feedback_schema.th_required'            => 'Обов’язкове?',
    'feedback_schema.th_max_length'          => 'Макс. довжина',
    'feedback_schema.th_created_by'          => 'Створено ким',
    'feedback_schema.no_fields'              => 'Користувацькі поля тікетів ще не визначено.',
    'feedback_schema.system_user'            => 'Система',
    'feedback_schema.edit_btn'               => 'Редагувати',
    'feedback_schema.delete_confirm'         => 'Видалити це поле та всі пов’язані значення відповідей?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Керування таблицями та схемами',
    'manage_tables.subheading'           => 'Безпечно створюйте, перевіряйте, редагуйте або видаляйте динамічні таблиці додатка та їхні схеми колонок.',
    'manage_tables.switcher_label'       => 'Виберіть активну схему таблиці:',
    'manage_tables.edit_metadata_btn'    => 'Редагувати метадані таблиці',
    'manage_tables.delete_table_confirm'=> 'ПОПЕРЕДЖЕННЯ: Видалення цієї таблиці призведе до безповоротного видалення всіх колонок та збереженого вмісту. Абсолютно впевнені?',
    'manage_tables.delete_table_btn'     => 'Видалити таблицю',
    'manage_tables.edit_table_summary'   => 'Редагувати визначення таблиці:',
    'manage_tables.create_table_summary'=> '+ Створити нову динамічну таблицю',
    'manage_tables.table_name_label'     => 'Зрозуміла назва таблиці:',
    'manage_tables.table_desc_label'     => 'Опис / Призначення:',
    'manage_tables.save_table_btn'       => 'Зберегти зміни таблиці',
    'manage_tables.create_table_btn'     => 'Створити схему таблиці',
    'manage_tables.edit_col_summary'     => 'Редагувати динамічну колонку:',
    'manage_tables.add_col_summary_prefix' => '+ Додати нову колонку таблиці для',
    'manage_tables.col_name_label'       => 'Назва колонки:',
    'manage_tables.type_text_long'       => 'TEXT (Довгий абзац)',
    'manage_tables.date_behavior_label' => 'Поводження пошуку за датою:',
    'manage_tables.date_bhv_manual'      => 'Дата бази даних (лише ручне введення)',
    'manage_tables.date_bhv_admin'       => 'Лише дати адміністратора',
    'manage_tables.date_bhv_all'         => 'Усі дати, включаючи дати адміністратора',
    'manage_tables.req_toggle_label'     => 'Зробити цю колонку обов’язковою (примусове введення даних)',
    'manage_tables.exclude_search_label'=> 'Виключити цю колонку з публічного пошуку (index.php)',
    'manage_tables.create_col_btn'       => 'Створити колонку',
    'manage_tables.existing_cols_heading_prefix' => 'Існуючі колонки для',
    'manage_tables.th_public_search'     => 'Публічний пошук?',
    'manage_tables.th_display_format'    => 'Формат відображення',
    'manage_tables.th_date_created'      => 'Дата створення',
    'manage_tables.no_columns_found'     => 'Для цієї таблиці ще не визначено динамічних колонок.',
    'manage_tables.status_hidden'        => 'Приховано',
    'manage_tables.delete_col_confirm'   => 'ПОПЕРЕДЖЕННЯ: Видалення цієї колонки також видалить усі пов’язані дані комірок у кожному записі. Ви впевнені?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Керування шаблонами сповіщень користувачів',
    'user_emails.subheading'             => 'Налаштуйте макети листів, що надсилаються під час запрошення користувачів або надсилання посилань для скидання пароля.',
    'user_emails.select_template_label'=> 'Виберіть шаблон для редагування:',
    'user_emails.opt_invitation'         => 'Шаблон запрошення облікового запису користувача',
    'user_emails.opt_reset'              => 'Шаблон скидання пароля / посилання доступу',
    'currently_editing'                  => 'Зараз редагується:',
    'user_emails.desc_invitation'        => 'Надсилається автоматично, коли адміністратор створює або запрошує нового користувача.',
    'user_emails.desc_reset'             => 'Надсилається під час запиту скидання пароля або повторного надсилання посилання доступу.',
    'user_emails.email_body_label'       => 'Тіло листа:',
    'user_emails.back_to_creation'       => 'Повернутися до створення користувача',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Шаблони листів для волонтерів та тригери',
    'volunteer_emails.subheading'        => 'Налаштуйте автоматичні відповіді електронною поштою для волонтерів на різних етапах робочого процесу. Використовуйте фігурні дужки для динамічних значень.',
    'volunteer_emails.back_to_dashboard'=> 'Повернутися до заявок волонтерів',
    'volunteer_emails.custom_tags_desc'  => 'Генеруються автоматично з полів конструктора форми:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Керування схемою форми волонтерів',
    'volunteer_schema.subheading'        => 'Налаштуйте користувацькі поля, типи даних, підтипи, опції та загальні налаштування відображення форми.',
    'volunteer_schema.back_to_dashboard'=> 'Повернутися до заявок волонтерів',
    'volunteer_schema.settings_summary'  => 'Налаштування заголовка форми та тексту відмови від відповідальності',
    'volunteer_schema.edit_field_title'  => 'Редагувати поле:',
    'volunteer_schema.add_field_title'   => '+ Додати нове поле форми волонтера',
    'volunteer_schema.create_field_btn'  => 'Створити поле',
    'volunteer_schema.existing_fields_heading' => 'Існуючі поля форми волонтерів',
    'volunteer_schema.no_fields'         => 'Користувацькі поля волонтерів ще не визначено.',
    'volunteer_schema.delete_confirm'    => 'Видалити це поле та всі пов’язані значення відповідей?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Перевірка черги модерації та пропозицій',
    'moderate.subheading'                => 'Порівняйте зміни, запропоновані користувачами, з активними записами у ваших дозволених таблицях. Затверджуйте, перевизначайте або відхиляйте пропозиції.',
    'moderate.shortcut_label'            => 'Підказка комбінацій клавіш:',
    'moderate.shortcut_desc'             => 'Натисніть Ctrl + Enter для швидкого схвалення або Esc для очищення поля перевизначення!',
    'moderate.th_id_date'                => 'ID / Дата',
    'moderate.th_table_record'           => 'Таблиця, запис та колонка',
    'moderate.th_comparison'             => 'Порівняння (Активне vs Пропоноване) та Обґрунтування',
    'moderate.th_actions'                => 'Дії модератора',
    'moderate.no_suggestions'            => 'У ваших дозволених таблицях модерації не знайдено очікуваних пропозицій.',
    'moderate.by_label'                  => 'Ким:',
    'moderate.guest_user'                => 'Гість / Відвідувач',
    'moderate.record_id_label'           => 'ID запису:',
    'moderate.column_label'              => 'Колонка:',
    'moderate.required_badge'            => 'Обов’язково',
    'moderate.live_value_label'          => 'Поточне активне значення:',
    'moderate.empty_placeholder'         => '[Порожньо]',
    'moderate.proposed_value_label'      => 'Пропонована зміна:',
    'moderate.evidence_label'            => 'Обґрунтування / Джерело:',
    'moderate.no_evidence'               => 'Обґрунтування чи джерело не надано.',
    'moderate.override_label'            => 'Перевизначити значення:',
    'moderate.select_placeholder'        => '-- Вибрати --',
    'moderate.historical_dates_title'    => 'Підтримувані історичні дати',
    'moderate.approve_confirm'           => 'Затвердити та застосувати це значення?',
    'moderate.decline_confirm'           => 'Відхилити та відкинути цю пропозицію?',
    'moderate.approve_btn'               => 'Затвердити',
    'moderate.decline_btn'               => 'Відхилити',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Менеджер сповіщень та оголошень сайту',
    'notices.subheading'                 => 'Створюйте динамічні оповіщення, привітальні банери або цільові оголошення для певних ролей користувачів.',
    'notices.error_blank'                => 'Заголовок та вміст не можуть бути порожніми.',
    'notices.msg_created'                => 'Сповіщення успішно створено!',
    'notices.msg_deleted'                => 'Сповіщення видалено.',
    'notices.create_heading'             => 'Створити нове сповіщення',
    'notices.title_label'                => 'Заголовок / Шапка сповіщення:',
    'notices.content_label'              => 'Вміст сповіщення (дозволено HTML/текст):',
    'notices.target_roles_label'         => 'Цільова аудиторія (виберіть ролі або всі):',
    'notices.role_everyone'              => 'Усі',
    'notices.role_public'                => 'Публічний (Гість)',
    'notices.role_users'                 => 'Користувачі',
    'notices.role_moderators'            => 'Модератори',
    'notices.role_admins'                => 'Адміністратори',
    'notices.dismissible_label'          => "Закривається (містить кнопку закриття 'X')",
    'notices.display_order_label'        => 'Порядок відображення:',
    'notices.publish_btn'                => 'Опублікувати сповіщення',
    'notices.existing_heading'           => 'Активні та існуючі сповіщення',
    'notices.th_order'                   => 'Порядок',
    'notices.th_title'                   => 'Заголовок',
    'notices.th_target_roles'            => 'Цільові ролі',
    'notices.th_dismissible'             => 'Закривається',
    'notices.no_notices'                 => 'Сповіщення ще не створено.',
    'notices.yes'                        => 'Так',
    'notices.no_sticky'                  => 'Ні (Прилипаюче / Sticky)',
    'notices.delete_confirm'             => 'Видалити це сповіщення?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Глобальні налаштування сайту, модулі та дозволи',
    'settings.subheading'                => 'Керуйте основними налаштуваннями, поштовими драйверами, параметрами безпеки/CAPTCHA, модулями функцій, режимом обслуговування, сповіщеннями сайту та матрицею ролей.',
    'settings.tab_core'                  => 'Ядро та Пошта',
    'settings.tab_modules'               => 'Модулі',
    'settings.tab_maintenance'           => 'Обслуговування',
    'settings.tab_notices'               => 'Сповіщення сайту',
    'settings.tab_permissions'           => 'Ролі та Дозволи',
    'settings.tab_audit'                 => 'Журнал аудиту',
    'settings.db_updates_heading'        => 'Оновлення бази даних',
    'settings.schema_current'            => 'Поточна версія схеми:',
    'settings.schema_latest'             => 'Остання доступна версія:',
    'settings.download_backup_btn'       => 'Завантажити резервну копію БД',
    'settings.download_backup_desc'      => 'Зберігає повний файл .sql на вашому комп’ютері. Збережіть його в безпечному місці перед виконанням оновлень.',
    'settings.schema_update_notice'      => 'Доступні оновлення бази даних. Будь ласка, завантажте резервну копію вище перед продовженням.',
    'settings.migration_confirm'         => 'Ви завантажили резервну копію бази даних? Це застосує очікувані оновлення схеми.',
    'settings.update_db_btn'             => 'Оновити базу даних',
    'settings.schema_uptodate'           => 'База даних актуальна.',
    'settings.core_sys_heading'          => 'Налаштування ядра системи',
    'settings.sys_name_label'            => 'Назва системи / додатка:',
    'settings.default_lang_label'        => 'Мова сайту за замовчуванням:',
    'settings.default_lang_desc'         => 'Використовується для гостей і користувачів, які не вибрали мову. Додайте файли в lang/ (наприклад, uk.php) для підтримки додаткових мов.',
    'settings.captcha_heading'           => 'Налаштування безпеки та CAPTCHA',
    'settings.captcha_provider_label'    => 'Провайдер CAPTCHA:',
    'settings.captcha_none'              => 'Вимкнено (Без CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Налаштування Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Настройки Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Налаштування hCaptcha',
    'settings.site_key_label'            => 'Ключ сайту (Публічний):',
    'settings.secret_key_label'          => 'Секретний ключ (Приватний):',
    'settings.mail_heading'              => 'Налаштування доставки пошти',
    'settings.mail_domain_label'         => 'Поштовий домен системи (Резервний):',
    'settings.mail_from_label'           => "Користувацька адреса 'Від кого' (From):",
    'settings.mail_from_desc'            => 'Виділена адреса, що використовується як відправник для вихідних листів.',
    'settings.mail_driver_label'         => 'Поштовий драйвер / Рушій:',
    'settings.driver_native'             => 'Вбудована пошта (Локальний релей Postfix)',
    'settings.driver_smtp'               => 'Автентифікований SMTP (PHPMailer)',
    'settings.smtp_heading'              => 'Налаштування SMTP-сервера',
    'settings.smtp_host_label'           => 'SMTP Хост:',
    'settings.smtp_port_label'           => 'Порт:',
    'settings.smtp_encryption_label'     => 'Шифрування:',
    'settings.enc_tls'                   => 'TLS (Порт 587)',
    'settings.enc_ssl'                   => 'SSL (Порт 465)',
    'settings.smtp_user_label'           => 'SMTP Ім’я користувача:',
    'settings.smtp_pass_label'           => 'SMTP Пароль (залиште порожнім, щоб зберегти поточний):',
    'settings.save_core_mail_btn'        => 'Зберегти налаштування ядра та пошти',
    'settings.test_mail_heading'         => 'Перевірка поштового налаштування',
    'settings.test_email_label'          => 'Адреса електронної пошти одержувача:',
    'settings.send_test_btn'             => 'Надіслати тестовий лист',
    'settings.modules_heading'           => 'Перемикачі модулів додатка та контроль ефективності',
    'settings.modules_subheading'        => 'Увімкніть або вимкніть функції для оптимізації продуктивності виконання додатка та адаптації під конкретні вимоги розгортання.',
    'settings.mod_users'                 => 'Керування користувачами та багатокористувацький доступ',
    'settings.mod_users_desc'            => 'Включає реєстрацію, керування користувачами та багатокористувацьку автентифікацію.',
    'settings.mod_leaderboard'           => 'Таблиця лідерів та гейміфікація',
    'settings.mod_leaderboard_desc'      => 'Враховує зусилля з транскрипції та нараховує зірочки-бали.',
    'settings.mod_leaderboard_note'      => '(Потрібне Керування користувачами та багатокористувацький доступ)',
    'settings.mod_moderation'            => 'Робочий процес модерації',
    'settings.mod_moderation_desc'       => 'Включає перевірку пропозицій правок та чергу модерації.',
    'settings.mod_volunteers'            => 'Портал волонтерів та заявки',
    'settings.mod_volunteers_desc'       => 'Включає публічну форму волонтерства та панель адміністратора.',
    'settings.mod_feedback'              => 'Надсилання зворотного зв’язку',
    'settings.mod_feedback_desc'         => 'Включає публічну форму зворотного зв’язку та відповідну панель адміністратора.',
    'settings.save_modules_btn'          => 'Зберегти конфігурацію модулів',
    'settings.maintenance_heading'       => 'Режим обслуговування системи',
    'settings.maintenance_toggle'        => 'Увімкнути режим обслуговування (перевести сайт в офлайн)',
    'settings.maintenance_reason_label'  => 'Причина / Повідомлення для користувачів:',
    'settings.maintenance_eta_label'     => 'Орієнтовний час відновлення (ETA):',
    'settings.save_maintenance_btn'      => 'Зберегти налаштування обслуговування',
    'settings.notices_heading'           => 'Сповіщення та оголошення сайту',
    'settings.add_notice_btn'            => '+ Додати нове сповіщення',
    'settings.no_notices'                => 'Сповіщення не налаштовано.',
    'settings.status_active'             => 'Активно',
    'settings.status_inactive'           => 'Неактивно',
    'settings.notice_content_label'      => 'Вміст:',
    'settings.save_notice_btn'           => 'Зберегти сповіщення',
    'settings.permissions_heading'       => 'Матриця ролей та динамічних дозволів',
    'settings.permissions_subheading'    => 'Дозволи згрупповані за функціями системи. Розгорніть розділи для налаштування можливостей та збережіть матрицю нижче.',
    'settings.th_role'                   => 'Роль',
    'settings.th_capabilities'           => 'Можливості, призначені цій групі',
    'settings.save_permissions_btn'      => 'Зберегти матрицю дозволів',
    'settings.audit_heading'             => 'Перегляд журналу аудиту системи',
    'settings.audit_subheading'          => 'Перевіряйте зареєстровані дії безпеки, введення даних та модерацію. За потреби очищайте журнали за допомогою параметрів обслуговування нижче.',
    'settings.purge_all_confirm'         => '⚠️ ПОПЕРЕДЖЕННЯ: Це БЕЗПОВОРОТНО ВИДАЛИТЬ УСІ ЖУРНАЛИ АУДИТУ СИСТЕМИ. Ви впевнені?',
    'settings.clear_all_audit_btn'       => 'Очистити всі журнали аудиту',
    'settings.purge_records_confirm'     => 'Ви впевнені, що хочете очистити всі журнали аудиту, пов’язані із записами?',
    'settings.clear_records_audit_btn'   => 'Очистити лише аудит записів',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Мітка часу',
    'settings.th_actor'                  => 'Суб’єкт',
    'settings.th_action'                 => 'Дія',
    'settings.th_record_id'              => 'ID запису',
    'settings.th_details'                => 'Деталі',
    'settings.th_ip'                     => 'IP-адреса',
    'settings.no_audit_logs'             => 'Журналів аудиту не знайдено.',
    'settings.system_guest'              => 'Система / Гість',
    'settings.audit_limit_note'          => 'Показано останні 250 записів журналу аудиту.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Керування обліковими записами та модерація таблиці лідерів',
    'admin_users.subheading'             => 'Перевіряйте статус користувачів, призначайте ролі, перезаписуйте адреси електронної пошти, ініціюйте скидання паролів або повторні запрошення, скидайте 2FA або блокуйте облікові записи.',
    'admin_users.manage_templates_btn'   => 'Керувати шаблонами листів',
    'admin_users.invite_user_btn'        => 'Запросити нового користувача',
    'admin_users.th_username'            => 'Ім’я користувача',
    'admin_users.th_email_override'      => 'Email та перевизначення',
    'admin_users.th_role_assignment'     => 'Призначення ролі',
    'admin_users.th_score'               => 'Бали',
    'admin_users.th_status'              => 'Статус',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Дії та модерація',
    'admin_users.no_users'               => 'Користувачів не знайдено.',
    'admin_users.save_email_title'       => 'Зберегти нову адресу електронної пошти',
    'admin_users.verified_label'         => 'Підтверджено:',
    'admin_users.yes'                    => 'Так',
    'admin_users.no'                     => 'Ні',
    'admin_users.protected_admin'        => 'Захищений головний адміністратор',
    'admin_users.update_btn'             => 'Оновити',
    'admin_users.status_active'          => 'Активний',
    'admin_users.status_suspended'       => 'Заблокований',
    'admin_users.enabled'                => 'Увімкнено',
    'admin_users.disabled'               => 'Вимкнено',
    'admin_users.set_score_btn'          => 'Встановити бали',
    'admin_users.resend_invite_confirm' => 'Повторно надіслати запрошення електронною поштою цьому користувачу?',
    'admin_users.resend_invite_btn'      => 'Повторити запрошення',
    'admin_users.reset_pwd_confirm'      => 'Надіслати посилання для скидання пароля цьому користувачу?',
    'admin_users.reset_password_btn'     => 'Скинути пароль',
    'admin_users.suspend_confirm'        => 'Заблокувати користувача та відкликати доступ через порушення правил?',
    'admin_users.suspend_btn'            => 'Заблокувати',
    'admin_users.reactivate_btn'         => 'Розблокувати',
    'admin_users.reset_2fa_confirm'      => 'Скинути та вимкнути 2FA для цього користувача?',
    'admin_users.reset_2fa_btn'          => 'Скинути 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Повернутися до панелі тікетів',
    'view_ticket.ticket_heading_prefix'=> 'Тікет',
    'view_ticket.support_request'      => 'Запит до служби підтримки',
    'view_ticket.submitted_by'         => 'Відправник:',
    'view_ticket.on_date'              => 'дата',
    'view_ticket.submitted_fields'     => 'Надіслані поля форми:',
    'view_ticket.ticket_status_label'  => 'Статус тікета:',
    'view_ticket.status_pending'       => 'Очікування',
    'view_ticket.status_progress'      => 'В процесі',
    'view_ticket.status_completed'     => 'Завершено',
    'view_ticket.status_rejected'      => 'Відхилено',
    'view_ticket.dialogue_heading'     => 'Гілка обговорення',
    'view_ticket.no_replies'           => 'Відповідей поки немає.',
    'view_ticket.admin_label'          => 'Адміністратор',
    'view_ticket.staff'                => 'Співробітник',
    'view_ticket.post_reply_heading'   => 'Опублікувати відповідь та сповістити відправника',
    'view_ticket.reply_placeholder'    => 'Напишіть вашу відповідь тут...',
    'view_ticket.send_reply_btn'       => 'Надіслати відповідь та лист відправнику',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Заявки волонтерів та робочий процес',
    'volunteer_dashboard.subheading'         => 'Перевіряйте заявки, призначайте бесіди, записуйте нотатки співбесід та приймайте кандидатів до системи.',
    'volunteer_dashboard.manage_emails_btn' => 'Керувати шаблонами листів',
    'volunteer_dashboard.manage_schema_btn' => 'Керувати схемою форми',
    'volunteer_dashboard.th_status'          => 'Статус',
    'volunteer_dashboard.th_name'            => 'Ім’я',
    'volunteer_dashboard.th_interview_notes'=> 'Співбесіда / Нотатки',
    'volunteer_dashboard.no_submissions'     => 'Заявок волонтерів не знайдено.',
    'volunteer_dashboard.volunteer_prefix'   => 'Волонтер',
    'volunteer_dashboard.chat_label'         => 'Бесіда:',
    'volunteer_dashboard.notes_label'        => 'Нотатки:',
    'volunteer_dashboard.no_notes'           => 'Нотаток поки немає',
    'volunteer_dashboard.chat_notes_btn'     => 'Бесіда та нотатки',
    'volunteer_dashboard.accept_title'       => 'Прийняти через систему запрошень',
    'volunteer_dashboard.accept_invite_btn'  => 'Прийняти та надіслати запрошення',
    'volunteer_dashboard.delete_confirm'     => 'Видалити цей запис волонтера?',
    'volunteer_dashboard.modal_heading'      => 'Керування співбесідою та нотатками кандидата',
    'volunteer_dashboard.modal_status_label'=> 'Статус заявки:',
    'volunteer_dashboard.status_pending'     => 'Очікує перевірки',
    'volunteer_dashboard.status_chat'        => 'Бесіду призначено',
    'volunteer_dashboard.status_accepted'    => 'Прийнято',
    'volunteer_dashboard.status_rejected'    => 'Відхилено',
    'volunteer_dashboard.modal_date_label'   => 'Дата та час призначеної бесіди:',
    'volunteer_dashboard.modal_notes_label'  => 'Нотатки співбесіди / зустрічі:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Запишіть відгуки щодо бесіди тут...',
    'volunteer_dashboard.save_changes_btn'   => 'Зберегти зміни',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Заборонено: Публічний перегляд не увімкнено.',
    'api_search.error_unauthorized_table' => 'Несанкціонований доступ до таблиці.',
    'api_search.no_records'              => 'У цьому довіднику не знайдено жодного запису.',
    'api_search.history_btn'             => 'Історія',
    'api_search.suggest_edit_btn'        => 'Запропонувати правку',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Повернутися на головну сторінку',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ------------------------------------------------------------------
    'feedback.hp_label'              => 'Залиште порожнім',
    'feedback.first_name_label'      => 'Ім’я:',
    'feedback.surname_label'         => 'Прізвище:',
    'feedback.email_label'           => 'Адреса електронної пошти:',
    'feedback.subject_label'         => 'Тема / Заголовок питання:',
    'feedback.required_title'        => 'Обов’язкове поле',
    'feedback.select_placeholder'    => '-- Вибрати --',
    'feedback.multi_select_hint'     => 'Утримуйте Ctrl або Cmd для вибору кількох.',
    'feedback.submit_btn'            => 'Надіслати тікет',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ---------------------------------------------------               -------
    'security_engine.err_suspicious_agent' => 'Помилка безпеки: Підозрілий підпис клієнта.',
    'security_engine.err_access_denied'    => 'Помилка безпеки: Доступ заборонено.',
    'security_engine.err_rate_limit'       => 'Забагато запитів з цієї IP-адреси. Будь ласка, спробуйте пізніше.',
    'security_engine.err_excessive_links'  => 'Надсилання відхилено через надмірну кількість посилань.',
    'security_engine.err_complete_captcha' => 'Будь ласка, пройдіть перевірку безпеки CAPTCHA.',
    'security_engine.err_captcha_failed'   => 'Перевірка CAPTCHA не вдалася. Будь ласка, спробуйте ще раз.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Встановлення завершено',
    'install.complete_heading'           => 'Встановлення завершено',
    'install.complete_desc'              => 'Цей сайт уже налаштовано. Інсталятор заблоковано для запобігання повторного запуску.',
    'install.login_link'                 => 'Увійти',
    'install.home_link'                  => 'Перейти на сайт',
    'install.delete_folder_hint'         => 'Для підвищеної безпеки ви можете видалити або перейменувати папку <code>install</code>.',
    'install.msg_db_ready'               => 'База даних готова. Створіть свій обліковий запис адміністратора для завершення.',
    'install.err_config_load'            => 'Не вдалося використати наявну конфігурацію:',
    'install.err_write_permission'       => 'PHP не може створювати файли в цій папці проєкту.',
    'install.detail_prefix'              => 'Деталі:',
    'install.err_db_required'            => 'Назва бази даних та ім’я користувача бази даних є обов’язковими.',
    'install.err_db_not_empty'           => 'Ця база даних не порожня. Використовуйте нову порожню базу даних (або очистіть усі таблиці) та спробуйте ще раз.',
    'install.msg_schema_imported'        => 'Базу даних підключено, схему імпортовано. Створіть свій обліковий запис адміністратора.',
    'install.err_complete_db_first'      => 'Будь ласка, спершу завершіть крок бази даних.',
    'install.err_admin_required'         => 'Усі поля адміністратора є обов’язковими.',
    'install.err_invalid_email'          => 'Недійсна адреса електронної пошти.',
    'install.err_password_length'        => 'Пароль має містити щонайменше 8 символів.',
    'install.err_passwords_match'        => 'Паролі не збігаються.',
    'install.err_admin_save_failed'      => 'Не вдалося зберегти обліковий запис адміністратора. Перевірте структуру таблиці користувачів.',
    'install.msg_installation_complete' => 'Встановлення завершено.',
    'install.page_title'                 => 'Встановлення — Довідник парафіяльних записів',
    'install.heading'                    => 'Встановлення',
    'install.subheading'                 => 'Початкове налаштування <strong>лише для цієї папки додатка</strong>. Використовуйте порожню базу даних MySQL.',
    'install.done_heading'               => 'Готово',
    'install.done_message'               => 'Встановлення завершено. Інсталятор заблоковано.',
    'install.admin_heading'              => 'Обліковий запис адміністратора сайту',
    'install.admin_subheading'           => 'Це дані для входу на <strong>цей сайт</strong> (не обліковий запис бази даних).',
    'install.admin_username_label'       => 'Ім’я користувача адміністратора',
    'install.admin_email_label'          => 'Email адміністратора',
    'install.admin_password_label'       => 'Пароль адміністратора (мін. 8 символів)',
    'install.admin_confirm_password_label' => 'Підтвердьте пароль адміністратора',
    'install.finish_btn'                 => 'Завершити встановлення',
    'install.db_heading'                 => 'Підключення до бази даних',
    'install.db_hint'                    => 'Використовуйте дані MySQL з вашої <strong>панелі керування хостингом</strong>. Це не дані для входу адміністратора сайту.',
    'install.db_host_label'              => 'Хост бази даних',
    'install.db_name_label'              => 'Назва бази даних',
    'install.db_user_label'              => 'Ім’я користувача бази даних',
    'install.db_pass_label'              => 'Пароль бази даних',
    'install.db_submit_btn'              => 'Створити таблиці та продовжити',
    'install.req_heading'                => '1. Вимоги',
    'install.req_php'                    => 'PHP 8.0+ (виявлено %s)',
    'install.req_pdo'                    => 'Розширення PDO MySQL',
    'install.req_logs'                   => 'Папка журналів із правами запису (або папка проєкту)',
    'install.req_probe'                  => 'Можливість створення файлів у цій папці проєкту',
    'install.continue_btn'               => 'Продовжити',
    'install.req_fail_msg'               => 'Будь ласка, виправте помилки перевірок та перезавантажте цю сторінку.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Перегляд таблиці лідерів',
    'leaderboard.heading'         => 'Таблиця лідерів участі спільноти',
    'leaderboard.subheading'      => 'Визнання заслуг членів нашої спільноти, які допомагають збирати, транскрибувати або керувати записами бази даних.',
    'leaderboard.th_rank'         => 'Ранг',
    'leaderboard.th_contributor'  => 'Учасник',
    'leaderboard.th_role'         => 'Роль',
    'leaderboard.th_score'        => 'Бали',
    'leaderboard.no_users'        => 'У таблиці лідерів поки не знайдено активних користувачів.',
    'leaderboard.medal_gold'      => 'Золота медаль',
    'leaderboard.medal_silver'    => 'Срібна медаль',
    'leaderboard.medal_bronze'    => 'Бронзова медаль',
    'leaderboard.medal_ribbon'    => 'Стрічка 4-го рівня',
    'leaderboard.medal_rosette'   => 'Розетка 5-го рівня',
    'leaderboard.medal_trophy'    => 'Трофей 6-го рівня',
    'leaderboard.medal_star'      => 'Зірка 7-го рівня',
    'leaderboard.medal_military'  => 'Військова медаль 8-го рівня',
    'leaderboard.medal_glowing'   => 'Сяюча зірка 9-го рівня',
    'leaderboard.medal_crown'     => 'Корона 10-го рівня',
    'leaderboard.you_badge'       => '(Ви)',
    'leaderboard.default_role'    => 'Користувач',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Парафіяльні записи складено із загальнодоступних історичних джерел.',
    'footer.software_notice'  => 'Платформа програмного забезпечення з відкритим кодом під ліцензією MIT.',
    'footer.rights_reserved'  => 'Усі права захищено.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'База даних парафіяльних записів',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Закрити сповіщення',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Запис не вказано.',
    'record_history.exit_not_found'        => 'Запис не знайдено.',
    'record_history.heading_prefix'        => 'Історія та журнал аудиту: Запис',
    'record_history.return_btn'            => 'Назад',
    'record_history.directory_table_label'=> 'Таблиця довідника:',
    'record_history.subheading_lifecycle' => 'Відображає соціальний життєвий цикл змін, пропозицій та доказів, безпосередньо пов’язаних із цим записом.',
    'record_history.snapshot_heading'      => 'Знімок поточних активних значень',
    'record_history.empty_value'           => '[Порожньо]',
    'record_history.timeline_heading'      => 'Хронологія подій та дій',
    'record_history.no_history'            => 'Для цього запису поки не зареєстровано спеціальних історичних подій аудиту.',
    'record_history.purge_confirm'         => 'Видалити цей конкретний запис журналу аудиту?',
    'record_history.purge_btn'             => 'Очистити журнал',
    'record_history.actor_label'           => 'Суб’єкт:',
    'record_history.system_guest'          => 'Система / Гість',
    'record_history.target_column'         => 'Цільова колонка:',
    'record_history.proposed_value'        => 'Пропоноване значення:',
    'record_history.reasoning_evidence'    => 'Обґрунтування / Джерело:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ---------------------------------------------------               -------
    'update_database.msg_success'      => 'Базу даних успішно оновлено! Застосовано міграцій: %d.',
    'update_database.msg_uptodate'     => 'База даних уже актуальна.',
    'update_database.err_failed'       => 'Міграція не вдалася:',
    'update_database.page_title'       => 'Потрібне оновлення системи — База даних парафіяльних записів',
    'update_database.heading'          => '⚠️ Потрібне оновлення системи',
    'update_database.subheading'       => 'Схема бази даних додатка застаріла і вимагає оновлення схеми перед продовженням звичайної роботи.',
    'update_database.current_version'  => 'Поточна версія схеми:',
    'update_database.latest_version'   => 'Остання доступна версія:',
    'update_database.proceed_login'    => 'Перейти на сторінку входу',
    'update_database.confirm_prompt'   => 'Ви зробили резервну копію бази даних? Натисніть OK для застосування очікуваних оновлень схеми.',
    'update_database.update_btn'       => 'Оновити базу даних зараз',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ---------------------------------------------------               -------
    'authenticate.err_invalid_credentials' => 'Невірні облікові дані або доступ до облікового запису обмежено.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'Обов’язкове поле \'%s\' не може бути порожнім.',
    'save_data_entry.audit_created_prefix' => 'Запис створено в таблиці з ID %d.',
    'save_data_entry.msg_success'          => 'Запис успішно додано!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Виявлено спам. Надсилання відхилено.',
    'save_public_suggestion.err_field_required' => 'Це поле є обов’язковим і не може бути надісланим порожнім.',
    'save_public_suggestion.msg_success'        => 'Вашу пропозицію правки успішно надіслано в чергу модерації на перевірку. Дякуємо!',
    'save_public_suggestion.err_failed_submit'  => 'Не вдалося надіслати пропозицію правки. Будь ласка, спробуйте ще раз.',
    'save_public_suggestion.err_invalid_column' => 'Вказано недійсну колонку.',
    'save_public_suggestion.err_invalid_params' => 'Недійсні параметри надсилання запису.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'РРРР-ММ-ДД (або частковий рік)',
    'data_entry.date_placeholder_dmy' => 'ДД/ММ/РРРР (або частковий рік)',
    'data_entry.date_placeholder_mdy' => 'ММ/ДД/РРРР (або частковий рік)',
    'data_entry.no_tables_heading'    => '⚠️ Таблиці бази даних не знайдено',
    'data_entry.no_tables_desc'       => 'Для введення даних у системі наразі не налаштовано жодної активної таблиці.',
    'data_entry.admin_tables_prompt'  => 'Як адміністратор, перейдіть до розділу <strong>Керування таблицями</strong>, щоб створити таблицю та додати колонку перед введенням записів.',
    'data_entry.go_manage_tables'     => 'Перейти до керування таблицями',
    'data_entry.contact_admin_tables' => 'Зверніться до адміністратора для налаштування таблиць і колонок.',
    'data_entry.no_cols_heading'      => '⚠️ Колонки не налаштовано',
    'data_entry.no_cols_desc'         => 'У системі є таблиці, але для активної таблиці не визначено жодної колонки даних.',
    'data_entry.admin_cols_prompt'    => 'Як адміністратор, перейдіть до розділу <strong>Керування таблицями</strong>, щоб додати принаймні одну колонку.',
    'data_entry.contact_admin_cols'   => 'Зверніться до адміністратора для налаштування колонок цієї таблиці.',
    'data_entry.active_table_label'   => 'Активна таблиця введення даних:',
    'data_entry.add_entry_summary'    => '➕ Додати новий запис даних (Натисніть, щоб розгорнути/згорнути)',
    'data_entry.bool_yes_true'        => 'Так / Істина',
    'data_entry.bool_no_false'        => 'Ні / Хиба',
    'data_entry.bool_male'            => 'Чоловічий',
    'data_entry.bool_female'          => 'Жіночий',
    'data_entry.bool_true'            => 'Істина',
    'data_entry.bool_false'           => 'Хиба',
    'data_entry.bool_tick'            => '✔ (Позначка)',
    'data_entry.bool_cross'           => '✘ (Хрестик)',
    'data_entry.date_title_hint'      => 'Приймаються повні або часткові дати (наприклад, 1842 або 1842-05)',
    'data_entry.enter_value_placeholder' => 'Введіть значення...',
    'data_entry.submit_data_btn'      => 'Надіслати дані',
    'data_entry.shortcuts_tip'        => '💡 Підказка: Натисніть <strong>Ctrl + Enter</strong> для надсилання або <strong>Esc</strong> для очищення поточного поля.',
    'data_entry.dup_heading'          => '⚠️ Попередження про можливий дублікат',
    'data_entry.dup_desc'             => 'Ми знайшли схожі записи в системі:',
    'data_entry.dup_item_format'      => 'ID запису: %d — Значення: %s',
    'data_entry.dup_prompt'           => 'Бажаєте продовжити і все одно зберегти цей дубльований запис?',
    'data_entry.dup_confirm_btn'      => 'Так, підтвердити та зберегти дублікат',
    'data_entry.search_summary'       => '🔍 Пошук та фільтрація існуючих записів (Натисніть, щоб розгорнути/згорнути)',
    'data_entry.date_to_label'        => 'до',
    'data_entry.filter_all_option'    => '-- Усі --',
    'data_entry.filter_placeholder'   => 'Фільтрувати...',
    'data_entry.apply_filters_btn'    => 'Застосувати фільтри пошуку',
    'data_entry.reset_filter_btn'     => 'Скинути фільтр',
    'data_entry.csv_entire_btn'       => 'Завантажити весь CSV',
    'data_entry.json_entire_btn'      => 'Завантажити весь JSON',
    'data_entry.copy_entire_btn'      => 'Скопіювати всю таблицю',
    'data_entry.csv_filtered_btn'     => 'Завантажити відфільтрований CSV',
    'data_entry.json_filtered_btn'     => 'Завантажити відфільтрований JSON',
    'data_entry.copy_filtered_btn'    => 'Скопіювати відфільтровану таблицю',
    'data_entry.clipboard_alert'      => 'Дані таблиці скопійовано до буфера обміну! Ви можете вставити їх у Excel або Google Таблиці.',
    'data_entry.existing_records_heading' => 'Таблиця існуючих записів',
    'data_entry.th_added_by'          => 'Додано ким',
    'data_entry.th_date_created'      => 'Дата створення',
    'data_entry.no_records'           => 'Записів не знайдено.',
    'data_entry.na_value'             => 'Н/Д',
    'data_entry.page_label'           => 'Сторінка:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Відновлення пароля',
    'forgot_password.heading'         => 'Скиньте ваш пароль',
    'forgot_password.subheading'      => 'Введіть адресу електронної пошти вашого облікового запису нижче, і ми надішлемо вам безпечне посилання для скидання пароля.',
    'forgot_password.email_label'     => 'Адреса електронної пошти:',
    'forgot_password.submit_btn'      => 'Надіслати посилання для скидання',
    'forgot_password.back_login_link' => 'Повернутися до входу',

    // ------------------------------------------------------------------
    // User Login
    // ---------------------------------------------------               -------
    'login.aria_region'          => 'Вхід користувача',
    'login.heading'              => 'Вхід до облікового запису',
    'login.username_label'       => 'Ім’я користувача або email:',
    'login.password_label'       => 'Пароль:',
    'login.submit_btn'           => 'Увійти',
    'login.forgot_password_link' => 'Забули пароль?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Ласкаво просимо — Майстер налаштування облікового запису',
    'onboarding.heading'           => 'Ласкаво просимо до команди!',
    'onboarding.subheading'        => 'Перш ніж почати, приділіть хвилину налаштуванню регіональних уподобань відображення та конфіденційності. Ви можете змінити їх у своєму профілі в будь-який час.',
    'onboarding.timezone_label'    => 'Часовий пояс / Регіон:',
    'onboarding.date_format_label' => 'Формат відображення дати:',
    'onboarding.time_format_label' => 'Формат годинника (відображення часу):',
    'onboarding.time_24'          => '24 години (наприклад, 16:07)',
    'onboarding.time_12'          => '12 годин AM/PM (наприклад, 04:07 PM)',
    'onboarding.time_none'        => 'Лише дата (повністю приховати час)',
    'onboarding.attribution_label' => 'Уподобання відображення в таблиці лідерів:',
    'onboarding.attribution_desc1' => 'Керує тим, як ваше ім’я відображається в публічній таблиці лідерів та записах.',
    'onboarding.attr_anon_title'   => 'Анонімно:',
    'onboarding.attr_anon_text'    => 'Показує ініціали та випадковий номер усім.',
    'onboarding.attr_public_title' => 'Публічно:',
    'onboarding.attr_public_text'  => 'Показує ваше повне ім’я всім.',
    'onboarding.attr_vol_title'    => 'Лише волонтерам:',
    'onboarding.attr_vol_text'     => 'Показує ініціали публічно, але ваше повне ім’я увійшовшим волонтерам, модераторам та адміністраторам.',
    'onboarding.attr_opt_anon'     => 'Анонімно (Ініціали та випадковий номер)',
    'onboarding.attr_opt_public'   => 'Публічно (Показувати повне ім’я)',
    'onboarding.attr_opt_vol'      => 'Лише волонтерам',
    'onboarding.submit_btn'        => 'Зберегти налаштування та продовжити',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Керування профілем користувача',
    'profile.heading'              => 'Профіль користувача та безпека',
    'profile.personal_details_heading' => 'Особисті дані',
    'profile.language_label'       => 'Бажана мова:',
    'profile.lang_site_default'    => 'За замовчуванням для сайту',
    'profile.update_details_btn'   => 'Оновити особисті дані',
    'profile.email_heading'        => 'Адреса електронної пошти',
    'profile.current_email_label'  => 'Поточний email:',
    'profile.email_verified'       => '(Підтверджено)',
    'profile.email_unverified'     => '(Не підтверджено — перевірте вхідні)',
    'profile.change_email_label'   => 'Змінити адресу електронної пошти:',
    'profile.aria_new_email'       => 'Нова адреса електронної пошти',
    'profile.update_email_btn'     => 'Оновити email та підтвердити',
    'profile.password_heading'     => 'Змінити пароль',
    'profile.current_password_label' => 'Поточний пароль:',
    'profile.new_password_label'   => 'Новий пароль (мін. 8 символів):',
    'profile.confirm_password_label' => 'Підтвердіть новий пароль:',
    'profile.show_passwords_label' => 'Показувати паролі відкритим текстом',
    'profile.update_password_btn'  => 'Оновити пароль',
    'profile.tfa_heading'          => 'Двофакторна автентифікація (2FA)',
    'profile.tfa_status_label'     => 'Статус:',
    'profile.tfa_enabled'          => 'Увімкнено',
    'profile.tfa_disabled'         => 'Вимкнено',
    'profile.setup_tfa_btn'        => 'Налаштувати Google Authenticator',
    'profile.tfa_active_desc'      => '2FA активно захищає вхід до вашого облікового запису.',
    'profile.backup_codes_heading' => 'Ваші нові захисні резервні коди',
    'profile.download_codes_btn'   => 'Завантажити нові коди як .txt',
    'profile.generate_codes_confirm' => 'Ви впевнені? Це анулює будь-які існуючі резервні коди.',
    'profile.generate_codes_btn'   => 'Згенерувати нові резервні коди',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Реєстрація користувача',
    'register.heading'        => 'Зареєструвати новий обліковий запис',
    'register.username_label' => 'Ім’я користувача:',
    'register.submit_btn'     => 'Зареєструватися',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Токен налаштування недійсний або відсутній.',
    'set_password.exit_expired_token'        => 'Це посилання на пароль недійсне або термін його дії минув.',
    'set_password.proceed_login_btn'         => 'Перейти на сторінку входу',
    'set_password.aria_region'               => 'Встановити пароль',
    'set_password.heading_format'            => 'Встановити пароль для %s',
    'set_password.subheading_format'         => 'Ласкаво просимо до вашого нового облікового запису, %s! Будь ласка, виберіть свій пароль нижче.',
    'set_password.new_password_label'        => 'Новий пароль (мін. 8 символів):',
    'set_password.confirm_password_label'    => 'Підтвердьте пароль:',
    'set_password.show_password_label'       => 'Показати пароль',
    'set_password.save_password_btn'         => 'Зберегти пароль',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => 'Майстер налаштування 2FA',
    'setup_2fa.heading'          => 'Налаштувати Google Authenticator',
    'setup_2fa.subheading'       => 'Відскануйте QR-код нижче за допомогою вашого додатка-автентифікатора.',
    'setup_2fa.qr_alt'           => 'QR-код для налаштування 2FA',
    'setup_2fa.manual_prompt'    => 'Або введіть цей секретний ключ вручну:',
    'setup_2fa.backup_heading'   => 'Екстрені коди відновлення безпеки',
    'setup_2fa.backup_desc'      => 'Зберігайте ці резервні коди в безпечному місці. Кожен код можна використати <strong>лише один раз</strong>, якщо ви втратите доступ до свого додатка:',
    'setup_2fa.download_btn'     => 'Завантажити коди як .txt',
    'setup_2fa.code_label'       => 'Введіть 6-значний код із додатка для перевірки та увімкнення:',
    'setup_2fa.aria_code_input'  => '6-значний код підтвердження',
    'setup_2fa.submit_btn'       => 'Перевірити та увімкнути 2FA',
    'setup_2fa.cancel_link'      => 'Скасувати та повернутися до профілю',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Запропонувати правку',
    'suggest_edit.heading_prefix'       => 'Запропонувати правку для запису',
    'suggest_edit.return_btn'           => 'Повернутися до запису',
    'suggest_edit.success_msg_suffix'   => 'Ви можете надіслати іншу правку нижче або скористатися посиланням повернення вище, коли закінчите.',
    'suggest_edit.current_values_heading' => 'Поточні значення:',
    'suggest_edit.empty_label'          => '(порожньо)',
    'suggest_edit.submit_heading'       => 'Надіслати нове пропоноване значення та джерело',
    'suggest_edit.confirm_prompt'       => 'Ви впевнені, що хочете надіслати цю пропозицію правки на розгляд адміністратору?',
    'suggest_edit.select_column_label'  => 'Виберіть колонку для редагування:',
    'suggest_edit.reasoning_label'      => 'Джерело / Обґрунтування / Нотатки:',
    'suggest_edit.reasoning_placeholder'=> 'Надайте контекст, цитату джерела або причину для цієї зміни...',
    'suggest_edit.submit_btn'           => 'Надіслати пропозицію на розгляд',
    'suggest_edit.proposed_value_label' => 'Пропоноване нове значення:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => 'Перевірка 2FA',
    'verify_2fa.heading'         => 'Двофакторна автентифікація',
    'verify_2fa.subheading'      => 'Введіть 6-значний код із вашого додатка-автентифікатора або резервний захисний код.',
    'verify_2fa.code_label'      => 'Код підтвердження / Захисний код:',
    'verify_2fa.aria_code_input' => 'Введіть код підтвердження або захисний код',
    'verify_2fa.submit_btn'      => 'Перевірити та увійти',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Токен підтвердження не надано.',
    'verify_email.err_invalid_token'    => 'Недійсний токен підтвердження.',
    'verify_email.msg_already_verified' => 'Вашу електронну пошту вже підтверджено. Ви можете увійти.',
    'verify_email.err_expired_token'    => 'Термін дії цього посилання підтвердження минув (перевищено ліміт 24 години). Будь ласка, зареєструйтеся знову або запитуйте нове посилання.',
    'verify_email.msg_success'          => 'Електронну пошту успішно підтверджено! Ваш обліковий запис тепер активний. Перейдіть до входу.',
    'verify_email.err_update_failed'    => 'Сталася помилка під час підтвердження вашої електронної пошти. Спробуйте ще раз.',
    'verify_email.aria_region'          => 'Статус підтвердження email',
    'verify_email.heading'              => 'Статус підтвердження email',
    'verify_email.login_btn'            => 'Натисніть тут для входу',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Форма волонтера',
    'volunteer.honeypot_label'       => 'Залиште це поле порожнім:',
    'volunteer.required_field_title'=> 'Обов’язкове поле',
    'volunteer.multi_select_hint'    => 'Утримуйте Ctrl або Cmd для вибору кількох.',
    'volunteer.submit_btn'           => 'Надіслати заявку волонтера',
];
