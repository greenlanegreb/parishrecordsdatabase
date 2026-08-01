<?php
// lang/pt_BR.php - Brazilian Portuguese (Português do Brasil)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Entrar',
    'nav.logout'                 => 'Sair',
    'nav.feedback'               => 'Feedback',
    'nav.volunteer'              => 'Se Seja Voluntário',
    'nav.leaderboard'            => 'Placar',
    'nav.search'                 => 'Pesquisar',
    'nav.settings'               => 'Configurações do Sistema',
    'nav.high_contrast'          => 'Alto Contraste',
    'nav.low_contrast'           => 'Baixo Contraste',
    'nav.welcome'                => 'Bem-vindo,',
    'nav.data_entry'             => 'Entrada de Dados',
    'nav.moderation'             => 'Moderação',
    'nav.invite_user'            => 'Convidar Usuário',
    'nav.manage_users'           => 'Gerenciar Usuários',
    'nav.manage_tables'          => 'Gerenciar Tabelas',
    'nav.volunteer_dashboard'    => 'Painel de Voluntários',
    'nav.feedback_dashboard'     => 'Painel de Feedback',
    'nav.leaderboard_score'      => 'Pontuação do Placar',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Filtros de Pesquisa Multicoluna',
    'search.reset'               => 'Redefinir Pesquisa',
    'search.export_csv'          => 'Exportar Resultados Filtrados como CSV',
    'search.no_records'          => 'Nenhum registro encontrado neste diretório.',
    'search.load_error'          => 'Erro ao carregar os resultados. Por favor, tente novamente.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Enviar',
    'btn.cancel'                 => 'Cancelar',
    'btn.save'                   => 'Salvar',
    'btn.delete'                 => 'Excluir',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Obrigado! Seu feedback foi enviado com sucesso.',
    'feedback.error_all_fields'   => 'Por favor, preencha todos os campos.',
    'feedback.error_invalid_email'=> 'Por favor, insira um endereço de e-mail válido.',
    'feedback.error_save_failed'  => 'Ocorreu um erro ao salvar o seu feedback. Por favor, tente novamente.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Nenhuma Tabela de Banco de Dados Encontrada',
    'index.no_tables_desc'             => 'Atualmente, não há tabelas de banco de dados ativas configuradas no sistema.',
    'index.admin_create_table_guide'   => 'Como administrador, acesse <strong>Gerenciar Tabelas</strong> para criar uma tabela e adicionar pelo menos uma coluna antes de exibir ou inserir registros.',
    'index.go_to_manage_tables'        => 'Ir para Gerenciar Tabelas',
    'index.contact_admin_tables'       => 'Entre em contato com um administrador para configurar as tabelas e colunas do banco de dados.',
    'index.guest_login_tables_guide'   => 'Por favor, <a href=":login_link">faça login</a> ou entre em contato com o administrador para configurar as tabelas.',
    'index.no_columns_heading'         => 'Nenhuma Coluna Configurada',
    'index.no_columns_desc'            => 'Existem tabelas no sistema, mas nenhuma coluna de dados foi definida para a tabela ativa.',
    'index.admin_add_columns_guide'    => 'Como administrador, acesse <strong>Gerenciar Tabelas</strong> para adicionar pelo menos uma coluna à sua tabela.',
    'index.contact_admin_columns'      => 'Entre em contato com o administrador para configurar as colunas desta tabela.',
    'index.select_directory_database'  => 'Selecionar banco de dados do diretório:',
    'index.opt_yes_true'               => 'Sim / Verdadeiro',
    'index.opt_no_false'               => 'Não / Falso',
    'index.opt_male'                   => 'Masculino',
    'index.opt_female'                 => 'Feminino',
    'index.opt_true'                   => 'Verdadeiro',
    'index.opt_false'                  => 'Falso',
    'index.opt_tick'                   => '✔ (Marcado)',
    'index.opt_cross'                  => '✘ (Cruz)',
    'index.option_all'                 => '-- Todos --',
    'index.date_to_label'              => 'até',
    'index.search_placeholder'         => 'Pesquisar...',
    'index.download_entire_csv'        => 'Baixar CSV Completo',
    'index.download_entire_json'       => 'Baixar JSON Completo',
    'index.copy_entire_table'          => 'Copiar Tabela Completa',
    'index.download_filtered_csv'      => 'Baixar CSV Filtrado',
    'index.download_filtered_json'     => 'Baixar JSON Filtrado',
    'index.copy_filtered_table'        => 'Copiar Tabela Filtrada',
    'index.th_record_id'               => 'ID do Registro',
    'index.th_created_by'              => 'Criado por',
    'index.th_date_added'              => 'Data de Adição',
    'index.th_actions'                 => 'Ações',
    'index.modal_heading'              => 'Sugerir Correção de Registro',
    'index.modal_desc'                 => 'Forneça uma correção ou informação alternativa para este registro. Nossa equipe de moderação irá analisá-la.',
    'index.modal_target_column'        => 'Coluna Alvo:',
    'index.modal_proposed_value'       => 'Valor Proposto / Correção:',
    'index.modal_input_placeholder'    => 'Insira as informações atualizadas...',
    'index.modal_submit_btn'           => 'Enviar Sugestão',
    'index.clipboard_success'          => 'Os dados da tabela foram copiados para a área de transferência! Você pode colá-los no Excel ou Google Sheets.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Formulário de Convite de Novo Usuário',
    'create_user.subheading'           => 'Isso gerará um link de configuração seguro válido por 24 horas e o enviará diretamente por e-mail ao usuário.',
    'create_user.first_name'           => 'Nome:',
    'create_user.surname'              => 'Sobrenome:',
    'create_user.username_label'       => 'Nome de Usuário (Opcional):',
    'create_user.username_placeholder' => 'Deixe em branco para gerar automaticamente',
    'create_user.username_help'        => 'Se deixado em branco, um nome de usuário exclusivo será gerado automaticamente com base no primeiro nome.',
    'create_user.email_label'          => 'Endereço de E-mail:',
    'create_user.role_label'           => 'Função do Usuário:',
    'create_user.submit_btn'           => 'Criar Usuário e Enviar Convite',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Painel de Tickets de Suporte e Feedback',
    'feedback_dash.subheading'           => 'Gerencie solicitações de suporte público, atualize status e participe da conversa.',
    'feedback_dash.manage_emails'        => 'Gerenciar Modelos de E-mail',
    'feedback_dash.manage_schema'        => 'Gerenciar Esquema do Formulário de Tickets',
    'feedback_dash.th_ticket_date'       => 'ID do Ticket / Data',
    'feedback_dash.th_submitter'         => 'Remetente',
    'feedback_dash.th_subject_info'      => 'Assunto / Informações Básicas',
    'feedback_dash.th_status'            => 'Status',
    'feedback_dash.no_tickets'           => 'Nenhum ticket de feedback encontrado.',
    'feedback_dash.anonymous'            => 'Anônimo',
    'feedback_dash.default_subject'      => 'Consulta Geral',
    'feedback_dash.open_ticket_btn'      => 'Abrir Ticket e Conversa',
    'feedback_dash.delete_confirm'       => 'Excluir este ticket de suporte e todas as respostas associadas?',
    'feedback_dash.msg_deleted'          => 'Ticket #:id excluído com sucesso.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Modelos de E-mail de Tickets de Suporte',
    'feedback_emails.subheading'         => 'Personalize as notificações por e-mail automáticas enviadas durante o fluxo de tickets. Use chaves para valores dinâmicos.',
    'feedback_emails.back_to_dashboard' => 'Voltar ao Painel de Tickets',
    'feedback_emails.email_subject'      => 'Assunto do E-mail:',
    'feedback_emails.email_body'         => 'Modelo do Corpo do E-mail:',
    'feedback_emails.save_template_btn' => 'Salvar Modelo',
    'feedback_emails.placeholders_heading' => 'Marcadores Disponíveis',
    'feedback_emails.placeholders_desc' => 'Você pode usar estas tags em qualquer lugar no assunto ou no corpo:',
    'feedback_emails.fixed_tags'         => 'Tags Fixas Principais:',
    'feedback_emails.custom_tags'        => 'Tags de Esquema Personalizadas:',
    'feedback_emails.custom_tags_desc'   => 'Geradas automaticamente a partir dos campos do construtor de formulários de tickets:',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Gerenciar Esquema do Formulário de Feedback',
    'feedback_schema.subheading'             => 'Configure campos personalizados, tipos de dados, limites de caracteres, subtipos, opções e estilo de exibição.',
    'feedback_schema.settings_summary'       => 'Configurar Título do Formulário e Texto de Isenção de Responsabilidade',
    'feedback_schema.form_title_label'       => 'Título do Formulário:',
    'feedback_schema.form_intro_label'       => 'Texto de Introdução / Descrição:',
    'feedback_schema.save_settings_btn'      => 'Salvar Configurações do Formulário',
    'feedback_schema.edit_field_title'       => 'Editar Campo do Ticket:',
    'feedback_schema.add_field_title'        => '+ Adicionar Novo Campo ao Formulário de Tickets',
    'feedback_schema.field_name_label'       => 'Rótulo / Nome do Campo:',
    'feedback_schema.data_type_label'        => 'Tipo de Dados:',
    'feedback_schema.type_varchar'           => 'VARCHAR (Texto Curto)',
    'feedback_schema.type_text'              => 'TEXT (Parágrafo Longo / Mensagem)',
    'feedback_schema.type_int'               => 'INT (Número Inteiro)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Indicador Sim/Não)',
    'feedback_schema.type_date'              => 'DATE (Data do Calendário)',
    'feedback_schema.subtype_label'          => 'Subtipo do Campo / Estilo de Renderização de Entrada:',
    'feedback_schema.subtype_standard'       => '-- Padrão --',
    'feedback_schema.subtype_standard_lower'=> 'padrão',
    'feedback_schema.options_label'          => 'Opções (separadas por vírgula ou uma por linha):',
    'feedback_schema.options_help'           => 'Forneça opções separadas por vírgulas ou quebras de linha.',
    'feedback_schema.allow_multiple'         => 'Permitir seleção de várias opções (Seleção Múltipla)',
    'feedback_schema.boolean_format'         => 'Formato de Exibição Booleano:',
    'feedback_schema.max_length_label'       => 'Comprimento Máximo / Limite de Caracteres Opcional:',
    'feedback_schema.is_required_label'      => 'Tornar este campo obrigatório para os remetentes',
    'feedback_schema.save_field_btn'         => 'Salvar Alterações do Campo',
    'feedback_schema.create_field_btn'       => 'Criar Campo de Ticket',
    'feedback_schema.sub_email'              => 'E-mail',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Menu Suspenso (Select)',
    'feedback_schema.sub_radio'              => 'Grupo de Botões de Rádio',
    'feedback_schema.sub_checkbox'           => 'Caixa de Seleção (Checkbox)',
    'feedback_schema.sub_textarea'           => 'Caixa de Texto Multilinhas',
    'feedback_schema.sub_number'             => 'Entrada Numérica',
    'feedback_schema.existing_fields_heading'=> 'Campos de Ticket Existentes',
    'feedback_schema.th_move'                => 'Mover',
    'feedback_schema.th_field_name'          => 'Nome do Campo',
    'feedback_schema.th_data_type'           => 'Tipo de Dados',
    'feedback_schema.th_subtype'             => 'Subtipo',
    'feedback_schema.th_required'            => 'Obrigatório?',
    'feedback_schema.th_max_length'          => 'Comprimento Máximo',
    'feedback_schema.th_created_by'          => 'Criado por',
    'feedback_schema.no_fields'              => 'Nenhum campo de ticket personalizado definido ainda.',
    'feedback_schema.system_user'            => 'Sistema',
    'feedback_schema.edit_btn'               => 'Editar',
    'feedback_schema.delete_confirm'         => 'Excluir este campo e todos os valores de resposta associados?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Gerenciar Tabelas e Esquemas',
    'manage_tables.subheading'           => 'Crie, inspecione, edite ou exclua com segurança as tabelas dinâmicas do aplicativo e seus esquemas de colunas.',
    'manage_tables.switcher_label'       => 'Selecionar esquema de tabela ativo:',
    'manage_tables.edit_metadata_btn'    => 'Editar Metadados da Tabela',
    'manage_tables.delete_table_confirm'=> 'AVISO: Excluir esta tabela removerá permanentemente todas as colunas e o conteúdo salvo. Tem certeza absoluta?',
    'manage_tables.delete_table_btn'     => 'Excluir Tabela',
    'manage_tables.edit_table_summary'   => 'Editar Definição da Tabela:',
    'manage_tables.create_table_summary'=> '+ Criar Nova Tabela Dinâmica',
    'manage_tables.table_name_label'     => 'Nome Amigável da Tabela:',
    'manage_tables.table_desc_label'     => 'Descrição / Propósito:',
    'manage_tables.save_table_btn'       => 'Salvar Alterações da Tabela',
    'manage_tables.create_table_btn'     => 'Criar Esquema da Tabela',
    'manage_tables.edit_col_summary'     => 'Editar Coluna Dinâmica:',
    'manage_tables.add_col_summary_prefix' => '+ Adicionar nova coluna de tabela para',
    'manage_tables.col_name_label'       => 'Nome da Coluna:',
    'manage_tables.type_text_long'       => 'TEXT (Parágrafo Longo)',
    'manage_tables.date_behavior_label' => 'Comportamento de Busca de Data:',
    'manage_tables.date_bhv_manual'      => 'Data do banco de dados (inserção manual apenas)',
    'manage_tables.date_bhv_admin'       => 'Apenas datas de administrador',
    'manage_tables.date_bhv_all'         => 'Todas as datas, incluindo as de administrador',
    'manage_tables.req_toggle_label'     => 'Tornar esta coluna obrigatória (entrada de dados obrigatória)',
    'manage_tables.exclude_search_label'=> 'Excluir esta coluna da busca pública (index.php)',
    'manage_tables.create_col_btn'       => 'Criar Coluna',
    'manage_tables.existing_cols_heading_prefix' => 'Colunas existentes para',
    'manage_tables.th_public_search'     => 'Busca Pública?',
    'manage_tables.th_display_format'    => 'Formato de Exibição',
    'manage_tables.th_date_created'      => 'Data de Criação',
    'manage_tables.no_columns_found'     => 'Nenhuma coluna dinâmica definida para esta tabela ainda.',
    'manage_tables.status_hidden'        => 'Oculto',
    'manage_tables.delete_col_confirm'   => 'AVISO: Excluir esta coluna também removerá todos os dados de célula associados em cada registro. Tem certeza?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Gerenciar Modelos de E-mail de Notificação de Usuário',
    'user_emails.subheading'             => 'Personalize os layouts de e-mail enviados ao convidar usuários ou enviar links de redefinição de senha.',
    'user_emails.select_template_label'=> 'Selecionar modelo para editar:',
    'user_emails.opt_invitation'         => 'Modelo de Convite de Conta de Usuário',
    'user_emails.opt_reset'              => 'Modelo de Redefinição de Senha / Link de Acesso',
    'currently_editing'                  => 'Editando Atualmente:',
    'user_emails.desc_invitation'        => 'Enviado automaticamente quando um administrador cria ou convida um novo usuário.',
    'user_emails.desc_reset'             => 'Enviado quando uma redefinição de senha ou reenvio de link de acesso é acionado.',
    'user_emails.email_body_label'       => 'Corpo do E-mail:',
    'user_emails.back_to_creation'       => 'Voltar à Criação de Usuário',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Modelos de E-mail de Voluntários e Gatilhos',
    'volunteer_emails.subheading'        => 'Personalize as respostas automáticas por e-mail para voluntários em diferentes estágios do fluxo de trabalho. Use chaves para valores dinâmicos.',
    'volunteer_emails.back_to_dashboard'=> 'Voltar às Solicitações de Voluntários',
    'volunteer_emails.custom_tags_desc'  => 'Geradas automaticamente a partir dos campos do construtor de formulários:',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Gerenciar Esquema do Formulário de Voluntários',
    'volunteer_schema.subheading'        => 'Configure campos personalizados, tipos de dados, subtipos, opções e preferências gerais de exibição do formulário.',
    'volunteer_schema.back_to_dashboard'=> 'Voltar às Solicitações de Voluntários',
    'volunteer_schema.settings_summary'  => 'Configurar Título do Formulário e Texto de Isenção de Responsabilidade',
    'volunteer_schema.edit_field_title'  => 'Editar Campo:',
    'volunteer_schema.add_field_title'   => '+ Adicionar Novo Campo ao Formulário de Voluntários',
    'volunteer_schema.create_field_btn'  => 'Criar Campo',
    'volunteer_schema.existing_fields_heading' => 'Campos do Formulário de Voluntários Existentes',
    'volunteer_schema.no_fields'         => 'Nenhum campo de voluntário personalizado definido ainda.',
    'volunteer_schema.delete_confirm'    => 'Excluir este campo e todos os valores de resposta associados?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Revisão da Fila de Moderação e Sugestões',
    'moderate.subheading'                => 'Compare as alterações sugeridas pelos usuários com os registros ativos em suas tabelas autorizadas. Aprove, substitua ou rejeite sugestões.',
    'moderate.shortcut_label'            => 'Dica de Atalhos de Teclado:',
    'moderate.shortcut_desc'             => 'Pressione Ctrl + Enter para aprovar rapidamente ou Esc para limpar a caixa de substituição!',
    'moderate.th_id_date'                => 'ID / Data',
    'moderate.th_table_record'           => 'Tabela, Registro e Coluna',
    'moderate.th_comparison'             => 'Comparação (Ativo vs Sugerido) e Evidência',
    'moderate.th_actions'                => 'Ações do Moderador',
    'moderate.no_suggestions'            => 'Nenhuma sugestão pendente encontrada para suas tabelas de moderação autorizadas.',
    'moderate.by_label'                  => 'Por:',
    'moderate.guest_user'                => 'Convidado / Visitante',
    'moderate.record_id_label'           => 'ID do Registro:',
    'moderate.column_label'              => 'Coluna:',
    'moderate.required_badge'            => 'Obrigatório',
    'moderate.live_value_label'          => 'Valor ativo atual:',
    'moderate.empty_placeholder'         => '[Vazio]',
    'moderate.proposed_value_label'      => 'Alteração sugerida:',
    'moderate.evidence_label'            => 'Evidência / Justificativa:',
    'moderate.no_evidence'               => 'Nenhuma evidência ou justificativa fornecida.',
    'moderate.override_label'            => 'Substituir Valor:',
    'moderate.select_placeholder'        => '-- Selecionar --',
    'moderate.historical_dates_title'    => 'Datas históricas suportadas',
    'moderate.approve_confirm'           => 'Aprovar e aplicar este valor?',
    'moderate.decline_confirm'           => 'Rejeitar e descartar esta sugestão?',
    'moderate.approve_btn'               => 'Aprovar',
    'moderate.decline_btn'               => 'Rejeitar',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Gerenciador de Avisos e Anúncios do Site',
    'notices.subheading'                 => 'Crie alertas dinâmicos, banners de boas-vindas ou anúncios direcionados para funções de usuário específicas.',
    'notices.error_blank'                => 'Título e conteúdo não podem ficar em branco.',
    'notices.msg_created'                => 'Aviso criado com sucesso!',
    'notices.msg_deleted'                => 'Aviso excluído.',
    'notices.create_heading'             => 'Criar Novo Aviso',
    'notices.title_label'                => 'Título / Cabeçalho do Aviso:',
    'notices.content_label'              => 'Conteúdo do Aviso (HTML/Texto permitido):',
    'notices.target_roles_label'         => 'Público-alvo (Selecionar funções ou todos):',
    'notices.role_everyone'              => 'Todos',
    'notices.role_public'                => 'Público (Convidado)',
    'notices.role_users'                 => 'Usuários',
    'notices.role_moderators'            => 'Moderadores',
    'notices.role_admins'                => 'Administradores',
    'notices.dismissible_label'          => "Dispensável (Inclui botão 'X' de fechar)",
    'notices.display_order_label'        => 'Ordem de Exibição:',
    'notices.publish_btn'                => 'Publicar Aviso',
    'notices.existing_heading'           => 'Avisos Ativos e Existentes',
    'notices.th_order'                   => 'Ordem',
    'notices.th_title'                   => 'Título',
    'notices.th_target_roles'            => 'Funções Alvo',
    'notices.th_dismissible'             => 'Dispensável',
    'notices.no_notices'                 => 'Nenhum aviso criado ainda.',
    'notices.yes'                        => 'Sim',
    'notices.no_sticky'                  => 'Não (Fixo / Sticky)',
    'notices.delete_confirm'             => 'Excluir este aviso?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Configurações Globais do Site, Módulos e Permissões',
    'settings.subheading'                => 'Gerencie configurações centrais, drivers de e-mail, opções de segurança/CAPTCHA, módulos de recursos, modo de manutenção, avisos do site e matriz de funções.',
    'settings.tab_core'                  => 'Núcleo e E-mail',
    'settings.tab_modules'               => 'Módulos',
    'settings.tab_maintenance'           => 'Manutenção',
    'settings.tab_notices'               => 'Avisos do Site',
    'settings.tab_permissions'           => 'Funções e Permissões',
    'settings.tab_audit'                 => 'Log de Auditoria',
    'settings.db_updates_heading'        => 'Atualizações do Banco de Dados',
    'settings.schema_current'            => 'Versão atual do esquema:',
    'settings.schema_latest'             => 'Última versão disponível:',
    'settings.download_backup_btn'       => 'Baixar Backup do Banco de Dados',
    'settings.download_backup_desc'      => 'Salva um arquivo .sql completo no seu computador. Guarde-o em um local seguro antes de executar atualizações.',
    'settings.schema_update_notice'      => 'Atualizações de banco de dados disponíveis. Baixe um backup acima antes de continuar.',
    'settings.migration_confirm'         => 'Você baixou um backup do banco de dados? Isso aplicará as atualizações de esquema pendentes.',
    'settings.update_db_btn'             => 'Atualizar Banco de Dados',
    'settings.schema_uptodate'           => 'O banco de dados está atualizado.',
    'settings.core_sys_heading'          => 'Configurações do Núcleo do Sistema',
    'settings.sys_name_label'            => 'Nome do Sistema / Aplicativo:',
    'settings.default_lang_label'        => 'Idioma Padrão do Site:',
    'settings.default_lang_desc'         => 'Usado para visitantes e usuários sem idioma preferido. Adicione arquivos em lang/ (ex: pt_BR.php) para mais opções.',
    'settings.captcha_heading'           => 'Configuração de Segurança e CAPTCHA',
    'settings.captcha_provider_label'    => 'Mecanismo Provedor de CAPTCHA:',
    'settings.captcha_none'              => 'Desativado (Sem CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Configurações do Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Configurações do Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Configurações do hCaptcha',
    'settings.site_key_label'            => 'Chave do Site (Pública):',
    'settings.secret_key_label'          => 'Chave Secreta (Privada):',
    'settings.mail_heading'              => 'Configuração de Entrega de E-mail',
    'settings.mail_domain_label'         => 'Domínio de E-mail do Sistema (Fallback):',
    'settings.mail_from_label'           => "Endereço de E-mail 'De' (From) Personalizado:",
    'settings.mail_from_desc'            => 'Um endereço dedicado usado como remetente para e-mails de saída.',
    'settings.mail_driver_label'         => 'Driver / Mecanismo de E-mail:',
    'settings.driver_native'             => 'E-mail Nativo (Postfix Local Relay)',
    'settings.driver_smtp'               => 'SMTP Autenticado (PHPMailer)',
    'settings.smtp_heading'              => 'Configurações do Servidor SMTP',
    'settings.smtp_host_label'           => 'Host SMTP:',
    'settings.smtp_port_label'           => 'Porta:',
    'settings.smtp_encryption_label'     => 'Criptografia:',
    'settings.enc_tls'                   => 'TLS (Porta 587)',
    'settings.enc_ssl'                   => 'SSL (Porta 465)',
    'settings.smtp_user_label'           => 'Nome de Usuário SMTP:',
    'settings.smtp_pass_label'           => 'Senha SMTP (deixe em branco para manter a atual):',
    'settings.save_core_mail_btn'        => 'Salvar Configurações do Núcleo e E-mail',
    'settings.test_mail_heading'         => 'Testar Configuração de E-mail',
    'settings.test_email_label'          => 'Endereço de E-mail do Destinatário:',
    'settings.send_test_btn'             => 'Enviar E-mail de Teste',
    'settings.modules_heading'           => 'Chaves de Módulos do Aplicativo e Controle de Desempenho',
    'settings.modules_subheading'        => 'Ative ou desative recursos para otimizar o desempenho de execução do aplicativo e adaptar-se aos requisitos de implantação específicos.',
    'settings.mod_users'                 => 'Gerenciamento de Usuários e Acesso Multiusuário',
    'settings.mod_users_desc'            => 'Ativa o registro, gerenciamento de usuários e autenticação multiusuário.',
    'settings.mod_leaderboard'           => 'Placar e Gamificação',
    'settings.mod_leaderboard_desc'      => 'Reconhece os esforços de transcrição e fornece pontos de avaliação por estrelas.',
    'settings.mod_leaderboard_note'      => '(Requer Gerenciamento de Usuários e Acesso Multiusuário)',
    'settings.mod_moderation'            => 'Fluxo de Trabalho de Moderação',
    'settings.mod_moderation_desc'       => 'Ativa a revisão de sugestões de edição e fila de moderação.',
    'settings.mod_volunteers'            => 'Portal de Voluntários e Solicitações',
    'settings.mod_volunteers_desc'       => 'Ativa o formulário de intenção de voluntariado público e painel administrativo.',
    'settings.mod_feedback'              => 'Envio de Feedback',
    'settings.mod_feedback_desc'         => 'Ativa o formulário de feedback público e o painel administrativo correspondente.',
    'settings.save_modules_btn'          => 'Salvar Configuração de Módulos',
    'settings.maintenance_heading'       => 'Modo de Manutenção do Sistema',
    'settings.maintenance_toggle'        => 'Ativar Modo de Manutenção (Colocar o site offline)',
    'settings.maintenance_reason_label'  => 'Motivo / Mensagem para os Usuários:',
    'settings.maintenance_eta_label'     => 'Previsão de Retorno (ETA):',
    'settings.save_maintenance_btn'      => 'Salvar Configurações de Manutenção',
    'settings.notices_heading'           => 'Avisos e Anúncios do Site',
    'settings.add_notice_btn'            => '+ Adicionar Novo Aviso',
    'settings.no_notices'                => 'Nenhum aviso configurado.',
    'settings.status_active'             => 'Ativo',
    'settings.status_inactive'           => 'Inativo',
    'settings.notice_content_label'      => 'Conteúdo:',
    'settings.save_notice_btn'           => 'Salvar Aviso',
    'settings.permissions_heading'       => 'Matriz de Funções e Permissões Dinâmicas',
    'settings.permissions_subheading'    => 'As permissões são agrupadas por funcionalidades do sistema. Expanda as seções para configurar os recursos e salve as alterações abaixo.',
    'settings.th_role'                   => 'Função',
    'settings.th_capabilities'           => 'Recursos atribuídos a este grupo',
    'settings.save_permissions_btn'      => 'Salvar Matriz de Permissões',
    'settings.audit_heading'             => 'Navegador de Log de Auditoria do Sistema',
    'settings.audit_subheading'          => 'Inspecione ações de segurança registradas, entradas de dados e moderação. Use as opções de manutenção abaixo para limpar os logs, se necessário.',
    'settings.purge_all_confirm'         => '⚠️ AVISO: Isso excluirá PERMANENTEMENTE TODOS OS LOGS DE AUDITORIA DO SISTEMA. Tem certeza?',
    'settings.clear_all_audit_btn'       => 'Limpar Todos os Logs de Auditoria',
    'settings.purge_records_confirm'     => 'Tem certeza de que deseja limpar todos os logs de auditoria relacionados a registros?',
    'settings.clear_records_audit_btn'   => 'Limpar Apenas Auditoria de Registros',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Carimbo de Data/Hora',
    'settings.th_actor'                  => 'Ator',
    'settings.th_action'                 => 'Ação',
    'settings.th_record_id'              => 'ID do Registro',
    'settings.th_details'                => 'Detalhes',
    'settings.th_ip'                     => 'Endereço IP',
    'settings.no_audit_logs'             => 'Nenhum log de auditoria encontrado.',
    'settings.system_guest'              => 'Sistema / Convidado',
    'settings.audit_limit_note'          => 'Mostrando os últimos 250 logs de auditoria.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Gerenciamento de Contas de Usuários e Moderação do Placar',
    'admin_users.subheading'             => 'Verifique o status do usuário, atribua funções, substitua e-mails, inicie redefinições de senha ou convites, redefina o 2FA ou suspenda contas.',
    'admin_users.manage_templates_btn'   => 'Gerenciar Modelos de E-mail',
    'admin_users.invite_user_btn'        => 'Convidar Novo Usuário',
    'admin_users.th_username'            => 'Nome de Usuário',
    'admin_users.th_email_override'      => 'E-mail e Substituição',
    'admin_users.th_role_assignment'     => 'Atribuição de Função',
    'admin_users.th_score'               => 'Pontuação',
    'admin_users.th_status'              => 'Status',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Ações e Moderação',
    'admin_users.no_users'               => 'Nenhum usuário encontrado.',
    'admin_users.save_email_title'       => 'Salvar Novo Endereço de E-mail',
    'admin_users.verified_label'         => 'Verificado:',
    'admin_users.yes'                    => 'Sim',
    'admin_users.no'                     => 'Não',
    'admin_users.protected_admin'        => 'Administrador principal protegido',
    'admin_users.update_btn'             => 'Atualizar',
    'admin_users.status_active'          => 'Ativo',
    'admin_users.status_suspended'       => 'Suspenso',
    'admin_users.enabled'                => 'Ativado',
    'admin_users.disabled'               => 'Desativado',
    'admin_users.set_score_btn'          => 'Definir Pontuação',
    'admin_users.resend_invite_confirm' => 'Reenviar o e-mail de convite da conta para este usuário?',
    'admin_users.resend_invite_btn'      => 'Reenviar Convite',
    'admin_users.reset_pwd_confirm'      => 'Enviar um link de redefinição de senha para este usuário?',
    'admin_users.reset_password_btn'     => 'Redefinir Senha',
    'admin_users.suspend_confirm'        => 'Suspender o usuário e revogar o acesso devido a uso indevido?',
    'admin_users.suspend_btn'            => 'Suspender Conta',
    'admin_users.reactivate_btn'         => 'Reativar',
    'admin_users.reset_2fa_confirm'      => 'Redefinir e desativar o 2FA para este usuário?',
    'admin_users.reset_2fa_btn'          => 'Redefinir 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Voltar ao Painel de Tickets',
    'view_ticket.ticket_heading_prefix'=> 'Ticket',
    'view_ticket.support_request'      => 'Solicitação de Suporte',
    'view_ticket.submitted_by'         => 'Enviado por:',
    'view_ticket.on_date'              => 'em',
    'view_ticket.submitted_fields'     => 'Campos do formulário enviados:',
    'view_ticket.ticket_status_label'  => 'Status do Ticket:',
    'view_ticket.status_pending'       => 'Pendente',
    'view_ticket.status_progress'      => 'Em Andamento',
    'view_ticket.status_completed'     => 'Concluído',
    'view_ticket.status_rejected'      => 'Rejeitado',
    'view_ticket.dialogue_heading'     => 'Histórico da Conversa',
    'view_ticket.no_replies'           => 'Nenhuma resposta registrada ainda.',
    'view_ticket.admin_label'          => 'Administrador',
    'view_ticket.staff'                => 'Equipe',
    'view_ticket.post_reply_heading'   => 'Publicar Resposta e Notificar Remetente',
    'view_ticket.reply_placeholder'    => 'Escreva sua resposta aqui...',
    'view_ticket.send_reply_btn'       => 'Enviar Resposta e E-mail ao Remetente',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Solicitações de Voluntários e Fluxo de Trabalho',
    'volunteer_dashboard.subheading'         => 'Revise solicitações, agende conversas, registre notas de entrevistas e aprove candidatos no sistema.',
    'volunteer_dashboard.manage_emails_btn' => 'Gerenciar Modelos de E-mail',
    'volunteer_dashboard.manage_schema_btn' => 'Gerenciar Esquema do Formulário',
    'volunteer_dashboard.th_status'          => 'Status',
    'volunteer_dashboard.th_name'            => 'Nome',
    'volunteer_dashboard.th_interview_notes'=> 'Entrevista / Notas',
    'volunteer_dashboard.no_submissions'     => 'Nenhuma solicitação de voluntário encontrada.',
    'volunteer_dashboard.volunteer_prefix'   => 'Voluntário',
    'volunteer_dashboard.chat_label'         => 'Conversa:',
    'volunteer_dashboard.notes_label'        => 'Notas:',
    'volunteer_dashboard.no_notes'           => 'Nenhuma nota ainda',
    'volunteer_dashboard.chat_notes_btn'     => 'Conversa e Notas',
    'volunteer_dashboard.accept_title'       => 'Aprovar via Sistema de Convite de Usuário',
    'volunteer_dashboard.accept_invite_btn'  => 'Aprovar e Enviar Convite',
    'volunteer_dashboard.delete_confirm'     => 'Excluir este registro de voluntário?',
    'volunteer_dashboard.modal_heading'      => 'Gerenciar Entrevista e Notas do Candidato',
    'volunteer_dashboard.modal_status_label'=> 'Status da Solicitação:',
    'volunteer_dashboard.status_pending'     => 'Aguardando Revisão',
    'volunteer_dashboard.status_chat'        => 'Conversa Agendada',
    'volunteer_dashboard.status_accepted'    => 'Aprovado',
    'volunteer_dashboard.status_rejected'    => 'Rejeitado',
    'volunteer_dashboard.modal_date_label'   => 'Data e Hora da Conversa Agendada:',
    'volunteer_dashboard.modal_notes_label'  => 'Notas da Entrevista / Reunião:',
    'volunteer_dashboard.modal_notes_placeholder' => 'Registre feedback da conversa aqui...',
    'volunteer_dashboard.save_changes_btn'   => 'Salvar Alterações',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Proibido: A visualização pública não está ativada.',
    'api_search.error_unauthorized_table' => 'Acesso não autorizado à tabela.',
    'api_search.no_records'              => 'Nenhum registro encontrado neste diretório.',
    'api_search.history_btn'             => 'Histórico',
    'api_search.suggest_edit_btn'        => 'Sugerir Edição',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Voltar à Página Inicial Pública',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ---------------------------------------------------               -------
    'feedback.hp_label'              => 'Deixar em branco',
    'feedback.first_name_label'      => 'Nome:',
    'feedback.surname_label'         => 'Sobrenome:',
    'feedback.email_label'           => 'Endereço de E-mail:',
    'feedback.subject_label'         => 'Assunto / Título da Consulta:',
    'feedback.required_title'        => 'Campo Obrigatório',
    'feedback.select_placeholder'    => '-- Selecionar --',
    'feedback.multi_select_hint'     => 'Pressione Ctrl ou Cmd para selecionar vários.',
    'feedback.submit_btn'            => 'Enviar Ticket',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Erro de segurança: Assinatura de cliente suspeita.',
    'security_engine.err_access_denied'    => 'Erro de segurança: Acesso negado.',
    'security_engine.err_rate_limit'       => 'Muitas solicitações deste endereço IP. Tente novamente mais tarde.',
    'security_engine.err_excessive_links'  => 'Envio bloqueado devido a excesso de links.',
    'security_engine.err_complete_captcha' => 'Por favor, complete o desafio de segurança CAPTCHA.',
    'security_engine.err_captcha_failed'   => 'A verificação do CAPTCHA falhou. Tente novamente.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Instalação Concluída',
    'install.complete_heading'           => 'Instalação Concluída',
    'install.complete_desc'              => 'Este site já está configurado. O instalador foi bloqueado para evitar novas execuções.',
    'install.login_link'                 => 'Entrar',
    'install.home_link'                  => 'Ir para o Site',
    'install.delete_folder_hint'         => 'Para maior segurança, você pode excluir ou renomear a pasta <code>install</code>.',
    'install.msg_db_ready'               => 'Banco de dados pronto. Crie sua conta de administrador para concluir.',
    'install.err_config_load'            => 'Não foi possível usar a configuração existente:',
    'install.err_write_permission'       => 'O PHP não pode criar arquivos nesta pasta do projeto.',
    'install.detail_prefix'              => 'Detalhe:',
    'install.err_db_required'            => 'O nome do banco de dados e o usuário são obrigatórios.',
    'install.err_db_not_empty'           => 'Este banco de dados não está vazio. Use um novo banco de dados vazio (ou limpe todas as tabelas) e tente novamente.',
    'install.msg_schema_imported'        => 'Banco de dados conectado e esquema importado. Crie sua conta de administrador.',
    'install.err_complete_db_first'      => 'Conclua a etapa do banco de dados primeiro.',
    'install.err_admin_required'         => 'Todos os campos de administrador são obrigatórios.',
    'install.err_invalid_email'          => 'Endereço de e-mail inválido.',
    'install.err_password_length'        => 'A senha deve ter pelo menos 8 caracteres.',
    'install.err_passwords_match'        => 'As senhas não coincidem.',
    'install.err_admin_save_failed'      => 'Falha ao salvar o usuário administrador. Verifique a estrutura da tabela de usuários.',
    'install.msg_installation_complete' => 'Instalação concluída.',
    'install.page_title'                 => 'Instalação — Registro Paroquial',
    'install.heading'                    => 'Instalação',
    'install.subheading'                 => 'Configuração inicial <strong>apenas para esta pasta do aplicativo</strong>. Use um banco de dados MySQL vazio.',
    'install.done_heading'               => 'Concluído',
    'install.done_message'               => 'Instalação concluída. O instalador está bloqueado.',
    'install.admin_heading'              => 'Conta de Administrador do Site',
    'install.admin_subheading'           => 'Estas são as credenciais de login para <strong>este site</strong> (não para o banco de dados).',
    'install.admin_username_label'       => 'Nome de Usuário do Administrador',
    'install.admin_email_label'          => 'E-mail do Administrador',
    'install.admin_password_label'       => 'Senha do Administrador (mín. 8 caracteres)',
    'install.admin_confirm_password_label' => 'Confirmar Senha do Administrador',
    'install.finish_btn'                 => 'Concluir Instalação',
    'install.db_heading'                 => 'Conexão com o Banco de Dados',
    'install.db_hint'                    => 'Use os detalhes do MySQL do seu <strong>painel de controle de hospedagem</strong>. Não é o login de administrador do site.',
    'install.db_host_label'              => 'Host do Banco de Dados',
    'install.db_name_label'              => 'Nome do Banco de Dados',
    'install.db_user_label'              => 'Usuário do Banco de Dados',
    'install.db_pass_label'              => 'Senha do Banco de Dados',
    'install.db_submit_btn'              => 'Criar Tabelas e Continuar',
    'install.req_heading'                => '1. Requisitos',
    'install.req_php'                    => 'PHP 8.0+ (encontrado %s)',
    'install.req_pdo'                    => 'Extensão PDO MySQL',
    'install.req_logs'                   => 'Pasta de logs com permissão de escrita (ou pasta do projeto)',
    'install.req_probe'                  => 'Capacidade de criar arquivos nesta pasta do projeto',
    'install.continue_btn'               => 'Continuar',
    'install.req_fail_msg'               => 'Corrija as verificações que falharam e recarregue esta página.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Visualização do Placar',
    'leaderboard.heading'         => 'Placar de Participação da Comunidade',
    'leaderboard.subheading'      => 'Reconhecendo os esforços dos membros da nossa comunidade que ajudam a coletar, transcrever ou gerenciar registros.',
    'leaderboard.th_rank'         => 'Classificação',
    'leaderboard.th_contributor'  => 'Colaborador',
    'leaderboard.th_role'         => 'Função',
    'leaderboard.th_score'        => 'Pontuação',
    'leaderboard.no_users'        => 'Nenhum usuário ativo encontrado no placar ainda.',
    'leaderboard.medal_gold'      => 'Medalha de Ouro',
    'leaderboard.medal_silver'    => 'Medalha de Prata',
    'leaderboard.medal_bronze'    => 'Medalha de Bronze',
    'leaderboard.medal_ribbon'    => 'Fita de Nível 4',
    'leaderboard.medal_rosette'   => 'Roseta de Nível 5',
    'leaderboard.medal_trophy'    => 'Troféu de Nível 6',
    'leaderboard.medal_star'      => 'Estrela de Nível 7',
    'leaderboard.medal_military'  => 'Medalha Militar de Nível 8',
    'leaderboard.medal_glowing'   => 'Estrela Brilhante de Nível 9',
    'leaderboard.medal_crown'     => 'Coroa de Nível 10',
    'leaderboard.you_badge'       => '(Você)',
    'leaderboard.default_role'    => 'Usuário',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Registros paroquiais compilados de fontes históricas em domínio público.',
    'footer.software_notice'  => 'Plataforma de software de código aberto sob licença MIT.',
    'footer.rights_reserved'  => 'Todos os direitos reservados.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Banco de Dados de Registros Paroquiais',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Fechar aviso',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Nenhum registro especificado.',
    'record_history.exit_not_found'        => 'Registro não encontrado.',
    'record_history.heading_prefix'        => 'Histórico e Log de Auditoria: Registro',
    'record_history.return_btn'            => 'Voltar',
    'record_history.directory_table_label'=> 'Tabela do Diretório:',
    'record_history.subheading_lifecycle' => 'Mostra o ciclo de vida social de alterações, sugestões e evidências associadas a este registro.',
    'record_history.snapshot_heading'      => 'Instantâneo dos valores ativos atuais',
    'record_history.empty_value'           => '[Vazio]',
    'record_history.timeline_heading'      => 'Linha do Tempo de Eventos e Atividades',
    'record_history.no_history'            => 'Nenhum evento de auditoria histórico registrado especificamente para este registro ainda.',
    'record_history.purge_confirm'         => 'Excluir esta entrada específica de log de auditoria?',
    'record_history.purge_btn'             => 'Limpar Log',
    'record_history.actor_label'           => 'Ator:',
    'record_history.system_guest'          => 'Sistema / Convidado',
    'record_history.target_column'         => 'Coluna Alvo:',
    'record_history.proposed_value'        => 'Valor Proposto:',
    'record_history.reasoning_evidence'    => 'Justificativa / Evidência:',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Banco de dados atualizado com sucesso! %d migrações aplicadas.',
    'update_database.msg_uptodate'     => 'O banco de dados já está atualizado.',
    'update_database.err_failed'       => 'A migração falhou:',
    'update_database.page_title'       => 'Atualização do Sistema Necessária — Registro Paroquial',
    'update_database.heading'          => '⚠️ Atualização do Sistema Necessária',
    'update_database.subheading'       => 'O esquema do banco de dados do aplicativo está desatualizado e requer uma atualização de esquema antes de prosseguir.',
    'update_database.current_version'  => 'Versão atual do esquema:',
    'update_database.latest_version'   => 'Última versão disponível:',
    'update_database.proceed_login'    => 'Ir para a Página de Login',
    'update_database.confirm_prompt'   => 'Você fez backup do seu banco de dados? Pressione OK para aplicar as atualizações de esquema pendentes.',
    'update_database.update_btn'       => 'Atualizar Banco de Dados Agora',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Credenciais inválidas ou acesso à conta restrito.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'O campo obrigatório \'%s\' não pode ficar em branco.',
    'save_data_entry.audit_created_prefix' => 'Registro criado na tabela com ID %d.',
    'save_data_entry.msg_success'          => 'Registro adicionado com sucesso!',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ---------------------------------------------------               -------
    'save_public_suggestion.err_spam_detected'  => 'Spam detectado. Envio rejeitado.',
    'save_public_suggestion.err_field_required' => 'Este campo é obrigatório e não pode ser enviado em branco.',
    'save_public_suggestion.msg_success'        => 'Sua sugestão de edição foi enviada com sucesso para a fila de moderação. Obrigado!',
    'save_public_suggestion.err_failed_submit'  => 'Falha ao enviar a sugestão de edição. Tente novamente.',
    'save_public_suggestion.err_invalid_column' => 'Coluna especificada inválida.',
    'save_public_suggestion.err_invalid_params' => 'Parâmetros de envio de registro inválidos.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'AAAA-MM-DD (ou ano parcial)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/AAAA (ou ano parcial)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/AAAA (ou ano parcial)',
    'data_entry.no_tables_heading'    => '⚠️ Nenhuma Tabela de Banco de Dados Encontrada',
    'data_entry.no_tables_desc'       => 'Nenhuma tabela de banco de dados ativa configurada para entrada de dados.',
    'data_entry.admin_tables_prompt'  => 'Como administrador, acesse <strong>Gerenciar Tabelas</strong> para criar uma tabela e adicionar uma coluna antes de inserir registros.',
    'data_entry.go_manage_tables'     => 'Ir para Gerenciar Tabelas',
    'data_entry.contact_admin_tables' => 'Entre em contato com um administrador para configurar tabelas e colunas.',
    'data_entry.no_cols_heading'      => '⚠️ Nenhuma Coluna Configurada',
    'data_entry.no_cols_desc'         => 'Existem tabelas no sistema, mas nenhuma coluna de dados foi definida para a tabela ativa.',
    'data_entry.admin_cols_prompt'    => 'Como administrador, acesse <strong>Gerenciar Tabelas</strong> para adicionar pelo menos uma coluna.',
    'data_entry.contact_admin_cols'   => 'Entre em contato com o administrador para configurar as colunas desta tabela.',
    'data_entry.active_table_label'   => 'Tabela de entrada de dados ativa:',
    'data_entry.add_entry_summary'    => '➕ Adicionar nova entrada de dados (Clique para expandir/recolher)',
    'data_entry.bool_yes_true'        => 'Sim / Verdadeiro',
    'data_entry.bool_no_false'        => 'Não / Falso',
    'data_entry.bool_male'            => 'Masculino',
    'data_entry.bool_female'          => 'Feminino',
    'data_entry.bool_true'            => 'Verdadeiro',
    'data_entry.bool_false'           => 'Falso',
    'data_entry.bool_tick'            => '✔ (Marcado)',
    'data_entry.bool_cross'           => '✘ (Cruz)',
    'data_entry.date_title_hint'      => 'Aceita datas completas ou parciais (ex: 1842 ou 1842-05)',
    'data_entry.enter_value_placeholder' => 'Insira o valor...',
    'data_entry.submit_data_btn'      => 'Enviar Dados',
    'data_entry.shortcuts_tip'        => '💡 Dica: Pressione <strong>Ctrl + Enter</strong> para enviar ou <strong>Esc</strong> para limpar o campo atual.',
    'data_entry.dup_heading'          => '⚠️ Aviso de Possível Duplicata',
    'data_entry.dup_desc'             => 'Encontramos registros semelhantes no sistema:',
    'data_entry.dup_item_format'      => 'ID do Registro: %d — Valor: %s',
    'data_entry.dup_prompt'           => 'Deseja prosseguir e salvar esta duplicata mesmo assim?',
    'data_entry.dup_confirm_btn'      => 'Sim, confirmar e salvar duplicata',
    'data_entry.search_summary'       => '🔍 Pesquisar e Filtrar Registros Existentes (Clique para expandir/recolher)',
    'data_entry.date_to_label'        => 'até',
    'data_entry.filter_all_option'    => '-- Todos --',
    'data_entry.filter_placeholder'   => 'Filtrar...',
    'data_entry.apply_filters_btn'    => 'Aplicar Filtros de Busca',
    'data_entry.reset_filter_btn'     => 'Redefinir Filtro',
    'data_entry.csv_entire_btn'       => 'Baixar CSV Completo',
    'data_entry.json_entire_btn'      => 'Baixar JSON Completo',
    'data_entry.copy_entire_btn'      => 'Copiar Tabela Completa',
    'data_entry.csv_filtered_btn'     => 'Baixar CSV Filtrado',
    'data_entry.json_filtered_btn'     => 'Baixar JSON Filtrado',
    'data_entry.copy_filtered_btn'    => 'Copiar Tabela Filtrada',
    'data_entry.clipboard_alert'      => 'Dados da tabela copiados para a área de transferência! Você pode colá-los no Excel ou Google Sheets.',
    'data_entry.existing_records_heading' => 'Tabela de Registros Existentes',
    'data_entry.th_added_by'          => 'Adicionado por',
    'data_entry.th_date_created'      => 'Data de Criação',
    'data_entry.no_records'           => 'Nenhum registro encontrado.',
    'data_entry.na_value'             => 'N/D',
    'data_entry.page_label'           => 'Página:',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Recuperação de Senha',
    'forgot_password.heading'         => 'Redefina sua Senha',
    'forgot_password.subheading'      => 'Insira o e-mail da sua conta abaixo para receber um link seguro de redefinição de senha.',
    'forgot_password.email_label'     => 'Endereço de E-mail:',
    'forgot_password.submit_btn'      => 'Enviar Link de Redefinição',
    'forgot_password.back_login_link' => 'Voltar à página de login',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Login de Usuário',
    'login.heading'              => 'Entrar na Conta',
    'login.username_label'       => 'Nome de usuário ou e-mail:',
    'login.password_label'       => 'Senha:',
    'login.submit_btn'           => 'Entrar',
    'login.forgot_password_link' => 'Esqueceu sua senha?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ---------------------------------------------------               -------
    'onboarding.page_title'        => 'Bem-vindo — Assistente de Configuração de Conta',
    'onboarding.heading'           => 'Bem-vindo à equipe!',
    'onboarding.subheading'        => 'Antes de começar, reserve um momento para configurar suas preferências de exibição regional e privacidade.',
    'onboarding.timezone_label'    => 'Fuso Horário / Região:',
    'onboarding.date_format_label' => 'Formato de Exibição de Data:',
    'onboarding.time_format_label' => 'Formato de Horógio (Exibição de Hora):',
    'onboarding.time_24'          => '24 horas (ex: 16:07)',
    'onboarding.time_12'          => '12 horas AM/PM (ex: 04:07 PM)',
    'onboarding.time_none'        => 'Apenas Data (Ocultar hora completamente)',
    'onboarding.attribution_label' => 'Preferência de exibição no placar:',
    'onboarding.attribution_desc1' => 'Controla como seu nome aparece no placar público e nos registros.',
    'onboarding.attr_anon_title'   => 'Anônimo:',
    'onboarding.attr_anon_text'    => 'Mostra iniciais e um número aleatório para todos.',
    'onboarding.attr_public_title' => 'Público:',
    'onboarding.attr_public_text'  => 'Mostra seu nome completo para todos.',
    'onboarding.attr_vol_title'    => 'Apenas Voluntários:',
    'onboarding.attr_vol_text'     => 'Mostra iniciais para o público, mas seu nome completo para voluntários, moderadores e administradores logados.',
    'onboarding.attr_opt_anon'     => 'Anônimo (Iniciais e número aleatório)',
    'onboarding.attr_opt_public'   => 'Público (Exibir nome completo)',
    'onboarding.attr_opt_vol'      => 'Apenas voluntários',
    'onboarding.submit_btn'        => 'Salvar Preferências e Continuar',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ---------------------------------------------------               -------
    'profile.aria_region'          => 'Gerenciamento de Perfil de Usuário',
    'profile.heading'              => 'Perfil de Usuário e Segurança',
    'profile.personal_details_heading' => 'Detalhes Pessoais',
    'profile.language_label'       => 'Idioma Preferido:',
    'profile.lang_site_default'    => 'Padrão do Site',
    'profile.update_details_btn'   => 'Atualizar Detalhes Pessoais',
    'profile.email_heading'        => 'Endereço de E-mail',
    'profile.current_email_label'  => 'E-mail atual:',
    'profile.email_verified'       => '(Verificado)',
    'profile.email_unverified'     => '(Não verificado - Verifique sua caixa de entrada)',
    'profile.change_email_label'   => 'Alterar Endereço de E-mail:',
    'profile.aria_new_email'       => 'Novo Endereço de E-mail',
    'profile.update_email_btn'     => 'Atualizar E-mail e Verificar',
    'profile.password_heading'     => 'Alterar Senha',
    'profile.current_password_label' => 'Senha Atual:',
    'profile.new_password_label'   => 'Nova Senha (mín. 8 caracteres):',
    'profile.confirm_password_label' => 'Confirmar Nova Senha:',
    'profile.show_passwords_label' => 'Mostrar senhas em texto visível',
    'profile.update_password_btn'  => 'Atualizar Senha',
    'profile.tfa_heading'          => 'Autenticação de Dois Fatores (2FA)',
    'profile.tfa_status_label'     => 'Status:',
    'profile.tfa_enabled'          => 'Ativado',
    'profile.tfa_disabled'         => 'Desativado',
    'profile.setup_tfa_btn'        => 'Configurar Google Authenticator',
    'profile.tfa_active_desc'      => 'O 2FA está protegendo ativamente o login da sua conta.',
    'profile.backup_codes_heading' => 'Seus novos códigos de backup de segurança',
    'profile.download_codes_btn'   => 'Baixar novos códigos como .txt',
    'profile.generate_codes_confirm' => 'Tem certeza? Isso invalidará quaisquer códigos de backup existentes.',
    'profile.generate_codes_btn'   => 'Gerar Novos Códigos de Backup',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Registro de Usuário',
    'register.heading'        => 'Registrar Nova Conta',
    'register.username_label' => 'Nome de Usuário:',
    'register.submit_btn'     => 'Registrar',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ---------------------------------------------------               -------
    'set_password.exit_invalid_token'        => 'Token de configuração inválido ou ausente.',
    'set_password.exit_expired_token'        => 'Este link de senha é inválido ou expirou.',
    'set_password.proceed_login_btn'         => 'Ir para a Página de Login',
    'set_password.aria_region'               => 'Definir Senha',
    'set_password.heading_format'            => 'Definir Senha para %s',
    'set_password.subheading_format'         => 'Bem-vindo à sua nova conta, %s! Por favor, escolha sua senha abaixo.',
    'set_password.new_password_label'        => 'Nova Senha (mín. 8 caracteres):',
    'set_password.confirm_password_label'    => 'Confirmar Senha:',
    'set_password.show_password_label'       => 'Mostrar Senha',
    'set_password.save_password_btn'         => 'Salvar Senha',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ---------------------------------------------------               -------
    'setup_2fa.aria_region'      => 'Assistente de Configuração de 2FA',
    'setup_2fa.heading'          => 'Configurar Google Authenticator',
    'setup_2fa.subheading'       => 'Escaneie o código QR abaixo com seu aplicativo autenticador.',
    'setup_2fa.qr_alt'           => 'Código QR para configuração de 2FA',
    'setup_2fa.manual_prompt'    => 'Ou insira esta chave secreta manualmente:',
    'setup_2fa.backup_heading'   => 'Códigos de Recuperação de Segurança de Emergência',
    'setup_2fa.backup_desc'      => 'Guarde estes códigos de backup em um local seguro. Cada código pode ser usado <strong>apenas uma vez</strong> caso você perca o acesso ao seu aplicativo:',
    'setup_2fa.download_btn'     => 'Baixar códigos como .txt',
    'setup_2fa.code_label'       => 'Insira o código de 6 dígitos do aplicativo para verificar e ativar:',
    'setup_2fa.aria_code_input'  => 'Código de verificação de 6 dígitos',
    'setup_2fa.submit_btn'       => 'Verificar e Ativar 2FA',
    'setup_2fa.cancel_link'      => 'Cancelar e Voltar ao Perfil',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ---------------------------------------------------               -------
    'suggest_edit.aria_region'          => 'Sugerir Edição',
    'suggest_edit.heading_prefix'       => 'Sugerir Edição para o Registro',
    'suggest_edit.return_btn'           => 'Voltar ao Registro',
    'suggest_edit.success_msg_suffix'   => 'Você pode enviar outra edição abaixo ou usar o link de retorno acima quando terminar.',
    'suggest_edit.current_values_heading' => 'Valores Atuais:',
    'suggest_edit.empty_label'          => '(vazio)',
    'suggest_edit.submit_heading'       => 'Enviar Novo Valor Proposto e Evidência',
    'suggest_edit.confirm_prompt'       => 'Tem certeza de que deseja enviar esta sugestão de edição para revisão do administrador?',
    'suggest_edit.select_column_label'  => 'Selecionar coluna para editar:',
    'suggest_edit.reasoning_label'      => 'Evidência / Justificativa / Notas da Fonte:',
    'suggest_edit.reasoning_placeholder'=> 'Forneça contexto, citação de fonte ou razão para esta alteração...',
    'suggest_edit.submit_btn'           => 'Enviar Sugestão para Revisão',
    'suggest_edit.proposed_value_label' => 'Novo valor proposto:',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ---------------------------------------------------               -------
    'verify_2fa.aria_region'     => 'Verificação de 2FA',
    'verify_2fa.heading'         => 'Autenticação de Dois Fatores',
    'verify_2fa.subheading'      => 'Insira o código de 6 dígitos do seu aplicativo autenticador ou um código de backup de segurança.',
    'verify_2fa.code_label'      => 'Código de Verificação / Código de Segurança:',
    'verify_2fa.aria_code_input' => 'Insira o código de verificação ou de segurança',
    'verify_2fa.submit_btn'      => 'Verificar e Entrar',

    // ------------------------------------------------------------------
    // Verify Email
    // ---------------------------------------------------               -------
    'verify_email.err_no_token'         => 'Nenhum token de verificação fornecido.',
    'verify_email.err_invalid_token'    => 'Token de verificação inválido.',
    'verify_email.msg_already_verified' => 'Seu e-mail já foi verificado. Você pode entrar.',
    'verify_email.err_expired_token'    => 'Este link de verificação expirou (limite de 24 horas excedido). Registre-se novamente ou solicite um novo link.',
    'verify_email.msg_success'          => 'E-mail verificado com sucesso! Sua conta agora está ativa. Prossiga para o login.',
    'verify_email.err_update_failed'    => 'Ocorreu um erro ao verificar seu e-mail. Tente novamente.',
    'verify_email.aria_region'          => 'Status de Verificação de E-mail',
    'verify_email.heading'              => 'Status de Verificação de E-mail',
    'verify_email.login_btn'            => 'Clique aqui para entrar',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Formulário de Voluntário',
    'volunteer.honeypot_label'       => 'Deixe este campo em branco:',
    'volunteer.required_field_title'=> 'Campo Obrigatório',
    'volunteer.multi_select_hint'    => 'Pressione Ctrl ou Cmd para selecionar vários.',
    'volunteer.submit_btn'           => 'Enviar Solicitação de Voluntariado',
];
