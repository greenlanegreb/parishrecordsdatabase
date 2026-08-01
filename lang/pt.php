<?php
// lang/pt.php - Portuguese (Português)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Entrar',
    'nav.logout'                 => 'Sair',
    'nav.feedback'               => 'Feedback',
    'nav.volunteer'              => 'Colabore',
    'nav.leaderboard'            => 'Classificação',
    'nav.search'                 => 'Pesquisar',
    'nav.settings'               => 'Configurações do Site',
    'nav.high_contrast'          => 'Alto Contraste',
    'nav.low_contrast'           => 'Baixo Contraste',
    'nav.welcome'                => 'Bem-vindo,',
    'nav.data_entry'             => 'Entrada de Dados',
    'nav.moderation'             => 'Moderação',
    'nav.invite_user'            => 'Convidar utilizador',
    'nav.manage_users'           => 'Gerir utilizadores',
    'nav.manage_tables'          => 'Gerir tabelas',
    'nav.volunteer_dashboard'    => 'Painel de Voluntários',
    'nav.feedback_dashboard'     => 'Painel de Feedback',
    'nav.leaderboard_score'      => 'Pontuação da Classificação',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Filtros de Pesquisa Multicoluna',
    'search.reset'               => 'Repor pesquisa',
    'search.export_csv'          => 'Descarregar resultados filtrados como CSV',
    'search.no_records'          => 'Nenhum registo encontrado nesta tabela.',
    'search.load_error'          => 'Não foi possível carregar os resultados. Por favor, tente novamente.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Submeter',
    'btn.cancel'                 => 'Cancelar',
    'btn.save'                   => 'Guardar',
    'btn.delete'                 => 'Eliminar',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Obrigado! O seu feedback foi submetido com sucesso.',
    'feedback.error_all_fields'   => 'Todos os campos são obrigatórios.',
    'feedback.error_invalid_email'=> 'Por favor, insira um endereço de e-mail válido.',
    'feedback.error_save_failed'  => 'Ocorreu um erro ao guardar o seu feedback. Por favor, tente novamente.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Nenhuma tabela de base de dados encontrada',
    'index.no_tables_desc'             => 'De momento, o sistema não tem nenhuma tabela de base de dados ativa configurada.',
    'index.admin_create_table_guide'   => 'Como administrador, aceda a <strong>Gerir tabelas</strong> para criar uma tabela e adicione pelo menos uma coluna antes de poder exibir ou introduzir registos.',
    'index.go_to_manage_tables'        => 'Ir para Gerir tabelas',
    'index.contact_admin_tables'       => 'Contacte um administrador para configurar as tabelas e colunas da base de dados.',
    'index.guest_login_tables_guide'   => 'Por favor, <a href=":login_link">inicie sessão</a> como administrador, aceda à secção <strong>Gerir tabelas</strong> para criar uma tabela e, em seguida, adicione pelo menos uma coluna.',
    'index.no_columns_heading'         => 'Nenhuma coluna configurada',
    'index.no_columns_desc'            => 'Existem tabelas no sistema, mas nenhuma coluna de dados foi definida para a tabela ativa.',
    'index.admin_add_columns_guide'    => 'Como administrador, aceda a <strong>Gerir tabelas</strong> para adicionar pelo menos uma coluna à sua tabela.',
    'index.contact_admin_columns'      => 'Contacte um administrador para configurar as colunas desta tabela.',
    'index.select_directory_database'  => 'Selecionar base de dados do diretório:',
    'index.opt_yes_true'               => 'Sim / Verdadeiro',
    'index.opt_no_false'               => 'Não / Falso',
    'index.opt_male'                   => 'Masculino',
    'index.opt_female'                 => 'Feminino',
    'index.opt_true'                   => 'Verdadeiro',
    'index.opt_false'                  => 'Falso',
    'index.opt_tick'                   => '✔ (Marcar)',
    'index.opt_cross'                  => '✘ (Cruz)',
    'index.option_all'                 => '-- Todos --',
    'index.date_to_label'              => 'até',
    'index.search_placeholder'         => 'Pesquisar...',
    'index.download_entire_csv'        => 'Descarregar CSV completo',
    'index.download_entire_json'       => 'Descarregar JSON completo',
    'index.copy_entire_table'          => 'Copiar tabela completa',
    'index.download_filtered_csv'      => 'Descarregar CSV filtrado',
    'index.download_filtered_json'     => 'Descarregar JSON filtrado',
    'index.copy_filtered_table'        => 'Copiar tabela filtrada',
    'index.th_record_id'               => 'ID do Registo',
    'index.th_created_by'              => 'Criado por',
    'index.th_date_added'              => 'Data de adição',
    'index.th_actions'                 => 'Ações',
    'index.modal_heading'              => 'Sugerir correção de registo',
    'index.modal_desc'                 => 'Forneça uma correção ou informação alternativa para este registo. A nossa equipa de moderação irá revê-la.',
    'index.modal_target_column'        => 'Coluna alvo:',
    'index.modal_proposed_value'       => 'Correção / Valor proposto:',
    'index.modal_input_placeholder'    => 'Introduza a informação atualizada...',
    'index.modal_submit_btn'           => 'Submeter sugestão',
    'index.clipboard_success'          => 'Dados da tabela copiados para a área de transferência! Pode colá-los diretamente no Excel ou Google Sheets.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Formulário de convite de novo utilizador',
    'create_user.subheading'           => 'Isto irá gerar um link de configuração seguro válido por 24 horas e enviá-lo diretamente por e-mail para o utilizador.',
    'create_user.first_name'           => 'Nome:',
    'create_user.surname'              => 'Apelido:',
    'create_user.username_label'       => 'Nome de utilizador (Opcional):',
    'create_user.username_placeholder' => 'Deixar em branco para geração automática',
    'create_user.username_help'        => 'Se deixado em branco, será gerado automaticamente um nome de utilizador único com base no nome.',
    'create_user.email_label'          => 'Endereço de e-mail:',
    'create_user.role_label'           => 'Função do utilizador:',
    'create_user.submit_btn'           => 'Criar utilizador e enviar convite',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Painel de tickets de suporte e feedback',
    'feedback_dash.subheading'           => 'Gerir pedidos de suporte públicos, atualizar estados e participar em conversas diretas.',
    'feedback_dash.manage_emails'        => 'Gerir modelos de e-mail',
    'feedback_dash.manage_schema'        => 'Gerir esquema do formulário de tickets',
    'feedback_dash.th_ticket_date'       => 'ID do Ticket / Data',
    'feedback_dash.th_submitter'         => 'Remetente',
    'feedback_dash.th_subject_info'      => 'Assunto / Informação base',
    'feedback_dash.th_status'            => 'Estado',
    'feedback_dash.no_tickets'           => 'Nenhum ticket de feedback encontrado.',
    'feedback_dash.anonymous'            => 'Anónimo',
    'feedback_dash.default_subject'      => 'Consulta geral',
    'feedback_dash.open_ticket_btn'      => 'Abrir ticket e conversa',
    'feedback_dash.delete_confirm'       => 'Eliminar este ticket de suporte e todas as respostas associadas?',
    'feedback_dash.msg_deleted'          => 'Ticket #:id eliminada com sucesso.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Modelos de e-mail para tickets de suporte',
    'feedback_emails.subheading'         => 'Personalize as notificações automáticas por e-mail enviadas durante o fluxo de tickets. Utilize chavetas para marcadores dinâmicos.',
    'feedback_emails.back_to_dashboard' => 'Voltar ao painel de tickets de feedback',
    'feedback_emails.email_subject'      => 'Assunto do e-mail:',
    'feedback_emails.email_body'         => 'Modelo do corpo do e-mail:',
    'feedback_emails.save_template_btn' => 'Guardar modelo',
    'feedback_emails.placeholders_heading' => 'Marcadores disponíveis',
    'feedback_emails.placeholders_desc' => 'Pode utilizar estas etiquetas em qualquer parte do assunto ou corpo:',
    'feedback_emails.fixed_tags'         => 'Etiquetas centrais fixas:',
    'feedback_emails.custom_tags'        => 'Etiquetas de esquema personalizadas:',
    'feedback_emails.custom_tags_desc'   => 'Geradas automaticamente a partir dos campos do criador de formulários de tickets:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Gestão de esquemas de formulários de feedback',
    'feedback_schema.subheading'             => 'Configure campos personalizados, tipos de dados, limites de caracteres, subtipos, opções e definições de apresentação.',
    'feedback_schema.settings_summary'       => 'Configurar título do formulário e texto de isenção de responsabilidade',
    'feedback_schema.form_title_label'       => 'Título do formulário:',
    'feedback_schema.form_intro_label'       => 'Texto de introdução / Descrição:',
    'feedback_schema.save_settings_btn'      => 'Guardar definições do formulário',
    'feedback_schema.edit_field_title'       => 'Editar campo de ticket:',
    'feedback_schema.add_field_title'        => '+ Adicionar novo campo de formulário de ticket',
    'feedback_schema.field_name_label'       => 'Etiqueta / Nome do campo:',
    'feedback_schema.data_type_label'        => 'Tipo de dados:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Texto curto)',
    'feedback_schema.type_text'              => 'TEXT (Parágrafo longo / Mensagem)',
    'feedback_schema.type_int'               => 'INT (Número inteiro)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Sinalizador Sim/Não)',
    'feedback_schema.type_date'              => 'DATE (Data de calendário)',
    'feedback_schema.subtype_label'          => 'Subtipo de campo / Estilo de renderização de entrada:',
    'feedback_schema.subtype_standard'       => '-- Padrão --',
    'feedback_schema.subtype_standard_lower'=> 'padrão',
    'feedback_schema.options_label'          => 'Opções (separadas por vírgulas ou uma por linha):',
    'feedback_schema.options_help'           => 'Forneça opções separadas por vírgulas ou quebras de linha.',
    'feedback_schema.allow_multiple'         => 'Permitir a seleção de várias opções (Seleção múltipla)',
    'feedback_schema.boolean_format'         => 'Formato de exibição booleano:',
    'feedback_schema.max_length_label'       => 'Tamanho máximo / Comprimento (Limite opcional de caracteres):',
    'feedback_schema.is_required_label'      => 'Tornar este campo obrigatório para os remetentes',
    'feedback_schema.save_field_btn'         => 'Guardar alterações do campo',
    'feedback_schema.create_field_btn'       => 'Criar campo de ticket',
    'feedback_schema.sub_email'              => 'E-mail',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Menu pendente',
    'feedback_schema.sub_radio'              => 'Grupo de botões de rádio',
    'feedback_schema.sub_checkbox'           => 'Caixa de verificação',
    'feedback_schema.sub_textarea'           => 'Caixa de texto',
    'feedback_schema.sub_number'             => 'Entrada numérica',
    'feedback_schema.existing_fields_heading'=> 'Campos de ticket existentes',
    'feedback_schema.th_move'                => 'Mover',
    'feedback_schema.th_field_name'          => 'Nome do campo',
    'feedback_schema.th_data_type'           => 'Tipo de dados',
    'feedback_schema.th_subtype'             => 'Subtipo',
    'feedback_schema.th_required'            => 'Obrigatório?',
    'feedback_schema.th_max_length'          => 'Comprimento máximo',
    'feedback_schema.th_created_by'          => 'Criado por',
    'feedback_schema.no_fields'              => 'Ainda não foram definidos campos de ticket personalizados.',
    'feedback_schema.system_user'            => 'Sistema',
    'feedback_schema.edit_btn'               => 'Editar',
    'feedback_schema.delete_confirm'         => 'Eliminar este campo e todos os valores de resposta associados?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Gestão dinâmica de tabelas e esquemas',
    'manage_tables.subheading'           => 'Crie, inspecione, modifique ou elimine em segurança as tabelas dinâmicas da aplicação e respetivos esquemas de colunas.',
    'manage_tables.switcher_label'       => 'Selecionar esquema de tabela ativo:',
    'manage_tables.edit_metadata_btn'    => 'Editar metadados da tabela',
    'manage_tables.delete_table_confirm'=> 'AVISO: A eliminação desta tabela removerá permanentemente todas as colunas e conteúdos guardados. Tem a certeza absoluta?',
    'manage_tables.delete_table_btn'     => 'Eliminar tabela',
    'manage_tables.edit_table_summary'   => 'Editar definição da tabela:',
    'manage_tables.create_table_summary'=> '+ Criar nova tabela dinâmica',
    'manage_tables.table_name_label'     => 'Nome amigável da tabela:',
    'manage_tables.table_desc_label'     => 'Descrição / Objetivo:',
    'manage_tables.save_table_btn'       => 'Guardar alterações da tabela',
    'manage_tables.create_table_btn'     => 'Criar esquema da tabela',
    'manage_tables.edit_col_summary'     => 'Editar coluna dinâmica:',
    'manage_tables.add_col_summary_prefix' => '+ Adicionar nova coluna de tabela para',
    'manage_tables.col_name_label'       => 'Nome da coluna:',
    'manage_tables.type_text_long'       => 'TEXT (Parágrafo longo)',
    'manage_tables.date_behavior_label' => 'Comportamento de pesquisa de datas:',
    'manage_tables.date_bhv_manual'      => 'Datas na base de dados (apenas introdução manual)',
    'manage_tables.date_bhv_admin'       => 'Apenas datas administrativas',
    'manage_tables.date_bhv_all'         => 'Todas as datas, incluindo administrativas',
    'manage_tables.req_toggle_label'     => 'Tornar esta coluna obrigatória (entrada de dados obrigatória)',
    'manage_tables.exclude_search_label'=> 'Excluir esta coluna da pesquisa pública (index.php)',
    'manage_tables.create_col_btn'       => 'Criar coluna',
    'manage_tables.existing_cols_heading_prefix' => 'Colunas existentes para',
    'manage_tables.th_public_search'     => 'Pesquisa pública?',
    'manage_tables.th_display_format'    => 'Formato de exibição',
    'manage_tables.th_date_created'      => 'Data de criação',
    'manage_tables.no_columns_found'     => 'Ainda não existem colunas dinâmicas definidas para esta tabela.',
    'manage_tables.status_hidden'        => 'Oculto',
    'manage_tables.delete_col_confirm'   => 'AVISO: A eliminação desta coluna também removerá todos os dados de células relacionados em cada registo. Tem a certeza?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Gerir modelos de e-mail de notificação de utilizador',
    'user_emails.subheading'             => 'Personalize os layouts de e-mail enviados ao convidar utilizadores ou ao enviar links de redefinição de palavra-passe.',
    'user_emails.select_template_label'=> 'Selecionar modelo para editar:',
    'user_emails.opt_invitation'         => 'Modelo de convite de conta de utilizador',
    'user_emails.opt_reset'              => 'Modelo de redefinição de palavra-passe / link de acesso',
    'currently_editing'                  => 'A editar atualmente:',
    'user_emails.desc_invitation'        => 'Enviado automaticamente quando um administrador cria ou convida um novo utilizador.',
    'user_emails.desc_reset'             => 'Enviado quando é iniciada uma redefinição de palavra-passe ou reenvio de link de acesso.',
    'user_emails.email_body_label'       => 'Corpo do e-mail:',
    'user_emails.back_to_creation'       => 'Voltar à criação de utilizadores',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Modelos de e-mail e gatilhos para voluntários',
    'volunteer_emails.subheading'        => 'Personalize as respostas automáticas por e-mail enviadas aos voluntários durante as diferentes fases do fluxo de trabalho. Utilize chavetas para marcadores dinâmicos.',
    'volunteer_emails.back_to_dashboard'=> 'Voltar às candidaturas de voluntários',
    'volunteer_emails.custom_tags_desc'  => 'Gerados automaticamente a partir dos campos do seu criador de formulários:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Gestão de esquemas de formulários de voluntários',
    'volunteer_schema.subheading'        => 'Configure campos personalizados, tipos de dados, subtipos, opções e definições gerais de apresentação do formulário.',
    'volunteer_schema.back_to_dashboard'=> 'Voltar às candidaturas de voluntários',
    'volunteer_schema.settings_summary'  => 'Configurar título do formulário e texto de isenção de responsabilidade',
    'volunteer_schema.edit_field_title'  => 'Editar campo:',
    'volunteer_schema.add_field_title'   => '+ Adicionar novo campo de formulário de voluntário',
    'volunteer_schema.create_field_btn'  => 'Criar campo',
    'volunteer_schema.existing_fields_heading' => 'Campos de formulário de voluntários existentes',
    'volunteer_schema.no_fields'         => 'Ainda não foram definidos campos de voluntário personalizados.',
    'volunteer_schema.delete_confirm'    => 'Eliminar este campo e todos os valores de resposta associados?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Revisão de sugestões pendentes',
    'moderate.subheading'                => 'Compare as alterações sugeridas pelos utilizadores com os registos ativos nas suas tabelas aprovadas. Aprove, sobrescreva ou rejeite sugestões.',
    'moderate.shortcut_label'            => 'Dica de atalho de teclado:',
    'moderate.shortcut_desc'             => 'Prima Ctrl + Enter para aprovar rapidamente ou Esc para limpar a caixa de sobrescrita!',
    'moderate.th_id_date'                => 'ID / Data',
    'moderate.th_table_record'           => 'Tabela, registo e coluna',
    'moderate.th_comparison'             => 'Comparação (Ativo vs Sugerido) e Evidência',
    'moderate.th_actions'                => 'Ações do Moderador',
    'moderate.no_suggestions'            => 'Nenhuma sugestão pendente encontrada para as suas tabelas de moderação aprovadas.',
    'moderate.by_label'                  => 'Por:',
    'moderate.guest_user'                => 'Espectador / Convidado',
    'moderate.record_id_label'           => 'ID do Registo:',
    'moderate.column_label'              => 'Coluna:',
    'moderate.required_badge'            => 'Obrigatório',
    'moderate.live_value_label'          => 'Valor ativo atual:',
    'moderate.empty_placeholder'         => '[Vazio]',
    'moderate.proposed_value_label'      => 'Alteração sugerida:',
    'moderate.evidence_label'            => 'Evidência / Justificação:',
    'moderate.no_evidence'               => 'Nenhuma evidência ou justificação fornecida.',
    'moderate.override_label'            => 'Sobrescrever valor:',
    'moderate.select_placeholder'        => '-- Selecionar --',
    'moderate.historical_dates_title'    => 'Datas históricas suportadas',
    'moderate.approve_confirm'           => 'Aprovar e aplicar este valor?',
    'moderate.decline_confirm'           => 'Rejeitar e descartar esta sugestão?',
    'moderate.approve_btn'               => 'Aprovar',
    'moderate.decline_btn'               => 'Rejeitar',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Gestor de avisos e anúncios do site',
    'notices.subheading'                 => 'Crie alertas dinâmicos, banners de boas-vindas ou anúncios direcionados para funções de utilizador específicas.',
    'notices.error_blank'                => 'O título e o conteúdo não podem estar em branco.',
    'notices.msg_created'                => 'Aviso criado com sucesso!',
    'notices.msg_deleted'                => 'Aviso eliminado.',
    'notices.create_heading'             => 'Criar novo aviso',
    'notices.title_label'                => 'Título / Cabeçalho do aviso:',
    'notices.content_label'              => 'Conteúdo do aviso (HTML/Texto permitido):',
    'notices.target_roles_label'         => 'Público-alvo (Selecionar funções ou todos):',
    'notices.role_everyone'              => 'Todos',
    'notices.role_public'                => 'Público (Convidado)',
    'notices.role_users'                 => 'Utilizadores',
    'notices.role_moderators'            => 'Moderadores',
    'notices.role_admins'                => 'Administradores',
    'notices.dismissible_label'          => "Dispensável (Inclui botão de fechar 'X')",
    'notices.display_order_label'        => 'Ordem de exibição:',
    'notices.publish_btn'                => 'Publicar aviso',
    'notices.existing_heading'           => 'Avisos ativos e existentes',
    'notices.th_order'                   => 'Ordem',
    'notices.th_title'                   => 'Título',
    'notices.th_target_roles'            => 'Funções-alvo',
    'notices.th_dismissible'             => 'Dispensável',
    'notices.no_notices'                 => 'Ainda não foram criados avisos.',
    'notices.yes'                        => 'Sim',
    'notices.no_sticky'                  => 'Não (Fixo / Sticky)',
    'notices.delete_confirm'             => 'Eliminar este aviso?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Configurações globais do site, módulos e permissões',
    'settings.subheading'                => 'Gerir configurações centrais, controladores de e-mail, opções de segurança/CAPTCHA, módulos de funcionalidades, modo de manutenção, avisos do site e matriz de funções.',
    'settings.tab_core'                  => 'Central e E-mail',
    'settings.tab_modules'               => 'Módulos',
    'settings.tab_maintenance'           => 'Manutenção',
    'settings.tab_notices'               => 'Avisos do Site',
    'settings.tab_permissions'           => 'Funções e Permissões',
    'settings.tab_audit'                 => 'Registo de Auditoria',
    'settings.db_updates_heading'        => 'Atualizações da base de dados',
    'settings.schema_current'            => 'Versão atual do esquema:',
    'settings.schema_latest'             => 'Mais recente disponível:',
    'settings.download_backup_btn'       => 'Descarregar cópia de segurança da base de dados',
    'settings.download_backup_desc'      => 'Guarda um ficheiro .sql completo no seu computador. Guarde-o num local seguro antes de executar atualizações.',
    'settings.schema_update_notice'      => 'Está disponível uma atualização da base de dados. Por favor, descarregue uma cópia de segurança acima antes de prosseguir.',
    'settings.migration_confirm'         => 'Já descarregou uma cópia de segurança da base de dados? Isto aplicará as atualizações de esquema pendentes.',
    'settings.update_db_btn'             => 'Atualizar base de dados',
    'settings.schema_uptodate'           => 'A base de dados está atualizada.',
    'settings.core_sys_heading'          => 'Configurações centrais do sistema',
    'settings.sys_name_label'            => 'Nome do sistema / aplicação:',
    'settings.default_lang_label'        => 'Idioma predefinido do site:',
    'settings.default_lang_desc'         => 'Utilizado para convidados e utilizadores que não selecionaram um idioma. Adicione ficheiros em lang/ (ex: pt.php) para oferecer opções adicionais.',
    'settings.captcha_heading'           => 'Configuração de segurança e CAPTCHA',
    'settings.captcha_provider_label'    => 'Motor fornecedor de CAPTCHA:',
    'settings.captcha_none'              => 'Desativado (Sem CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Configurações do Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Configurações do Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Configurações do hCaptcha',
    'settings.site_key_label'            => 'Chave do Site (Pública):',
    'settings.secret_key_label'          => 'Chave Secreta (Privada):',
    'settings.mail_heading'              => 'Configuração de entrega de e-mail',
    'settings.mail_domain_label'         => 'Domínio de e-mail do sistema (Fallback):',
    'settings.mail_from_label'           => "Endereço de e-mail 'De' personalizado:",
    'settings.mail_from_desc'            => 'Um endereço dedicado utilizado como remetente para e-mails de saída.',
    'settings.mail_driver_label'         => 'Controlador / Motor de e-mail:',
    'settings.driver_native'             => 'E-mail nativo (Post-relay Postfix local)',
    'settings.driver_smtp'               => 'SMTP autenticado (PHPMailer)',
    'settings.smtp_heading'              => 'Configurações do servidor SMTP',
    'settings.smtp_host_label'           => 'Servidor SMTP:',
    'settings.smtp_port_label'           => 'Porta:',
    'settings.smtp_encryption_label'     => 'Criptografia:',
    'settings.enc_tls'                   => 'TLS (Porta 587)',
    'settings.enc_ssl'                   => 'SSL (Porta 465)',
    'settings.smtp_user_label'           => 'Nome de utilizador SMTP:',
    'settings.smtp_pass_label'           => 'Palavra-passe SMTP (deixar em branco para manter a atual):',
    'settings.save_core_mail_btn'        => 'Guardar configurações centrais e de e-mail',
    'settings.test_mail_heading'         => 'Testar configuração de e-mail',
    'settings.test_email_label'          => 'Endereço de e-mail do destinatário:',
    'settings.send_test_btn'             => 'Enviar e-mail de teste',
    'settings.modules_heading'           => 'Interruptores de módulos da aplicação e controlos de eficiência',
    'settings.modules_subheading'        => 'Ative ou desative funcionalidades para otimizar a eficiência de execução da aplicação e adaptar o PRD aos seus requisitos de implementação específicos.',
    'settings.mod_users'                 => 'Gestão de utilizadores e acesso multiutilizador',
    'settings.mod_users_desc'            => 'Ativar registo, gestão de utilizadores e autenticação multiutilizador. (O acesso ao perfil permanece disponível para segurança de utilizador único).',
    'settings.mod_leaderboard'           => 'Classificação e Gamificação',
    'settings.mod_leaderboard_desc'      => 'Reconhece esforços de transcrição e pontuações de estrelas.',
    'settings.mod_leaderboard_note'      => '(Requer gestão de utilizadores e acesso multiutilizador)',
    'settings.mod_moderation'            => 'Fluxo de trabalho de moderação',
    'settings.mod_moderation_desc'       => 'Ativar revisão de sugestões de edição e fila de moderação.',
    'settings.mod_volunteers'            => 'Portal de voluntários e candidaturas',
    'settings.mod_volunteers_desc'       => 'Ativar formulário de interesse público de voluntários e painel de gestão do administrador.',
    'settings.mod_feedback'              => 'Submissões de feedback',
    'settings.mod_feedback_desc'         => 'Ativar formulário de feedback público e painel de gestão correspondente.',
    'settings.save_modules_btn'          => 'Guardar configurações dos módulos',
    'settings.maintenance_heading'       => 'Modo de manutenção do sistema',
    'settings.maintenance_toggle'        => 'Ativar modo de manutenção (Colocar o site offline)',
    'settings.maintenance_reason_label'  => 'Motivo / Mensagem para os utilizadores:',
    'settings.maintenance_eta_label'     => 'Tempo estimado de regresso (ETA):',
    'settings.save_maintenance_btn'      => 'Guardar definições de manutenção',
    'settings.notices_heading'           => 'Avisos e anúncios do site',
    'settings.add_notice_btn'            => '+ Adicionar novo aviso',
    'settings.no_notices'                => 'Nenhum aviso configurado.',
    'settings.status_active'             => 'Ativo',
    'settings.status_inactive'           => 'Inativo',
    'settings.notice_content_label'      => 'Conteúdo:',
    'settings.save_notice_btn'           => 'Guardar aviso',
    'settings.permissions_heading'       => 'Matriz dinâmica de funções e permissões',
    'settings.permissions_subheading'    => 'As permissões estão agrupadas por funções do sistema. Expanda as secções para configurar as capacidades e, em seguida, guarde as suas atualizações abaixo.',
    'settings.th_role'                   => 'Função',
    'settings.th_capabilities'           => 'Capacidades atribuídas neste grupo',
    'settings.save_permissions_btn'      => 'Guardar matriz de permissões',
    'settings.audit_heading'             => 'Explorador de registos de auditoria do sistema',
    'settings.audit_subheading'          => 'Revisão de ações de segurança, introdução de dados e moderação registadas. Utilize as opções de manutenção abaixo para limpar registos, se necessário.',
    'settings.purge_all_confirm'         => '⚠️ AVISO: Isto eliminará TODOS OS REGISTOS DE AUDITORIA DO SISTEMA permanentemente. Tem a certeza de que pretende continuar?',
    'settings.clear_all_audit_btn'       => 'Limpar todos os registos de auditoria',
    'settings.purge_records_confirm'     => 'Tem a certeza de que pretende limpar todas as entradas de auditoria relacionadas com registos?',
    'settings.clear_records_audit_btn'   => 'Limpar apenas registos de registos',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Carimbo de data/hora',
    'settings.th_actor'                  => 'Ator',
    'settings.th_action'                 => 'Ação',
    'settings.th_record_id'              => 'ID do Registo',
    'settings.th_details'                => 'Detalhes',
    'settings.th_ip'                     => 'Endereço IP',
    'settings.no_audit_logs'             => 'Nenhum registo de auditoria encontrado.',
    'settings.system_guest'              => 'Sistema / Convidado',
    'settings.audit_limit_note'          => 'A exibir os últimos 250 registos de auditoria.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Gestão de contas de utilizador e moderação da classificação',
    'admin_users.subheading'             => 'Inspecione o estado dos utilizadores, atribua funções, sobrescreva e-mails, inicie redefinições de palavra-passe ou convites, reinicie o 2FA ou suspenda contas.',
    'admin_users.manage_templates_btn'   => 'Gerir modelos de e-mail',
    'admin_users.invite_user_btn'        => 'Convidar novo utilizador',
    'admin_users.th_username'            => 'Nome de utilizador',
    'admin_users.th_email_override'      => 'E-mail e Sobrescrita',
    'admin_users.th_role_assignment'     => 'Atribuição de função',
    'admin_users.th_score'               => 'Pontuação',
    'admin_users.th_status'              => 'Estado',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Ações e Moderação',
    'admin_users.no_users'               => 'Nenhum utilizador encontrado.',
    'admin_users.save_email_title'       => 'Guardar novo endereço de e-mail',
    'admin_users.verified_label'         => 'Verificado:',
    'admin_users.yes'                    => 'Sim',
    'admin_users.no'                     => 'Não',
    'admin_users.protected_admin'        => 'Administrador principal protegido',
    'admin_users.update_btn'             => 'Atualizar',
    'admin_users.status_active'          => 'Ativo',
    'admin_users.status_suspended'       => 'Suspenso',
    'admin_users.enabled'                => 'Ativado',
    'admin_users.disabled'               => 'Desativado',
    'admin_users.set_score_btn'          => 'Definir pontuação',
    'admin_users.resend_invite_confirm' => 'Reenviar o e-mail de convite da conta para este utilizador?',
    'admin_users.resend_invite_btn'      => 'Reenviar convite',
    'admin_users.reset_pwd_confirm'      => 'Enviar um link de redefinição de palavra-passe para este utilizador?',
    'admin_users.reset_password_btn'     => 'Redefinir palavra-passe',
    'admin_users.suspend_confirm'        => 'Suspender o utilizador e revogar o acesso devido a abuso/violação?',
    'admin_users.suspend_btn'            => 'Suspender',
    'admin_users.reactivate_btn'         => 'Reativar',
    'admin_users.reset_2fa_confirm'      => 'Redefinir e desativar o 2FA para este utilizador?',
    'admin_users.reset_2fa_btn'          => 'Redefinir 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Voltar ao painel de tickets',
    'view_ticket.ticket_heading_prefix'=> 'Ticket',
    'view_ticket.support_request'      => 'Pedido de suporte',
    'view_ticket.submitted_by'         => 'Submetido por:',
    'view_ticket.on_date'              => 'em',
    'view_ticket.submitted_fields'     => 'Campos do formulário submetidos:',
    'view_ticket.ticket_status_label'  => 'Estado do ticket:',
    'view_ticket.status_pending'       => 'Pendente',
    'view_ticket.status_progress'      => 'Em progresso',
    'view_ticket.status_completed'     => 'Concluído',
    'view_ticket.status_rejected'      => 'Rejeitado',
    'view_ticket.dialogue_heading'     => 'Tópico da conversa',
    'view_ticket.no_replies'           => 'Ainda não existem respostas registadas.',
    'view_ticket.admin_label'          => 'Administrador',
    'view_ticket.staff'                => 'Equipa',
    'view_ticket.post_reply_heading'   => 'Publicar resposta e notificar o remetente',
    'view_ticket.reply_placeholder'    => 'Escreva a sua resposta aqui...',
    'view_ticket.send_reply_btn'       => 'Enviar resposta e notificar o remetente por e-mail',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Candidaturas de voluntários e fluxo de trabalho',
    'volunteer_dashboard.subheading'         => 'Analisar candidaturas, agendar conversas com voluntários, tomar notas de entrevistas e integrar candidatos no sistema.',
    'volunteer_dashboard.manage_emails_btn' => 'Gerir modelos de e-mail',
    'volunteer_dashboard.manage_schema_btn' => 'Gerir esquema do formulário',
    'volunteer_dashboard.th_status'          => 'Estado',
    'volunteer_dashboard.th_name'            => 'Nome',
    'volunteer_dashboard.th_interview_notes'=> 'Entrevista / Notas',
    'volunteer_dashboard.no_submissions'     => 'Nenhuma candidatura de voluntário encontrada.',
    'volunteer_dashboard.volunteer_prefix'   => 'Voluntário',
    'volunteer_dashboard.chat_label'         => 'Conversa:',
    'volunteer_dashboard.notes_label'        => 'Notas:',
    'volunteer_dashboard.no_notes'           => 'Ainda sem notas',
    'volunteer_dashboard.chat_notes_btn'     => 'Conversa e Notas',
    'volunteer_dashboard.accept_title'       => 'Aceitar no sistema de convite de utilizadores',
    'volunteer_dashboard.accept_invite_btn'  => 'Aceitar e enviar convite',
    'volunteer_dashboard.delete_confirm'     => 'Eliminar este registo de voluntário?',
    'volunteer_dashboard.modal_heading'      => 'Gerir entrevista e notas do candidato',
    'volunteer_dashboard.modal_status_label'=> 'Estado da candidatura:',
    'volunteer_dashboard.status_pending'     => 'Revisão pendente',
    'volunteer_dashboard.status_chat'        => 'Conversa agendada',
    'volunteer_dashboard.status_accepted'    => 'Aceite',
    'volunteer_dashboard.status_rejected'    => 'Rejeitado',
    'volunteer_dashboard.modal_date_label'   => 'Data e hora da conversa / entrevista agendada:',
    'volunteer_dashboard.modal_notes_label'  => 'Notas da entrevista / reunião:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Registe o feedback da conversa aqui...',
    'volunteer_dashboard.save_changes_btn'   => 'Guardar alterações',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Proibido: A visualização pública não está ativada.',
    'api_search.error_unauthorized_table' => 'Acesso não autorizado à tabela.',
    'api_search.no_records'              => 'Nenhum registo encontrado nesta tabela.',
    'api_search.history_btn'             => 'Histórico',
    'api_search.suggest_edit_btn'        => 'Sugerir edição',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Voltar à página inicial pública',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ------------------------------------------------------------------
    'feedback.hp_label'              => 'Deixar em branco',
    'feedback.first_name_label'      => 'Nome:',
    'feedback.surname_label'         => 'Apelido:',
    'feedback.email_label'           => 'Endereço de e-mail:',
    'feedback.subject_label'         => 'Assunto / Título da consulta:',
    'feedback.required_title'        => 'Campo obrigatório',
    'feedback.select_placeholder'    => '-- Selecionar --',
    'feedback.multi_select_hint'     => 'Mantenha Ctrl ou Cmd premido para selecionar vários.',
    'feedback.submit_btn'            => 'Submeter ticket',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Falha de segurança: Assinatura de cliente suspeita.',
    'security_engine.err_access_denied'    => 'Falha de segurança: Acesso negado.',
    'security_engine.err_rate_limit'       => 'Demasiados pedidos a partir deste endereço IP. Por favor, tente novamente mais tarde.',
    'security_engine.err_excessive_links'  => 'Submissão rejeitada devido a demasiados links detetados.',
    'security_engine.err_complete_captcha' => 'Por favor, complete o desafio de segurança CAPTCHA.',
    'security_engine.err_captcha_failed'   => 'Falha na verificação do CAPTCHA. Por favor, tente novamente.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Instalação concluída',
    'install.complete_heading'           => 'Instalação concluída',
    'install.complete_desc'              => 'Este site já se encontra configurado. O instalador foi bloqueado para impedir que seja executado novamente por engano.',
    'install.login_link'                 => 'Entrar',
    'install.home_link'                  => 'Ir para o site',
    'install.delete_folder_hint'         => 'Pode eliminar ou mudar o nome da pasta <code>install</code> para maior segurança.',
    'install.msg_db_ready'               => 'A base de dados está pronta. Crie a sua conta de administrador para concluir a instalação.',
    'install.err_config_load'            => 'Não foi possível utilizar a configuração existente:',
    'install.err_write_permission'       => 'O PHP não consegue criar ficheiros nesta pasta de projeto.',
    'install.detail_prefix'              => 'Detalhe:',
    'install.err_db_required'            => 'O nome da base de dados e o nome de utilizador da base de dados são obrigatórios.',
    'install.err_db_not_empty'           => 'Esta base de dados não está vazia. Utilize uma nova base de dados vazia (ou elimine todas as tabelas) e tente novamente.',
    'install.msg_schema_imported'        => 'Base de dados ligada e esquema importado. Crie a sua conta de administrador.',
    'install.err_complete_db_first'      => 'Por favor, conclua primeiro a etapa da base de dados.',
    'install.err_admin_required'         => 'Todos os campos de administrador são obrigatórios.',
    'install.err_invalid_email'          => 'Endereço de e-mail inválido.',
    'install.err_password_length'        => 'A palavra-passe deve ter pelo menos 8 caracteres.',
    'install.err_passwords_match'        => 'As palavras-passe não coincidem.',
    'install.err_admin_save_failed'      => 'O utilizador administrador não foi guardado. Verifique a estrutura da tabela de utilizadores.',
    'install.msg_installation_complete' => 'Instalação concluída.',
    'install.page_title'                 => 'Instalação — Diretório de Registos Paroquiais',
    'install.heading'                    => 'Instalação',
    'install.subheading'                 => 'Configuração inicial <strong>apenas para esta pasta da aplicação</strong>. Utilize uma base de dados MySQL vazia.',
    'install.done_heading'               => 'Concluído',
    'install.done_message'               => 'Instalação concluída. O instalador encontra-se agora bloqueado.',
    'install.admin_heading'              => 'Conta de administrador do site',
    'install.admin_subheading'           => 'Este é o login para <strong>este website</strong> (não para a base de dados).',
    'install.admin_username_label'       => 'Nome de utilizador do administrador',
    'install.admin_email_label'          => 'E-mail do administrador',
    'install.admin_password_label'       => 'Palavra-passe do administrador (mín. 8 caracteres)',
    'install.admin_confirm_password_label' => 'Confirmar palavra-passe do administrador',
    'install.finish_btn'                 => 'Concluir instalação',
    'install.db_heading'                 => 'Ligação à base de dados',
    'install.db_hint'                    => 'Utilize os dados do MySQL do seu <strong>painel de controlo de alojamento</strong>. Este não é o login de administrador do site (esse virá a seguir).',
    'install.db_host_label'              => 'Servidor da base de dados',
    'install.db_name_label'              => 'Nome da base de dados',
    'install.db_user_label'              => 'Nome de utilizador da base de dados',
    'install.db_pass_label'              => 'Palavra-passe da base de dados',
    'install.db_submit_btn'              => 'Criar tabelas e continuar',
    'install.req_heading'                => '1. Requisitos',
    'install.req_php'                    => 'PHP 8.0+ (detetado %s)',
    'install.req_pdo'                    => 'Extensão PDO MySQL',
    'install.req_logs'                   => 'Pasta de registos com permissão de escrita (ou pasta do projeto)',
    'install.req_probe'                  => 'É possível criar ficheiros nesta pasta do projeto',
    'install.continue_btn'               => 'Continuar',
    'install.req_fail_msg'               => 'Por favor, corrija as verificações que falharam e recarregue esta página.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Vista da Classificação',
    'leaderboard.heading'         => 'Classificação de Participação Comunitária',
    'leaderboard.subheading'      => 'Reconhecimento dos esforços dos membros da nossa comunidade que ajudam a compilar, transcrever e/ou gerir registos de bases de dados.',
    'leaderboard.th_rank'         => 'Posição',
    'leaderboard.th_contributor'  => 'Colaborador',
    'leaderboard.th_role'         => 'Função',
    'leaderboard.th_score'        => 'Pontuação',
    'leaderboard.no_users'        => 'Ainda não foram encontrados utilizadores ativos na Classificação.',
    'leaderboard.medal_gold'      => 'Medalha de Ouro',
    'leaderboard.medal_silver'    => 'Medalha de Prata',
    'leaderboard.medal_bronze'    => 'Medalha de Bronze',
    'leaderboard.medal_ribbon'    => 'Fita de Mérito Nível 4',
    'leaderboard.medal_rosette'   => 'Rosácea Nível 5',
    'leaderboard.medal_trophy'    => 'Troféu Nível 6',
    'leaderboard.medal_star'      => 'Estrela Nível 7',
    'leaderboard.medal_military'  => 'Medalha Militar Nível 8',
    'leaderboard.medal_glowing'   => 'Estrela Brilhante Nível 9',
    'leaderboard.medal_crown'     => 'Coroa Nível 10',
    'leaderboard.you_badge'       => '(Tu)',
    'leaderboard.default_role'    => 'Utilizador',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Registos paroquiais compilados a partir de fontes históricas em domínio público.',
    'footer.software_notice'  => 'Plataforma de software de código aberto sob licença MIT.',
    'footer.rights_reserved'  => 'Todos os direitos reservados.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Base de Dados de Registos Paroquiais',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Fechar aviso',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Nenhum registo especificado.',
    'record_history.exit_not_found'        => 'Registo não encontrado.',
    'record_history.heading_prefix'        => 'Histórico e Trilha de Auditoria: Registo',
    'record_history.return_btn'            => 'Voltar',
    'record_history.directory_table_label'=> 'Tabela do Diretório:',
    'record_history.subheading_lifecycle' => 'Mostra o ciclo de vida social de alterações, sugestões e justificações diretamente associadas a este registo.',
    'record_history.snapshot_heading'      => 'Instantâneo dos valores ativos atuais',
    'record_history.empty_value'           => '[Vazio]',
    'record_history.timeline_heading'      => 'Linha do tempo de eventos e atividades',
    'record_history.no_history'            => 'Ainda não existem eventos de auditoria histórica registados especificamente para este registo.',
    'record_history.purge_confirm'         => 'Eliminar esta entrada de registo de auditoria específica?',
    'record_history.purge_btn'             => 'Limpar registo',
    'record_history.actor_label'           => 'Ator:',
    'record_history.system_guest'          => 'Sistema / Convidado',
    'record_history.target_column'         => 'Coluna alvo:',
    'record_history.proposed_value'        => 'Valor proposto:',
    'record_history.reasoning_evidence'    => 'Justificação / Evidência:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Base de dados atualizada com sucesso! Foram aplicadas %d migrações.',
    'update_database.msg_uptodate'     => 'A base de dados já está atualizada.',
    'update_database.err_failed'       => 'Falha na migração:',
    'update_database.page_title'       => 'Atualização do sistema necessária — Diretório de Registos Paroquiais',
    'update_database.heading'          => '⚠️ Atualização do sistema necessária',
    'update_database.subheading'       => 'A estrutura da base de dados da aplicação está desatualizada e requer uma atualização de esquema antes de retomar o funcionamento normal.',
    'update_database.current_version'  => 'Versão atual do esquema:',
    'update_database.latest_version'   => 'Mais recente disponível:',
    'update_database.proceed_login'    => 'Prosseguir para o início de sessão',
    'update_database.confirm_prompt'   => 'Já criou uma cópia de segurança da sua base de dados? Clique em OK para aplicar as atualizações de esquema pendentes.',
    'update_database.update_btn'       => 'Atualizar base de dados agora',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Credenciais inválidas ou acesso à conta restrito.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'O campo obrigatório \'%s\' não pode ficar em branco.',
    'save_data_entry.audit_created_prefix' => 'Registo criado na tabela ID %d.',
    'save_data_entry.msg_success'          => 'Registo adicionado com sucesso!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Spam detetado. Submissão rejeitada.',
    'save_public_suggestion.err_field_required' => 'Este campo é obrigatório e não pode ser submetido em branco.',
    'save_public_suggestion.msg_success'        => 'A sua sugestão de edição foi submetida com sucesso e enviada para a fila de moderação para revisão. Obrigado!',
    'save_public_suggestion.err_failed_submit'  => 'Falha ao submeter a sugestão de edição. Por favor, tente novamente.',
    'save_public_suggestion.err_invalid_column' => 'Coluna especificada inválida.',
    'save_public_suggestion.err_invalid_params' => 'Parâmetros de submissão de registo inválidos.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'AAAA-MM-DD (ou ano parcial)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/AAAA (ou ano parcial)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/AAAA (ou ano parcial)',
    'data_entry.no_tables_heading'    => '⚠️ Nenhuma tabela de base de dados encontrada',
    'data_entry.no_tables_desc'       => 'De momento, o sistema não tem nenhuma tabela de base de dados ativa configurada para entrada de dados.',
    'data_entry.admin_tables_prompt'  => 'Como administrador, aceda a <strong>Gerir tabelas</strong> para criar uma tabela e adicione pelo menos uma coluna antes de introduzir registos.',
    'data_entry.go_manage_tables'     => 'Ir para Gerir tabelas',
    'data_entry.contact_admin_tables' => 'Contacte um administrador para configurar as tabelas e colunas da base de dados.',
    'data_entry.no_cols_heading'      => '⚠️ Nenhuma coluna configurada',
    'data_entry.no_cols_desc'         => 'Existem tabelas no sistema, mas nenhuma coluna de dados foi definida para a tabela ativa.',
    'data_entry.admin_cols_prompt'    => 'Como administrador, aceda a <strong>Gerir tabelas</strong> para adicionar pelo menos uma coluna à sua tabela.',
    'data_entry.contact_admin_cols'   => 'Contacte um administrador para configurar as colunas desta tabela.',
    'data_entry.active_table_label'   => 'Tabela de entrada de dados ativa:',
    'data_entry.add_entry_summary'    => '➕ Adicionar nova entrada de dados (Clique para expandir/recolher)',
    'data_entry.bool_yes_true'        => 'Sim / Verdadeiro',
    'data_entry.bool_no_false'        => 'Não / Falso',
    'data_entry.bool_male'            => 'Masculino',
    'data_entry.bool_female'          => 'Feminino',
    'data_entry.bool_true'            => 'Verdadeiro',
    'data_entry.bool_false'           => 'Falso',
    'data_entry.bool_tick'            => '✔ (Marcar)',
    'data_entry.bool_cross'           => '✘ (Cruz)',
    'data_entry.date_title_hint'      => 'Aceita datas completas ou parciais (ex: 1842 ou 1842-05)',
    'data_entry.enter_value_placeholder' => 'Introduzir valor...',
    'data_entry.submit_data_btn'      => 'Submeter dados',
    'data_entry.shortcuts_tip'        => '💡 Dicas: Prima <strong>Ctrl + Enter</strong> para submeter, ou <strong>Esc</strong> para limpar o campo atual.',
    'data_entry.dup_heading'          => '⚠️ Aviso de possível duplicado',
    'data_entry.dup_desc'             => 'Encontrámos entradas compatíveis no sistema:',
    'data_entry.dup_item_format'      => 'ID do Registo: %d — Valor: %s',
    'data_entry.dup_prompt'           => 'Pretende continuar e guardar esta entrada duplicada de qualquer forma?',
    'data_entry.dup_confirm_btn'      => 'Sim, confirmar e guardar duplicado',
    'data_entry.search_summary'       => '🔍 Pesquisar e filtrar registos existentes (Clique para expandir/recolher)',
    'data_entry.date_to_label'        => 'até',
    'data_entry.filter_all_option'    => '-- Todos --',
    'data_entry.filter_placeholder'   => 'Filtrar...',
    'data_entry.apply_filters_btn'    => 'Aplicar filtros de pesquisa',
    'data_entry.reset_filter_btn'     => 'Repor filtro',
    'data_entry.csv_entire_btn'       => 'Descarregar CSV completo',
    'data_entry.json_entire_btn'      => 'Descarregar JSON completo',
    'data_entry.copy_entire_btn'      => 'Copiar tabela completa',
    'data_entry.csv_filtered_btn'     => 'Descarregar CSV filtrado',
    'data_entry.json_filtered_btn'     => 'Descarregar JSON filtrado',
    'data_entry.copy_filtered_btn'    => 'Copiar tabela filtrada',
    'data_entry.clipboard_alert'      => 'Dados da tabela copiados para a área de transferência! Pode colá-los diretamente no Excel ou Google Sheets.',
    'data_entry.existing_records_heading' => 'Tabela de registos existentes',
    'data_entry.th_added_by'          => 'Adicionado por',
    'data_entry.th_date_created'      => 'Data de criação',
    'data_entry.no_records'           => 'Nenhum registo encontrado.',
    'data_entry.na_value'             => 'N/A',
    'data_entry.page_label'           => 'Página:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Recuperação de palavra-passe',
    'forgot_password.heading'         => 'Redefina a sua palavra-passe',
    'forgot_password.subheading'      => 'Introduza o endereço de e-mail da sua conta abaixo e enviaremos um link seguro para redefinir a sua palavra-passe.',
    'forgot_password.email_label'     => 'Endereço de e-mail:',
    'forgot_password.submit_btn'      => 'Enviar link de redefinição',
    'forgot_password.back_login_link' => 'Voltar ao início de sessão',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Início de sessão de utilizador',
    'login.heading'              => 'Início de sessão de utilizador',
    'login.username_label'       => 'Nome de utilizador ou e-mail:',
    'login.password_label'       => 'Palavra-passe:',
    'login.submit_btn'           => 'Entrar',
    'login.forgot_password_link' => 'Esqueceu-se da sua palavra-passe?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Bem-vindo - Assistente de Configuração de Conta',
    'onboarding.heading'           => 'Bem-vindo à equipa!',
    'onboarding.subheading'        => 'Antes de começar, reserve um momento para configurar as suas preferências regionais de exibição e privacidade. Pode atualizá-las a qualquer momento no seu perfil.',
    'onboarding.timezone_label'    => 'Fuso horário / Região:',
    'onboarding.date_format_label' => 'Formato de exibição de data:',
    'onboarding.time_format_label' => 'Formato do relógio (Exibição de hora):',
    'onboarding.time_24'          => '24 horas (ex: 16:07)',
    'onboarding.time_12'          => '12 horas AM/PM (ex: 04:07 PM)',
    'onboarding.time_none'        => 'Apenas data (Ocultar hora completamente)',
    'onboarding.attribution_label' => 'Preferência de exibição na classificação e atribuição:',
    'onboarding.attribution_desc1' => 'Controla como o seu nome é apresentado na classificação pública e nos registos.',
    'onboarding.attr_anon_title'   => 'Anónimo:',
    'onboarding.attr_anon_text'    => 'Mostra iniciais e um número aleatório para todos.',
    'onboarding.attr_public_title' => 'Público:',
    'onboarding.attr_public_text'  => 'Mostra o seu nome completo para todos.',
    'onboarding.attr_vol_title'    => 'Apenas voluntários:',
    'onboarding.attr_vol_text'     => 'Mostra iniciais ao público, mas o seu nome completo a voluntários, moderadores e administradores com sessão iniciada.',
    'onboarding.attr_opt_anon'     => 'Anónimo (Iniciais e número aleatório)',
    'onboarding.attr_opt_public'   => 'Público (Mostrar nome completo)',
    'onboarding.attr_opt_vol'      => 'Apenas voluntários',
    'onboarding.submit_btn'        => 'Guardar preferências e continuar',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Gestão de perfil de utilizador',
    'profile.heading'              => 'Perfil de utilizador e Segurança',
    'profile.personal_details_heading' => 'Detalhes pessoais',
    'profile.language_label'       => 'Idioma preferido:',
    'profile.lang_site_default'    => 'Predefinição do site',
    'profile.update_details_btn'   => 'Atualizar detalhes pessoais',
    'profile.email_heading'        => 'Endereço de e-mail',
    'profile.current_email_label'  => 'E-mail atual:',
    'profile.email_verified'       => '(Verificado)',
    'profile.email_unverified'     => '(Não verificado - Verifique a sua caixa de entrada)',
    'profile.change_email_label'   => 'Alterar endereço de e-mail:',
    'profile.aria_new_email'       => 'Novo endereço de e-mail',
    'profile.update_email_btn'     => 'Atualizar e-mail e verificar',
    'profile.password_heading'     => 'Alterar palavra-passe',
    'profile.current_password_label' => 'Palavra-passe atual:',
    'profile.new_password_label'   => 'Nova palavra-passe (mín. 8 caracteres):',
    'profile.confirm_password_label' => 'Confirmar nova palavra-passe:',
    'profile.show_passwords_label' => 'Mostrar palavras-passe em texto simples',
    'profile.update_password_btn'  => 'Atualizar palavra-passe',
    'profile.tfa_heading'          => 'Autenticação de Dois Fatores (2FA)',
    'profile.tfa_status_label'     => 'Estado:',
    'profile.tfa_enabled'          => 'Ativado',
    'profile.tfa_disabled'         => 'Desativado',
    'profile.setup_tfa_btn'        => 'Configurar Google Authenticator',
    'profile.tfa_active_desc'      => 'O 2FA protege ativamente o início de sessão da sua conta.',
    'profile.backup_codes_heading' => 'Os seus novos códigos de segurança',
    'profile.download_codes_btn'   => 'Descarregar novos códigos como .txt',
    'profile.generate_codes_confirm' => 'Tem a certeza? Isto invalidará quaisquer códigos de segurança existentes.',
    'profile.generate_codes_btn'   => 'Gerir novos códigos de segurança',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Registo de utilizador',
    'register.heading'        => 'Registar nova conta',
    'register.username_label' => 'Nome de utilizador:',
    'register.submit_btn'     => 'Registar',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Token de configuração inválido ou em falta.',
    'set_password.exit_expired_token'        => 'Este link de configuração de palavra-passe é inválido ou expirou.',
    'set_password.proceed_login_btn'         => 'Prosseguir para o início de sessão',
    'set_password.aria_region'               => 'Definir palavra-passe',
    'set_password.heading_format'            => 'Defina a sua palavra-passe para %s',
    'set_password.subheading_format'         => 'Bem-vindo à sua nova conta, %s! Por favor, escolha a sua palavra-passe abaixo.',
    'set_password.new_password_label'        => 'Nova palavra-passe (mín. 8 caracteres):',
    'set_password.confirm_password_label'    => 'Confirmar palavra-passe:',
    'set_password.show_password_label'       => 'Mostrar palavra-passe',
    'set_password.save_password_btn'         => 'Guardar palavra-passe',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => 'Assistente de configuração 2FA',
    'setup_2fa.heading'          => 'Configurar Google Authenticator',
    'setup_2fa.subheading'       => 'Leia o código QR abaixo com a sua aplicação de autenticação.',
    'setup_2fa.qr_alt'           => 'Código QR para configuração de 2FA',
    'setup_2fa.manual_prompt'    => 'Ou introduza esta chave secreta manualmente:',
    'setup_2fa.backup_heading'   => 'Códigos de recuperação de segurança de emergência',
    'setup_2fa.backup_desc'      => 'Guarde estes códigos de segurança num local seguro. Cada código pode ser utilizado <strong>apenas uma vez</strong> caso perca o acesso à sua aplicação de autenticação:',
    'setup_2fa.download_btn'     => 'Descarregar códigos como .txt',
    'setup_2fa.code_label'       => 'Introduza o código de 6 dígitos da aplicação para confirmar e ativar:',
    'setup_2fa.aria_code_input'  => 'Código de autenticação de 6 dígitos',
    'setup_2fa.submit_btn'       => 'Verificar e ativar 2FA',
    'setup_2fa.cancel_link'      => 'Cancelar e voltar ao perfil',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Sugerir edição',
    'suggest_edit.heading_prefix'       => 'Sugerir edição para o registo',
    'suggest_edit.return_btn'           => 'Voltar ao registo',
    'suggest_edit.success_msg_suffix'   => 'Sinta-se à vontade para submeter outra edição abaixo, ou utilize o link de regresso acima quando terminar.',
    'suggest_edit.current_values_heading' => 'Valores atuais:',
    'suggest_edit.empty_label'          => '(vazio)',
    'suggest_edit.submit_heading'       => 'Submeter novo valor proposto e evidência',
    'suggest_edit.confirm_prompt'       => 'Tem a certeza de que está pronto para submeter esta sugestão de edição para revisão do administrador?',
    'suggest_edit.select_column_label'  => 'Selecionar coluna para editar:',
    'suggest_edit.reasoning_label'      => 'Evidência / Justificação / Notas de fonte:',
    'suggest_edit.reasoning_placeholder'=> 'Forneça contexto, citações de fonte ou justificação para esta alteração...',
    'suggest_edit.submit_btn'           => 'Submeter sugestão para revisão',
    'suggest_edit.proposed_value_label' => 'Novo valor proposto:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => 'Verificação 2FA',
    'verify_2fa.heading'         => 'Autenticação de Dois Fatores',
    'verify_2fa.subheading'      => 'Introduza o código de 6 dígitos da sua aplicação de autenticação ou utilize um código de recuperação de segurança.',
    'verify_2fa.code_label'      => 'Código de verificação / Código de segurança:',
    'verify_2fa.aria_code_input' => 'Introduzir código de verificação ou de segurança',
    'verify_2fa.submit_btn'      => 'Verificar e entrar',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Nenhum token de verificação fornecido.',
    'verify_email.err_invalid_token'    => 'Token de verificação inválido.',
    'verify_email.msg_already_verified' => 'O seu e-mail já foi verificado. Pode iniciar sessão.',
    'verify_email.err_expired_token'    => 'Este link de verificação expirou (o período de 24 horas foi excedido). Por favor, registe-se novamente ou solicite um novo link.',
    'verify_email.msg_success'          => 'E-mail verificado com sucesso! A sua conta está agora ativa. Pode prosseguir para o início de sessão.',
    'verify_email.err_update_failed'    => 'Ocorreu um erro ao verificar o seu e-mail. Por favor, tente novamente.',
    'verify_email.aria_region'          => 'Estado de verificação de e-mail',
    'verify_email.heading'              => 'Estado de verificação de e-mail',
    'verify_email.login_btn'            => 'Clique aqui para iniciar sessão',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Formulário de voluntariado',
    'volunteer.honeypot_label'       => 'Deixar este campo em branco:',
    'volunteer.required_field_title'=> 'Campo obrigatório',
    'volunteer.multi_select_hint'    => 'Mantenha Ctrl ou Cmd premido para selecionar vários.',
    'volunteer.submit_btn'           => 'Submeter candidatura de voluntariado',
];
