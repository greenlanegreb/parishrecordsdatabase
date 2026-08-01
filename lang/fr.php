<?php
// lang/fr.php - French (Français)
return [

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav.login'                  => 'Connexion',
    'nav.logout'                 => 'Déconnexion',
    'nav.feedback'               => 'Commentaires',
    'nav.volunteer'              => 'Bénévolat',
    'nav.leaderboard'            => 'Classement',
    'nav.search'                 => 'Rechercher',
    'nav.settings'               => 'Paramètres du site',
    'nav.high_contrast'          => 'Haut contraste',
    'nav.low_contrast'           => 'Bas contraste',
    'nav.welcome'                => 'Bienvenue,',
    'nav.data_entry'             => 'Saisie de données',
    'nav.moderation'             => 'Modération',
    'nav.invite_user'            => 'Inviter un utilisateur',
    'nav.manage_users'           => 'Gérer les utilisateurs',
    'nav.manage_tables'          => 'Gérer les tables',
    'nav.volunteer_dashboard'    => 'Tableau de bord des bénévoles',
    'nav.feedback_dashboard'     => 'Tableau de bord des commentaires',
    'nav.leaderboard_score'      => 'Score du classement',

    // ------------------------------------------------------------------
    // Public search (index)
    // ------------------------------------------------------------------
    'search.heading'             => 'Filtres de recherche multicolonnes',
    'search.reset'               => 'Réinitialiser la recherche',
    'search.export_csv'          => 'Télécharger les résultats filtrés au format CSV',
    'search.no_records'          => 'Aucun enregistrement trouvé dans cette table.',
    'search.load_error'          => 'Impossible de charger les résultats. Veuillez réessayer.',

    // ------------------------------------------------------------------
    // Common buttons
    // ------------------------------------------------------------------
    'btn.submit'                 => 'Soumettre',
    'btn.cancel'                 => 'Annuler',
    'btn.save'                   => 'Enregistrer',
    'btn.delete'                 => 'Supprimer',

    // actions/save_feedback.php & feedback.php Strings
    'feedback.success_message'    => 'Merci ! Vos commentaires ont été envoyés avec succès.',
    'feedback.error_all_fields'   => 'Tous les champs sont obligatoires.',
    'feedback.error_invalid_email'=> 'Veuillez fournir une adresse e-mail valide.',
    'feedback.error_save_failed'  => 'Une erreur s’est produite lors de l’enregistrement de vos commentaires. Veuillez réessayer.',

    // ------------------------------------------------------------------
    // Index / Public Directory Page
    // ------------------------------------------------------------------
    'index.no_tables_heading'          => 'Aucune table de base de données trouvée',
    'index.no_tables_desc'             => 'Le système ne comporte actuellement aucune table de base de données active configurée.',
    'index.admin_create_table_guide'   => 'En tant qu’administrateur, veuillez vous rendre dans l’option de menu <strong>Gérer les tables</strong> pour créer une table, puis ajoutez au moins une colonne à cette table avant que les enregistrements ne puissent être consultés ou saisis.',
    'index.go_to_manage_tables'        => 'Aller à Gérer les tables',
    'index.contact_admin_tables'       => 'Veuillez contacter un administrateur pour configurer les tables et les colonnes de la base de données.',
    'index.guest_login_tables_guide'   => 'Veuillez <a href=":login_link">vous connecter</a> en tant qu’administrateur, aller dans la section <strong>Gérer les tables</strong> pour créer une table, puis ajouter au moins une colonne.',
    'index.no_columns_heading'         => 'Aucune colonne configurée',
    'index.no_columns_desc'            => 'Des tables existent dans le système, mais aucune colonne de données n’a été définie pour la table active.',
    'index.admin_add_columns_guide'    => 'En tant qu’administrateur, veuillez vous rendre dans l’option de menu <strong>Gérer les tables</strong> pour ajouter au moins une colonne à votre table.',
    'index.contact_admin_columns'      => 'Veuillez contacter un administrateur pour configurer les colonnes de cette table.',
    'index.select_directory_database'  => 'Sélectionner la base de données du répertoire :',
    'index.opt_yes_true'               => 'Oui / Vrai',
    'index.opt_no_false'               => 'Non / Faux',
    'index.opt_male'                   => 'Homme',
    'index.opt_female'                 => 'Femme',
    'index.opt_true'                   => 'Vrai',
    'index.opt_false'                  => 'Faux',
    'index.opt_tick'                   => '✔ (Coche)',
    'index.opt_cross'                  => '✘ (Croix)',
    'index.option_all'                 => '-- Tout --',
    'index.date_to_label'              => 'à',
    'index.search_placeholder'         => 'Rechercher...',
    'index.download_entire_csv'        => 'Télécharger le CSV complet',
    'index.download_entire_json'       => 'Télécharger le JSON complet',
    'index.copy_entire_table'          => 'Copier toute la table',
    'index.download_filtered_csv'      => 'Télécharger le CSV filtré',
    'index.download_filtered_json'     => 'Télécharger le JSON filtré',
    'index.copy_filtered_table'        => 'Copier la table filtrée',
    'index.th_record_id'               => 'ID de l’enregistrement',
    'index.th_created_by'              => 'Créé par',
    'index.th_date_added'              => 'Date d’ajout',
    'index.th_actions'                 => 'Actions',
    'index.modal_heading'              => 'Suggérer une correction d’enregistrement',
    'index.modal_desc'                 => 'Soumettez une correction ou des informations complémentaires pour cet enregistrement. Elle sera examinée par notre équipe de modération.',
    'index.modal_target_column'        => 'Colonne cible :',
    'index.modal_proposed_value'       => 'Correction / Valeur proposée :',
    'index.modal_input_placeholder'    => 'Entrez les informations mises à jour...',
    'index.modal_submit_btn'           => 'Soumettre la suggestion',
    'index.clipboard_success'          => 'Données de la table copiées dans le presse-papiers ! Vous pouvez les coller directement dans Excel ou Google Sheets.',

    // ------------------------------------------------------------------
    // Admin: Create User / Invite Form
    // ------------------------------------------------------------------
    'create_user.heading'              => 'Formulaire d’invitation de nouvel utilisateur',
    'create_user.subheading'           => 'Cela générera un lien de configuration sécurisé de 24 heures et l’enverra directement par e-mail à l’utilisateur.',
    'create_user.first_name'           => 'Prénom :',
    'create_user.surname'              => 'Nom de famille :',
    'create_user.username_label'       => 'Nom d’utilisateur (Facultatif) :',
    'create_user.username_placeholder' => 'Laisser vide pour générer automatiquement',
    'create_user.username_help'        => 'Si laissé vide, un nom d’utilisateur unique sera généré automatiquement à partir de son nom.',
    'create_user.email_label'          => 'Adresse e-mail :',
    'create_user.role_label'           => 'Rôle de l’utilisateur :',
    'create_user.submit_btn'           => 'Créer l’utilisateur et envoyer l’invitation',

    // ------------------------------------------------------------------
    // Admin: Feedback / Support Tickets Dashboard
    // ------------------------------------------------------------------
    'feedback_dash.heading'              => 'Tableau de bord des tickets de support et commentaires',
    'feedback_dash.subheading'           => 'Gérer les demandes de support public, mettre à jour les statuts et participer à un dialogue direct.',
    'feedback_dash.manage_emails'        => 'Gérer les modèles d’e-mails',
    'feedback_dash.manage_schema'        => 'Gérer le schéma du formulaire de tickets',
    'feedback_dash.th_ticket_date'       => 'ID du ticket / Date',
    'feedback_dash.th_submitter'         => 'Soumetteur',
    'feedback_dash.th_subject_info'      => 'Sujet / Infos initiales',
    'feedback_dash.th_status'            => 'Statut',
    'feedback_dash.no_tickets'           => 'Aucun ticket de commentaire trouvé.',
    'feedback_dash.anonymous'            => 'Anonyme',
    'feedback_dash.default_subject'      => 'Demande générale',
    'feedback_dash.open_ticket_btn'      => 'Ouvrir le ticket et le dialogue',
    'feedback_dash.delete_confirm'       => 'Supprimer ce ticket de support et toutes ses réponses ?',
    'feedback_dash.msg_deleted'          => 'Ticket #:id supprimé avec succès.',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Email Templates
    // ------------------------------------------------------------------
    'feedback_emails.heading'            => 'Modèles d’e-mails et déclencheurs des tickets de support',
    'feedback_emails.subheading'         => 'Personnalisez les notifications automatiques par e-mail envoyées pendant le cycle de vie des tickets. Utilisez des accolades pour les espaces réservés dynamiques.',
    'feedback_emails.back_to_dashboard' => 'Retour au tableau de bord des tickets de commentaires',
    'feedback_emails.email_subject'      => 'Sujet de l’e-mail :',
    'feedback_emails.email_body'         => 'Modèle du corps de l’e-mail :',
    'feedback_emails.save_template_btn' => 'Enregistrer le modèle',
    'feedback_emails.placeholders_heading' => 'Espaces réservés disponibles',
    'feedback_emails.placeholders_desc' => 'Vous pouvez utiliser ces balises n’importe où dans vos modèles de sujet ou de corps :',
    'feedback_emails.fixed_tags'         => 'Balises principales fixes :',
    'feedback_emails.custom_tags'        => 'Balises de schéma personnalisées :',
    'feedback_emails.custom_tags_desc'   => 'Générées automatiquement à partir des champs de votre générateur de formulaires de tickets :',

    // ------------------------------------------------------------------
    // Admin: Manage Feedback Ticket Schema & Fields
    // ------------------------------------------------------------------
    'feedback_schema.heading'                => 'Gestion du schéma du formulaire de commentaires',
    'feedback_schema.subheading'             => 'Configurez les champs personnalisés, les types de données, les limites de caractères, les sous-types, les options et les paramètres généraux de présentation du formulaire.',
    'feedback_schema.settings_summary'       => 'Configurer le titre du formulaire et le texte d’introduction',
    'feedback_schema.form_title_label'       => 'Titre du formulaire :',
    'feedback_schema.form_intro_label'       => 'Texte d’introduction / Description :',
    'feedback_schema.save_settings_btn'      => 'Enregistrer les paramètres du formulaire',
    'feedback_schema.edit_field_title'       => 'Modifier le champ du ticket :',
    'feedback_schema.add_field_title'        => '+ Ajouter un nouveau champ de formulaire de ticket',
    'feedback_schema.field_name_label'       => 'Libellé / Nom du champ :',
    'feedback_schema.data_type_label'        => 'Type de données :',
    'feedback_schema.type_varchar'           => 'VARCHAR (Texte court)',
    'feedback_schema.type_text'              => 'TEXT (Paragraphe long / Message)',
    'feedback_schema.type_int'               => 'INT (Nombre entier)',
    'feedback_schema.type_boolean'           => 'BOOLEAN (Indicateur Oui/Non)',
    'feedback_schema.type_date'              => 'DATE (Date du calendrier)',
    'feedback_schema.subtype_label'          => 'Sous-type de champ / Style de rendu d’entrée :',
    'feedback_schema.subtype_standard'       => '-- Standard --',
    'feedback_schema.subtype_standard_lower'=> 'standard',
    'feedback_schema.options_label'          => 'Options (séparées par des virgules ou une par ligne) :',
    'feedback_schema.options_help'           => 'Fournissez des choix séparés par des virgules ou des sauts de ligne.',
    'feedback_schema.allow_multiple'         => 'Autoriser la sélection de plusieurs options (Sélection multiple)',
    'feedback_schema.boolean_format'         => 'Format d’affichage booléen :',
    'feedback_schema.max_length_label'       => 'Taille maximale / Longueur (Limite de caractères facultative) :',
    'feedback_schema.is_required_label'      => 'Rendre ce champ obligatoire pour les soumetteurs',
    'feedback_schema.save_field_btn'         => 'Enregistrer les modifications du champ',
    'feedback_schema.create_field_btn'       => 'Créer le champ de ticket',
    'feedback_schema.sub_email'              => 'E-mail',
    'feedback_schema.sub_url'                => 'URL',
    'feedback_schema.sub_select'             => 'Liste déroulante',
    'feedback_schema.sub_radio'              => 'Groupe de boutons radio',
    'feedback_schema.sub_checkbox'           => 'Cases à cocher',
    'feedback_schema.sub_textarea'           => 'Zone de paragraphe',
    'feedback_schema.sub_number'             => 'Saisie numérique',
    'feedback_schema.existing_fields_heading'=> 'Champs de tickets existants',
    'feedback_schema.th_move'                => 'Déplacer',
    'feedback_schema.th_field_name'          => 'Nom du champ',
    'feedback_schema.th_data_type'           => 'Type de données',
    'feedback_schema.th_subtype'             => 'Sous-type',
    'feedback_schema.th_required'            => 'Obligatoire ?',
    'feedback_schema.th_max_length'          => 'Longueur max.',
    'feedback_schema.th_created_by'          => 'Créé par',
    'feedback_schema.no_fields'              => 'Aucun champ de ticket personnalisé défini pour le moment.',
    'feedback_schema.system_user'            => 'Système',
    'feedback_schema.edit_btn'               => 'Modifier',
    'feedback_schema.delete_confirm'         => 'Supprimer ce champ et toutes les valeurs de réponse associées ?',

    // ------------------------------------------------------------------
    // Admin: Manage Tables & Column Schemas
    // ------------------------------------------------------------------
    'manage_tables.heading'              => 'Gestion dynamique des tables et des schémas',
    'manage_tables.subheading'           => 'Créez, inspectez, modifiez ou désaffectez en toute sécurité les tables dynamiques de l’application et leurs schémas de colonnes sous-jacents.',
    'manage_tables.switcher_label'       => 'Sélectionner le schéma de table actif :',
    'manage_tables.edit_metadata_btn'    => 'Modifier les métadonnées de la table',
    'manage_tables.delete_table_confirm'=> 'AVERTISSEMENT : La suppression de cette table supprimera définitivement toutes ses colonnes et ses contenus enregistrés. Êtes-vous absolument sûr ?',
    'manage_tables.delete_table_btn'     => 'Supprimer la table',
    'manage_tables.edit_table_summary'   => 'Modifier la définition de la table :',
    'manage_tables.create_table_summary'=> '+ Créer une nouvelle table dynamique',
    'manage_tables.table_name_label'     => 'Nom convivial de la table :',
    'manage_tables.table_desc_label'     => 'Description / Objectif :',
    'manage_tables.save_table_btn'       => 'Enregistrer les modifications de la table',
    'manage_tables.create_table_btn'     => 'Créer le schéma de table',
    'manage_tables.edit_col_summary'     => 'Modifier la colonne dynamique :',
    'manage_tables.add_col_summary_prefix' => '+ Ajouter une nouvelle colonne de table pour',
    'manage_tables.col_name_label'       => 'Nom de la colonne :',
    'manage_tables.type_text_long'       => 'TEXT (Paragraphe long)',
    'manage_tables.date_behavior_label' => 'Comportement de recherche par date :',
    'manage_tables.date_bhv_manual'      => 'Dates dans la base de données (saisie manuelle uniquement)',
    'manage_tables.date_bhv_admin'       => 'Dates administratives uniquement',
    'manage_tables.date_bhv_all'         => 'Toutes les dates, y compris administratives',
    'manage_tables.req_toggle_label'     => 'Rendre cette colonne obligatoire (saisie de données obligatoire)',
    'manage_tables.exclude_search_label'=> 'Exclure cette colonne de la recherche publique (index.php)',
    'manage_tables.create_col_btn'       => 'Créer la colonne',
    'manage_tables.existing_cols_heading_prefix' => 'Colonnes existantes pour',
    'manage_tables.th_public_search'     => 'Recherche publique ?',
    'manage_tables.th_display_format'    => 'Format d’affichage',
    'manage_tables.th_date_created'      => 'Date de création',
    'manage_tables.no_columns_found'     => 'Aucune colonne dynamique définie pour cette table pour le moment.',
    'manage_tables.status_hidden'        => 'Masqué',
    'manage_tables.delete_col_confirm'   => 'AVERTISSEMENT : La suppression de cette colonne supprimera également toutes les données de cellules associées dans tous les enregistrements. Êtes-vous sûr ?',

    // ------------------------------------------------------------------
    // Admin: Manage User Notification Email Templates
    // ------------------------------------------------------------------
    'user_emails.heading'                => 'Gérer les modèles d’e-mails de notification utilisateur',
    'user_emails.subheading'             => 'Personnalisez les mises en page des e-mails envoyés lors de l’invitation d’utilisateurs ou de l’envoi de liens de réinitialisation de mot de passe.',
    'user_emails.select_template_label'=> 'Sélectionner le modèle à modifier :',
    'user_emails.opt_invitation'         => 'Modèle d’invitation de compte utilisateur',
    'user_emails.opt_reset'              => 'Modèle de réinitialisation de mot de passe / lien d’accès',
    'user_emails.currently_editing'      => 'Modification en cours :',
    'user_emails.desc_invitation'        => 'Envoyé automatiquement lorsqu’un administrateur crée ou invite un nouveau compte utilisateur.',
    'user_emails.desc_reset'             => 'Envoyé lorsqu’un administrateur déclenche une réinitialisation de mot de passe ou renvoie un lien d’accès.',
    'user_emails.email_body_label'       => 'Contenu du corps de l’e-mail :',
    'user_emails.back_to_creation'       => 'Retour à la création d’utilisateur',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Email Templates
    // ------------------------------------------------------------------
    'volunteer_emails.heading'           => 'Modèles d’e-mails et déclencheurs de bénévoles',
    'volunteer_emails.subheading'        => 'Personnalisez les réponses automatiques par e-mail envoyées aux bénévoles lors de diverses phases de flux de travail. Utilisez des accolades pour les espaces réservés dynamiques.',
    'volunteer_emails.back_to_dashboard'=> 'Retour aux candidatures de bénévoles',
    'volunteer_emails.custom_tags_desc'  => 'Générées automatiquement à partir des champs de votre générateur de formulaires :',

    // ------------------------------------------------------------------
    // Admin: Manage Volunteer Form Schema & Fields
    // ------------------------------------------------------------------
    'volunteer_schema.heading'           => 'Gestion du schéma du formulaire de bénévoles',
    'volunteer_schema.subheading'        => 'Configurez les champs personnalisés, les types de données, les sous-types, les options et les paramètres généraux de présentation du formulaire.',
    'volunteer_schema.back_to_dashboard'=> 'Retour aux candidatures de bénévoles',
    'volunteer_schema.settings_summary'  => 'Configurer le titre du formulaire et le texte d’introduction',
    'volunteer_schema.edit_field_title'  => 'Modifier le champ :',
    'volunteer_schema.add_field_title'   => '+ Ajouter un nouveau champ de formulaire de bénévole',
    'volunteer_schema.create_field_btn'  => 'Créer le champ',
    'volunteer_schema.existing_fields_heading' => 'Champs existants du formulaire de bénévoles',
    'volunteer_schema.no_fields'         => 'Aucun champ de bénévole personnalisé défini pour le moment.',
    'volunteer_schema.delete_confirm'    => 'Supprimer ce champ et toutes les valeurs de réponse associées ?',

    // ------------------------------------------------------------------
    // Admin: Moderation Queue & Suggestions Review
    // ------------------------------------------------------------------
    'moderate.heading'                   => 'Examen des suggestions en attente',
    'moderate.subheading'                => 'Comparez les modifications proposées par les utilisateurs aux enregistrements en direct sur vos tables autorisées. Approuvez les propositions, remplacez les valeurs ou refusez les suggestions.',
    'moderate.shortcut_label'            => 'Astuce de raccourci clavier :',
    'moderate.shortcut_desc'             => 'Appuyez sur Ctrl + Enter pour approuver rapidement, ou Esc pour effacer la zone de remplacement !',
    'moderate.th_id_date'                => 'ID / Date',
    'moderate.th_table_record'           => 'Table, enregistrement et colonne',
    'moderate.th_comparison'             => 'Comparaison (En direct vs Proposé) et Preuve',
    'moderate.th_actions'                => 'Actions du modérateur',
    'moderate.no_suggestions'            => 'Aucune suggestion en attente trouvée pour vos tables de modération autorisées.',
    'moderate.by_label'                  => 'Par :',
    'moderate.guest_user'                => 'Visiteur / Invité',
    'moderate.record_id_label'           => 'ID de l’enregistrement :',
    'moderate.column_label'              => 'Colonne :',
    'moderate.required_badge'            => 'Obligatoire',
    'moderate.live_value_label'          => 'Valeur en direct actuelle :',
    'moderate.empty_placeholder'         => '[Vide]',
    'moderate.proposed_value_label'      => 'Changement proposé :',
    'moderate.evidence_label'            => 'Preuve / Justification :',
    'moderate.no_evidence'               => 'Aucune preuve / justification fournies.',
    'moderate.override_label'            => 'Remplacer la valeur :',
    'moderate.select_placeholder'        => '-- Sélectionner --',
    'moderate.historical_dates_title'    => 'Dates historiques prises en charge',
    'moderate.approve_confirm'           => 'Approuver et appliquer cette valeur ?',
    'moderate.decline_confirm'           => 'Refuser et rejeter cette suggestion ?',
    'moderate.approve_btn'               => 'Approuver',
    'moderate.decline_btn'               => 'Refuser',

    // ------------------------------------------------------------------
    // Admin: Notices & Announcements Manager
    // ------------------------------------------------------------------
    'notices.heading'                    => 'Gestionnaire d’avis et d’annonces du site',
    'notices.subheading'                 => 'Créez des alertes dynamiques, des bannières de bienvenue ou des notifications ciblées pour des rôles d’utilisateurs spécifiques.',
    'notices.error_blank'                => 'Le titre et le contenu ne peuvent pas être vides.',
    'notices.msg_created'                => 'Avis créé avec succès !',
    'notices.msg_deleted'                => 'Avis supprimé.',
    'notices.create_heading'             => 'Créer un nouvel avis',
    'notices.title_label'                => 'Titre / En-tête de l’avis :',
    'notices.content_label'              => 'Contenu de l’avis (HTML/Texte autorisé) :',
    'notices.target_roles_label'         => 'Public cible (Sélectionner les rôles ou tout le monde) :',
    'notices.role_everyone'              => 'Tout le monde',
    'notices.role_public'                => 'Public (Invités)',
    'notices.role_users'                 => 'Utilisateurs',
    'notices.role_moderators'            => 'Modérateurs',
    'notices.role_admins'                => 'Administrateurs',
    'notices.dismissible_label'          => "Fermable (Inclut un bouton de fermeture 'X')",
    'notices.display_order_label'        => 'Ordre d’affichage :',
    'notices.publish_btn'                => 'Publier l’avis',
    'notices.existing_heading'           => 'Avis actifs et existants',
    'notices.th_order'                   => 'Ordre',
    'notices.th_title'                   => 'Titre',
    'notices.th_target_roles'            => 'Rôles cibles',
    'notices.th_dismissible'             => 'Fermable',
    'notices.no_notices'                 => 'Aucun avis créé pour le moment.',
    'notices.yes'                        => 'Oui',
    'notices.no_sticky'                  => 'Non (Épinglé / Sticky)',
    'notices.delete_confirm'             => 'Supprimer cet avis ?',

    // ------------------------------------------------------------------
    // Admin: Global Site Settings, Modules & Permissions
    // ------------------------------------------------------------------
    'settings.heading'                   => 'Paramètres globaux du site, modules et permissions',
    'settings.subheading'                => 'Gérez les configurations principales, les pilotes de messagerie, les options de sécurité/CAPTCHA, les modules de fonctionnalités, le mode maintenance, les annonces du site et les capacités des rôles.',
    'settings.tab_core'                  => 'Noyau et messagerie',
    'settings.tab_modules'               => 'Modules',
    'settings.tab_maintenance'           => 'Maintenance',
    'settings.tab_notices'               => 'Avis du site',
    'settings.tab_permissions'           => 'Rôles et permissions',
    'settings.tab_audit'                 => 'Journal d’audit',
    'settings.db_updates_heading'        => 'Mises à jour de la base de données',
    'settings.schema_current'            => 'Version actuelle du schéma :',
    'settings.schema_latest'             => 'Dernière disponible :',
    'settings.download_backup_btn'       => 'Télécharger la sauvegarde de la BD',
    'settings.download_backup_desc'      => 'Enregistre un fichier .sql complet sur votre ordinateur. Conservez-le dans un endroit sûr avant d’exécuter des mises à jour.',
    'settings.schema_update_notice'      => 'Une mise à jour de la base de données est disponible. Veuillez télécharger une sauvegarde ci-dessus avant de continuer.',
    'settings.migration_confirm'         => 'Avez-vous téléchargé une sauvegarde de la base de données ? Cela appliquera les mises à jour de schéma en attente.',
    'settings.update_db_btn'             => 'Mettre à jour la base de données',
    'settings.schema_uptodate'           => 'La base de données est à jour.',
    'settings.core_sys_heading'          => 'Paramètres système principaux',
    'settings.sys_name_label'            => 'Nom du système / de l’application :',
    'settings.default_lang_label'        => 'Langue par défaut du site :',
    'settings.default_lang_desc'         => 'Utilisée pour les invités et les utilisateurs n’ayant pas choisi de langue. Ajoutez des fichiers sous lang/ (par ex. fr.php) pour offrir plus d’options.',
    'settings.captcha_heading'           => 'Configuration de la sécurité et du CAPTCHA',
    'settings.captcha_provider_label'    => 'Moteur fournisseur de CAPTCHA :',
    'settings.captcha_none'              => 'Désactivé (Pas de CAPTCHA)',
    'settings.captcha_turnstile'         => 'Cloudflare Turnstile',
    'settings.captcha_recaptcha'         => 'Google reCAPTCHA v2 / v3',
    'settings.captcha_hcaptcha'          => 'hCaptcha',
    'settings.turnstile_heading'         => 'Paramètres de Cloudflare Turnstile',
    'settings.recaptcha_heading'         => 'Paramètres de Google reCAPTCHA',
    'settings.hcaptcha_heading'          => 'Paramètres de hCaptcha',
    'settings.site_key_label'            => 'Clé du site (Publique) :',
    'settings.secret_key_label'          => 'Clé secrète (Privée) :',
    'settings.mail_heading'              => 'Configuration de la distribution des e-mails',
    'settings.mail_domain_label'         => 'Domaine e-mail du système (Enveloppe de repli) :',
    'settings.mail_from_label'           => "Adresse e-mail 'De' personnalisée :",
    'settings.mail_from_desc'            => 'Adresse explicite utilisée comme expéditeur pour les e-mails sortants.',
    'settings.mail_driver_label'         => 'Pilote / Moteur de messagerie :',
    'settings.driver_native'             => 'Messagerie native (Relais Postfix local)',
    'settings.driver_smtp'               => 'SMTP authentifié (PHPMailer)',
    'settings.smtp_heading'              => 'Configurations du serveur SMTP',
    'settings.smtp_host_label'           => 'Hôte SMTP :',
    'settings.smtp_port_label'           => 'Port :',
    'settings.smtp_encryption_label'     => 'Chiffrement :',
    'settings.enc_tls'                   => 'TLS (Port 587)',
    'settings.enc_ssl'                   => 'SSL (Port 465)',
    'settings.smtp_user_label'           => 'Nom d’utilisateur SMTP :',
    'settings.smtp_pass_label'           => 'Mot de passe SMTP (Laisser vide pour conserver l’actuel) :',
    'settings.save_core_mail_btn'        => 'Enregistrer les paramètres principaux et de messagerie',
    'settings.test_mail_heading'         => 'Tester la configuration de messagerie',
    'settings.test_email_label'          => 'Adresse e-mail du destinataire :',
    'settings.send_test_btn'             => 'Envoyer un e-mail de test',
    'settings.modules_heading'           => 'Boutons de modules de l’application et contrôles d’efficacité',
    'settings.modules_subheading'        => 'Activez ou désactivez des fonctionnalités pour optimiser l’efficacité d’exécution de l’application et adapter le PRD à vos besoins de déploiement spécifiques.',
    'settings.mod_users'                 => 'Gestion des utilisateurs et accès multi-utilisateurs',
    'settings.mod_users_desc'            => 'Active l’inscription, la gestion des utilisateurs et l’authentification multi-utilisateurs. (L’accès au profil reste disponible pour la sécurité d’un utilisateur unique).',
    'settings.mod_leaderboard'           => 'Classement et gamification',
    'settings.mod_leaderboard_desc'      => 'Reconnaît les efforts de transcription et les scores en étoiles.',
    'settings.mod_leaderboard_note'      => '(Nécessite la gestion des utilisateurs et l’accès multi-utilisateurs)',
    'settings.mod_moderation'            => 'Flux de travail de modération',
    'settings.mod_moderation_desc'       => 'Active l’examen des suggestions de modification et la file d’attente de modération.',
    'settings.mod_volunteers'            => 'Portail des bénévoles et candidatures',
    'settings.mod_volunteers_desc'       => 'Active le formulaire public d’intérêt des bénévoles et le tableau de bord de gestion administrateur.',
    'settings.mod_feedback'              => 'Soumissions de commentaires',
    'settings.mod_feedback_desc'         => 'Active le formulaire public de commentaires et le tableau de bord de suivi administrateur.',
    'settings.save_modules_btn'          => 'Enregistrer les configurations des modules',
    'settings.maintenance_heading'       => 'Mode maintenance du système',
    'settings.maintenance_toggle'        => 'Activer le mode maintenance (Mettre le site hors ligne)',
    'settings.maintenance_reason_label'  => 'Raison / Message pour les utilisateurs :',
    'settings.maintenance_eta_label'     => 'Heure de retour prévue (ETA) :',
    'settings.save_maintenance_btn'      => 'Enregistrer les paramètres de maintenance',
    'settings.notices_heading'           => 'Avis et annonces du site',
    'settings.add_notice_btn'            => '+ Ajouter un nouvel avis',
    'settings.no_notices'                => 'Aucun avis configuré pour le moment.',
    'settings.status_active'             => 'Actif',
    'settings.status_inactive'           => 'Inactif',
    'settings.notice_content_label'      => 'Contenu :',
    'settings.save_notice_btn'           => 'Enregistrer l’avis',
    'settings.permissions_heading'       => 'Matrice dynamique des rôles et permissions',
    'settings.permissions_subheading'    => 'Les permissions sont regroupées par fonctions système. Développez les sections pour configurer les capacités, puis enregistrez vos mises à jour en bas.',
    'settings.th_role'                   => 'Rôle',
    'settings.th_capabilities'           => 'Capacités assignées dans ce groupe',
    'settings.save_permissions_btn'      => 'Enregistrer la matrice des permissions',
    'settings.audit_heading'             => 'Explorateur du journal d’audit du système',
    'settings.audit_subheading'          => 'Examinez les actions enregistrées de sécurité, de saisie de données et de modération. Utilisez les options de maintenance ci-dessous pour effacer les journaux si nécessaire.',
    'settings.purge_all_confirm'         => '⚠️ AVERTISSEMENT : Cela supprimera définitivement TOUT le journal d’audit du système. Êtes-vous sûr de vouloir continuer ?',
    'settings.clear_all_audit_btn'       => 'Effacer tout le journal d’audit',
    'settings.purge_records_confirm'     => 'Êtes-vous sûr de vouloir effacer toutes les entrées d’audit liées aux enregistrements ?',
    'settings.clear_records_audit_btn'   => 'Effacer uniquement le journal des enregistrements',
    'settings.th_id'                     => 'ID',
    'settings.th_timestamp'              => 'Horodatage',
    'settings.th_actor'                  => 'Acteur',
    'settings.th_action'                 => 'Action',
    'settings.th_record_id'              => 'ID de l’enregistrement',
    'settings.th_details'                => 'Détails',
    'settings.th_ip'                     => 'Adresse IP',
    'settings.no_audit_logs'             => 'Aucune entrée de journal d’audit trouvée.',
    'settings.system_guest'              => 'Système / Invité',
    'settings.audit_limit_note'          => 'Affichage des 250 dernières entrées du journal d’audit.',

    // ------------------------------------------------------------------
    // Admin: User Account Management & Leaderboard Moderation
    // ------------------------------------------------------------------
    'admin_users.heading'                => 'Gestion des comptes utilisateurs et modération du classement',
    'admin_users.subheading'             => 'Inspectez les statuts des utilisateurs, attribuez des rôles, remplacez des e-mails, déclenchez des réinitialisations de mot de passe ou des invitations, réinitialisez le 2FA ou suspendez des comptes.',
    'admin_users.manage_templates_btn'   => 'Gérer les modèles d’e-mails',
    'admin_users.invite_user_btn'        => 'Inviter un nouvel utilisateur',
    'admin_users.th_username'            => 'Nom d’utilisateur',
    'admin_users.th_email_override'      => 'E-mail et remplacement',
    'admin_users.th_role_assignment'     => 'Attribution de rôle',
    'admin_users.th_score'               => 'Score',
    'admin_users.th_status'              => 'Statut',
    'admin_users.th_2fa'                 => '2FA',
    'admin_users.th_actions'             => 'Actions et modération',
    'admin_users.no_users'               => 'Aucun utilisateur trouvé.',
    'admin_users.save_email_title'       => 'Enregistrer la nouvelle adresse e-mail',
    'admin_users.verified_label'         => 'Vérifié :',
    'admin_users.yes'                    => 'Oui',
    'admin_users.no'                     => 'Non',
    'admin_users.protected_admin'        => 'Administrateur principal protégé',
    'admin_users.update_btn'             => 'Mettre à jour',
    'admin_users.status_active'          => 'Actif',
    'admin_users.status_suspended'       => 'Suspendu',
    'admin_users.enabled'                => 'Activé',
    'admin_users.disabled'               => 'Désactivé',
    'admin_users.set_score_btn'          => 'Définir le score',
    'admin_users.resend_invite_confirm' => 'Renvoyer l’e-mail d’invitation au compte à cet utilisateur ?',
    'admin_users.resend_invite_btn'      => 'Renvoyer l’invitation',
    'admin_users.reset_pwd_confirm'      => 'Envoyer un lien de réinitialisation de mot de passe à cet utilisateur ?',
    'admin_users.reset_password_btn'     => 'Réinitialiser le mot de passe',
    'admin_users.suspend_confirm'        => 'Suspendre l’utilisateur et bloquer l’accès pour tricherie/infraction ?',
    'admin_users.suspend_btn'            => 'Suspendre',
    'admin_users.reactivate_btn'         => 'Réactiver',
    'admin_users.reset_2fa_confirm'      => 'Réinitialiser et désactiver le 2FA pour cet utilisateur ?',
    'admin_users.reset_2fa_btn'          => 'Réinitialiser le 2FA',

    // ------------------------------------------------------------------
    // Admin: View Ticket & Threaded Dialogue
    // ------------------------------------------------------------------
    'view_ticket.back_to_dashboard'    => 'Retour au tableau de bord des tickets',
    'view_ticket.ticket_heading_prefix'=> 'Ticket',
    'view_ticket.support_request'      => 'Demande de support',
    'view_ticket.submitted_by'         => 'Soumis par :',
    'view_ticket.on_date'              => 'le',
    'view_ticket.submitted_fields'     => 'Champs du formulaire soumis :',
    'view_ticket.ticket_status_label'  => 'Statut du ticket :',
    'view_ticket.status_pending'       => 'En attente',
    'view_ticket.status_progress'      => 'En cours',
    'view_ticket.status_completed'     => 'Terminé',
    'view_ticket.status_rejected'      => 'Rejeté',
    'view_ticket.dialogue_heading'     => 'Fil de discussion',
    'view_ticket.no_replies'           => 'Aucune réponse enregistrée pour le moment.',
    'view_ticket.admin_label'          => 'Admin',
    'view_ticket.staff'                => 'Personnel',
    'view_ticket.post_reply_heading'   => 'Publier une réponse et notifier le soumetteur',
    'view_ticket.reply_placeholder'    => 'Tapez votre réponse ici...',
    'view_ticket.send_reply_btn'       => 'Envoyer la réponse et notifier le soumetteur par e-mail',

    // ------------------------------------------------------------------
    // Admin: Volunteer Submissions & Workflow Dashboard
    // ------------------------------------------------------------------
    'volunteer_dashboard.heading'            => 'Soumissions de bénévoles et flux de travail',
    'volunteer_dashboard.subheading'         => 'Examinez les candidatures, planifiez des entretiens avec les bénévoles, prenez des notes d’entretien et acceptez des candidats dans le système.',
    'volunteer_dashboard.manage_emails_btn' => 'Gérer les modèles d’e-mails',
    'volunteer_dashboard.manage_schema_btn' => 'Gérer le schéma du formulaire',
    'volunteer_dashboard.th_status'          => 'Statut',
    'volunteer_dashboard.th_name'            => 'Nom',
    'volunteer_dashboard.th_interview_notes'=> 'Entretien / Notes',
    'volunteer_dashboard.no_submissions'     => 'Aucune candidature de bénévole trouvée.',
    'volunteer_dashboard.volunteer_prefix'   => 'Bénévole',
    'volunteer_dashboard.chat_label'         => 'Discussion :',
    'volunteer_dashboard.notes_label'        => 'Notes :',
    'volunteer_dashboard.no_notes'           => 'Aucune note pour le moment',
    'volunteer_dashboard.chat_notes_btn'     => 'Discussion et notes',
    'volunteer_dashboard.accept_title'       => 'Intégrer au système d’invitation des utilisateurs',
    'volunteer_dashboard.accept_invite_btn'  => 'Accepter et inviter',
    'volunteer_dashboard.delete_confirm'     => 'Supprimer cette entrée de bénévole ?',
    'volunteer_dashboard.modal_heading'      => 'Gérer l’entretien et les notes du candidat',
    'volunteer_dashboard.modal_status_label'=> 'Statut de la candidature :',
    'volunteer_dashboard.status_pending'     => 'En attente d’examen',
    'volunteer_dashboard.status_chat'        => 'Discussion planifiée',
    'volunteer_dashboard.status_accepted'    => 'Accepté',
    'volunteer_dashboard.status_rejected'    => 'Rejeté',
    'volunteer_dashboard.modal_date_label'   => 'Date et heure de la discussion / de l’entretien planifié :',
    'volunteer_dashboard.modal_notes_label'  => 'Notes de l’entretien / de la réunion :',
    'volunteer_dashboard.modal_notes_placeholder' => 'Enregistrez les commentaires de la discussion ici...',
    'volunteer_dashboard.save_changes_btn'   => 'Enregistrer les modifications',

    // ------------------------------------------------------------------
    // API: AJAX Search & Filtering
    // ------------------------------------------------------------------
    'api_search.error_public_forbidden' => '403 Interdit : La vue publique n’est pas activée.',
    'api_search.error_unauthorized_table' => 'Accès non autorisé à la table.',
    'api_search.no_records'              => 'Aucun enregistrement trouvé dans cette table.',
    'api_search.history_btn'             => 'Historique',
    'api_search.suggest_edit_btn'        => 'Suggérer une modification',

    // ------------------------------------------------------------------
    // Errors & HTTP Templates
    // ------------------------------------------------------------------
    'error_template.return_home_btn' => 'Retour à l’accueil public',

    // ------------------------------------------------------------------
    // Public: Ticket Intake & Feedback Portal
    // ------------------------------------------------------------------
    'feedback.hp_label'              => 'Laisser vide',
    'feedback.first_name_label'      => 'Prénom :',
    'feedback.surname_label'         => 'Nom de famille :',
    'feedback.email_label'           => 'Adresse e-mail :',
    'feedback.subject_label'         => 'Sujet / Titre de la demande :',
    'feedback.required_title'        => 'Champ obligatoire',
    'feedback.select_placeholder'    => '-- Sélectionner --',
    'feedback.multi_select_hint'     => 'Maintenez Ctrl ou Cmd enfoncé pour en sélectionner plusieurs.',
    'feedback.submit_btn'            => 'Soumettre le ticket',

    // ------------------------------------------------------------------
    // Security Engine & Firewall
    // ------------------------------------------------------------------
    'security_engine.err_suspicious_agent' => 'Échec du contrôle de sécurité : Signature client suspecte.',
    'security_engine.err_access_denied'    => 'Échec du contrôle de sécurité : Accès refusé.',
    'security_engine.err_rate_limit'       => 'Trop de soumissions à partir de cette adresse IP. Veuillez réessayer plus tard.',
    'security_engine.err_excessive_links'  => 'Soumission rejetée en raison d’un excès de liens détectés.',
    'security_engine.err_complete_captcha' => 'Veuillez compléter le défi de vérification CAPTCHA.',
    'security_engine.err_captcha_failed'   => 'Échec de la vérification CAPTCHA. Veuillez réessayer.',

    // ------------------------------------------------------------------
    // Installer Wizard
    // ------------------------------------------------------------------
    'install.complete_title'             => 'Installation terminée',
    'install.complete_heading'           => 'Installation terminée',
    'install.complete_desc'              => 'Ce site est déjà configuré. Le programme d’installation est verrouillé afin qu’il ne puisse pas être exécuté à nouveau par erreur.',
    'install.login_link'                 => 'Connexion',
    'install.home_link'                  => 'Aller sur le site',
    'install.delete_folder_hint'         => 'Vous pouvez supprimer ou renommer le dossier <code>install</code> pour plus de sécurité.',
    'install.msg_db_ready'               => 'La base de données est prête. Créez votre compte administrateur pour terminer l’installation.',
    'install.err_config_load'            => 'Impossible d’utiliser la configuration existante :',
    'install.err_write_permission'       => 'PHP ne peut pas créer de fichiers dans ce dossier de projet.',
    'install.detail_prefix'              => 'Détail :',
    'install.err_db_required'            => 'Le nom de la base de données et le nom d’utilisateur de la base de données sont obligatoires.',
    'install.err_db_not_empty'           => 'Cette base de données n’est pas vide. Utilisez une nouvelle base de données vide (ou supprimez toutes les tables) et réessayez.',
    'install.msg_schema_imported'        => 'Base de données connectée et schéma importé. Créez votre compte administrateur.',
    'install.err_complete_db_first'      => 'Terminez d’abord l’étape de la base de données.',
    'install.err_admin_required'         => 'Tous les champs administrateur sont obligatoires.',
    'install.err_invalid_email'          => 'Adresse e-mail non valide.',
    'install.err_password_length'        => 'Le mot de passe doit comporter au moins 8 caractères.',
    'install.err_passwords_match'        => 'Les mots de passe ne correspondent pas.',
    'install.err_admin_save_failed'      => 'L’utilisateur administrateur n’a pas été enregistré. Vérifiez la structure de la table des utilisateurs.',
    'install.msg_installation_complete' => 'Installation terminée.',
    'install.page_title'                 => 'Installation — Répertoire des registres paroissiaux',
    'install.heading'                    => 'Installer',
    'install.subheading'                 => 'Première installation <strong>uniquement pour ce dossier d’application</strong>. Utilisez une base de données MySQL vide.',
    'install.done_heading'               => 'Terminé',
    'install.done_message'               => 'Installation terminée. Le programme d’installation est maintenant verrouillé.',
    'install.admin_heading'              => 'Compte administrateur du site',
    'install.admin_subheading'           => 'Ceci est la connexion pour <strong>ce site Web</strong> (pas la base de données).',
    'install.admin_username_label'       => 'Nom d’utilisateur administrateur',
    'install.admin_email_label'          => 'E-mail administrateur',
    'install.admin_password_label'       => 'Mot de passe administrateur (min. 8 caractères)',
    'install.admin_confirm_password_label' => 'Confirmer le mot de passe administrateur',
    'install.finish_btn'                 => 'Terminer l’installation',
    'install.db_heading'                 => 'Connexion à la base de données',
    'install.db_hint'                    => 'Utilisez les informations MySQL de votre <strong>panneau de configuration d’hébergement</strong>. Il ne s’agit pas de la connexion administrateur du site Web (qui vient ensuite).',
    'install.db_host_label'              => 'Hôte de la base de données',
    'install.db_name_label'              => 'Nom de la base de données',
    'install.db_user_label'              => 'Nom d’utilisateur de la base de données',
    'install.db_pass_label'              => 'Mot de passe de la base de données',
    'install.db_submit_btn'              => 'Créer les tables &amp; continuer',
    'install.req_heading'                => '1. Exigences',
    'install.req_php'                    => 'PHP 8.0+ (%s trouvé)',
    'install.req_pdo'                    => 'Extension PDO MySQL',
    'install.req_logs'                   => 'Dossier de journaux accessible en écriture (ou dossier du projet)',
    'install.req_probe'                  => 'Peut créer des fichiers dans ce dossier de projet',
    'install.continue_btn'               => 'Continuer',
    'install.req_fail_msg'               => 'Corrigez les vérifications ayant échoué, puis rechargez cette page.',

    // ------------------------------------------------------------------
    // Leaderboard
    // ------------------------------------------------------------------
    'leaderboard.aria_region'     => 'Vue du classement',
    'leaderboard.heading'         => 'Classement des contributions communautaires',
    'leaderboard.subheading'      => 'Reconnaissance des efforts des membres de notre communauté qui aident à compiler, transcrire et/ou gérer les enregistrements de la base de données.',
    'leaderboard.th_rank'         => 'Rang',
    'leaderboard.th_contributor'  => 'Contributeur',
    'leaderboard.th_role'         => 'Rôle',
    'leaderboard.th_score'        => 'Score',
    'leaderboard.no_users'        => 'Aucun utilisateur actif trouvé dans le classement pour le moment.',
    'leaderboard.medal_gold'      => 'Médaille d’or',
    'leaderboard.medal_silver'    => 'Médaille d’argent',
    'leaderboard.medal_bronze'    => 'Médaille de bronze',
    'leaderboard.medal_ribbon'    => 'Ruban de récompense rang 4',
    'leaderboard.medal_rosette'   => 'Rosette rang 5',
    'leaderboard.medal_trophy'    => 'Trophée rang 6',
    'leaderboard.medal_star'      => 'Étoile rang 7',
    'leaderboard.medal_military'  => 'Médaille militaire rang 8',
    'leaderboard.medal_glowing'   => 'Étoile brillante rang 9',
    'leaderboard.medal_crown'     => 'Couronne rang 10',
    'leaderboard.you_badge'       => '(Vous)',
    'leaderboard.default_role'    => 'Utilisateur',

    // ------------------------------------------------------------------
    // Site Footer
    // ------------------------------------------------------------------
    'footer.compiled_notice'  => 'Registres paroissiaux compilés à partir de sources historiques du domaine public.',
    'footer.software_notice'  => 'Plateforme logicielle open-source sous licence MIT.',
    'footer.rights_reserved'  => 'Tous droits réservés.',

    // ------------------------------------------------------------------
    // Site Header & Head
    // ------------------------------------------------------------------
    'header.default_title' => 'Base de données des registres paroissiaux',

    // ------------------------------------------------------------------
    // Notices Banner Module
    // ------------------------------------------------------------------
    'notices_banner.close_title' => 'Fermer l’avis',

    // ------------------------------------------------------------------
    // Record History & Audit Trail
    // ------------------------------------------------------------------
    'record_history.exit_no_record'        => 'Aucun enregistrement spécifié.',
    'record_history.exit_not_found'        => 'Enregistrement non trouvé.',
    'record_history.heading_prefix'        => 'Historique et piste d’audit : Enregistrement',
    'record_history.return_btn'            => 'Retour',
    'record_history.directory_table_label'=> 'Table du répertoire :',
    'record_history.subheading_lifecycle' => 'Affiche le cycle de vie chronologique des modifications, suggestions et justifications associées exactement à cet enregistrement.',
    'record_history.snapshot_heading'      => 'Instantané des valeurs actuelles en direct',
    'record_history.empty_value'           => '[Vide]',
    'record_history.timeline_heading'      => 'Chronologie du cycle de vie et de l’activité',
    'record_history.no_history'            => 'Aucun événement d’audit historique enregistré spécifiquement pour cet enregistrement pour le moment.',
    'record_history.purge_confirm'         => 'Purger cette entrée de journal d’audit spécifique ?',
    'record_history.purge_btn'             => 'Purger le journal',
    'record_history.actor_label'           => 'Acteur :',
    'record_history.system_guest'          => 'Système / Invité',
    'record_history.target_column'         => 'Colonne cible :',
    'record_history.proposed_value'        => 'Valeur proposée :',
    'record_history.reasoning_evidence'    => 'Justification / Preuve :',

    // ------------------------------------------------------------------
    // Standalone Update Database Gateway
    // ------------------------------------------------------------------
    'update_database.msg_success'      => 'Base de données mise à jour avec succès ! %d migration(s) appliquée(s).',
    'update_database.msg_uptodate'     => 'La base de données est déjà à jour.',
    'update_database.err_failed'       => 'Échec de la migration :',
    'update_database.page_title'       => 'Mise à jour du système requise — Répertoire des registres paroissiaux',
    'update_database.heading'          => '⚠️ Mise à jour du système requise',
    'update_database.subheading'       => 'La structure de la base de données de l’application est obsolète et nécessite une mise à jour du schéma avant de pouvoir reprendre un fonctionnement normal.',
    'update_database.current_version'  => 'Version actuelle du schéma :',
    'update_database.latest_version'   => 'Dernière version disponible :',
    'update_database.proceed_login'    => 'Procéder à la connexion',
    'update_database.confirm_prompt'   => 'Avez-vous sauvegardé votre base de données ? Cliquez sur OK pour appliquer les mises à jour de schéma en attente.',
    'update_database.update_btn'       => 'Mettre à jour la base de données maintenant',

    // ------------------------------------------------------------------
    // User Authentication Action
    // ------------------------------------------------------------------
    'authenticate.err_invalid_credentials' => 'Identifiants non valides ou accès au compte restreint.',

    // ------------------------------------------------------------------
    // Save Data Entry Action
    // ------------------------------------------------------------------
    'save_data_entry.err_required_field'    => 'Le champ obligatoire \'%s\' ne peut pas être laissé vide.',
    'save_data_entry.audit_created_prefix' => 'Enregistrement créé dans la table ID %d.',
    'save_data_entry.msg_success'          => 'Enregistrement ajouté avec succès !',

    // ------------------------------------------------------------------
    // Save Public Suggestion Action
    // ------------------------------------------------------------------
    'save_public_suggestion.err_spam_detected'  => 'Détection de spam déclenchée. Soumission rejetée.',
    'save_public_suggestion.err_field_required' => 'Ce champ est obligatoire et ne peut pas être soumis vide.',
    'save_public_suggestion.msg_success'        => 'Votre suggestion de modification a été soumise avec succès et envoyée à la file d’attente de modération pour examen. Merci !',
    'save_public_suggestion.err_failed_submit'  => 'Échec de la soumission de la suggestion de modification. Veuillez réessayer.',
    'save_public_suggestion.err_invalid_column' => 'Colonne spécifiée non valide.',
    'save_public_suggestion.err_invalid_params' => 'Paramètres de soumission d’enregistrement non valides.',

    // ------------------------------------------------------------------
    // Data Entry Workstation
    // ------------------------------------------------------------------
    'data_entry.date_placeholder_ymd' => 'YYYY-MM-DD (ou année partielle)',
    'data_entry.date_placeholder_dmy' => 'DD/MM/YYYY (ou année partielle)',
    'data_entry.date_placeholder_mdy' => 'MM/DD/YYYY (ou année partielle)',
    'data_entry.no_tables_heading'    => '⚠️ Aucune table de base de données trouvée',
    'data_entry.no_tables_desc'       => 'Le système ne comporte actuellement aucune table de base de données active configurée pour la saisie de données.',
    'data_entry.admin_tables_prompt'  => 'En tant qu’administrateur, veuillez vous rendre dans l’option de menu <strong>Gérer les tables</strong> pour créer une table, puis ajoutez au moins une colonne avant de saisir des enregistrements.',
    'data_entry.go_manage_tables'     => 'Aller à Gérer les tables',
    'data_entry.contact_admin_tables' => 'Veuillez contacter un administrateur pour configurer les tables et les colonnes de la base de données.',
    'data_entry.no_cols_heading'      => '⚠️ Aucune colonne configurée',
    'data_entry.no_cols_desc'         => 'Des tables existent dans le système, mais aucune colonne de données n’a été définie pour la table active.',
    'data_entry.admin_cols_prompt'    => 'En tant qu’administrateur, veuillez vous rendre dans l’option de menu <strong>Gérer les tables</strong> pour ajouter au moins une colonne à votre table.',
    'data_entry.contact_admin_cols'   => 'Veuillez contacter un administrateur pour configurer les colonnes de cette table.',
    'data_entry.active_table_label'   => 'Table de saisie de données active :',
    'data_entry.add_entry_summary'    => '➕ Ajouter une nouvelle saisie de données (Cliquez pour développer/réduire)',
    'data_entry.bool_yes_true'        => 'Oui / Vrai',
    'data_entry.bool_no_false'        => 'Non / Faux',
    'data_entry.bool_male'            => 'Homme',
    'data_entry.bool_female'          => 'Femme',
    'data_entry.bool_true'            => 'Vrai',
    'data_entry.bool_false'           => 'Faux',
    'data_entry.bool_tick'            => '✔ (Coche)',
    'data_entry.bool_cross'           => '✘ (Croix)',
    'data_entry.date_title_hint'      => 'Accepte les dates complètes ou partielles (ex. 1842 ou 1842-05)',
    'data_entry.enter_value_placeholder' => 'Entrer la valeur...',
    'data_entry.submit_data_btn'      => 'Soumettre les données',
    'data_entry.shortcuts_tip'        => '💡 Astuces : Appuyez sur <strong>Ctrl + Enter</strong> pour soumettre, ou <strong>Esc</strong> pour effacer le champ actuel.',
    'data_entry.dup_heading'          => '⚠️ Avertissement de doublon potentiel',
    'data_entry.dup_desc'             => 'Nous avons trouvé des entrées correspondantes déjà dans le système :',
    'data_entry.dup_item_format'      => 'ID d’enregistrement : %d — Valeur : %s',
    'data_entry.dup_prompt'           => 'Souhaitez-vous tout de même continuer et enregistrer ce doublon ?',
    'data_entry.dup_confirm_btn'      => 'Oui, confirmer et enregistrer le doublon',
    'data_entry.search_summary'       => '🔍 Rechercher et filtrer les enregistrements existants (Cliquez pour développer/réduire)',
    'data_entry.date_to_label'        => 'à',
    'data_entry.filter_all_option'    => '-- Tout --',
    'data_entry.filter_placeholder'   => 'Filtrer...',
    'data_entry.apply_filters_btn'    => 'Appliquer les filtres de recherche',
    'data_entry.reset_filter_btn'     => 'Réinitialiser le filtre',
    'data_entry.csv_entire_btn'       => 'Télécharger le CSV complet',
    'data_entry.json_entire_btn'      => 'Télécharger le JSON complet',
    'data_entry.copy_entire_btn'      => 'Copier toute la table',
    'data_entry.csv_filtered_btn'     => 'Télécharger le CSV filtré',
    'data_entry.json_filtered_btn'    => 'Télécharger le JSON filtré',
    'data_entry.copy_filtered_btn'    => 'Copier la table filtrée',
    'data_entry.clipboard_alert'      => 'Données de la table copiées dans le presse-papiers ! Vous pouvez les coller directement dans Excel ou Google Sheets.',
    'data_entry.existing_records_heading' => 'Table des enregistrements existants',
    'data_entry.th_added_by'          => 'Ajouté par',
    'data_entry.th_date_created'      => 'Date de création',
    'data_entry.no_records'           => 'Aucun enregistrement trouvé.',
    'data_entry.na_value'             => 'N/A',
    'data_entry.page_label'           => 'Page :',

    // ------------------------------------------------------------------
    // Forgot Password
    // ------------------------------------------------------------------
    'forgot_password.aria_region'     => 'Récupération de mot de passe',
    'forgot_password.heading'         => 'Réinitialisez votre mot de passe',
    'forgot_password.subheading'      => 'Entrez l’adresse e-mail de votre compte ci-dessous, et nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.',
    'forgot_password.email_label'     => 'Adresse e-mail :',
    'forgot_password.submit_btn'      => 'Envoyer le lien de réinitialisation',
    'forgot_password.back_login_link' => 'Retour à la connexion',

    // ------------------------------------------------------------------
    // User Login
    // ------------------------------------------------------------------
    'login.aria_region'          => 'Connexion utilisateur',
    'login.heading'              => 'Connexion utilisateur',
    'login.username_label'       => 'Nom d’utilisateur ou e-mail :',
    'login.password_label'       => 'Mot de passe :',
    'login.submit_btn'           => 'Connexion',
    'login.forgot_password_link' => 'Mot de passe oublié ?',

    // ------------------------------------------------------------------
    // User Onboarding Setup Wizard
    // ------------------------------------------------------------------
    'onboarding.page_title'        => 'Bienvenue - Assistant de configuration du compte',
    'onboarding.heading'           => 'Bienvenue dans l’équipe !',
    'onboarding.subheading'        => 'Avant de commencer, veuillez prendre un moment pour configurer vos préférences d’affichage régional et de confidentialité. Vous pourrez toujours les modifier plus tard dans votre profil.',
    'onboarding.timezone_label'    => 'Fuseau horaire / Région :',
    'onboarding.date_format_label' => 'Format d’affichage de la date :',
    'onboarding.time_format_label' => 'Format de l’horloge (Affichage de l’heure) :',
    'onboarding.time_24'          => '24 heures (ex. 16:07)',
    'onboarding.time_12'          => '12 heures AM/PM (ex. 04:07 PM)',
    'onboarding.time_none'        => 'Date uniquement (Masquer complètement l’heure)',
    'onboarding.attribution_label' => 'Préférence d’affichage dans le classement et attribution :',
    'onboarding.attribution_desc1' => 'Contrôle la façon dont votre nom apparaît sur le classement public et dans les journaux d’enregistrement.',
    'onboarding.attr_anon_title'   => 'Anonyme :',
    'onboarding.attr_anon_text'    => 'Affiche les initiales et un nombre aléatoire pour tout le monde.',
    'onboarding.attr_public_title' => 'Public :',
    'onboarding.attr_public_text'  => 'Affiche votre nom complet à tout le monde.',
    'onboarding.attr_vol_title'   => 'Bénévoles uniquement :',
    'onboarding.attr_vol_text'     => 'Affiche les initiales au public, mais votre nom complet aux bénévoles, modérateurs et administrateurs connectés.',
    'onboarding.attr_opt_anon'     => 'Anonyme (Initiales et nombre aléatoire)',
    'onboarding.attr_opt_public'   => 'Public (Afficher le nom complet)',
    'onboarding.attr_opt_vol'      => 'Bénévoles uniquement',
    'onboarding.submit_btn'        => 'Enregistrer les préférences et continuer',

    // ------------------------------------------------------------------
    // User Profile & Security Settings
    // ------------------------------------------------------------------
    'profile.aria_region'          => 'Gestion du profil utilisateur',
    'profile.heading'              => 'Profil utilisateur et sécurité',
    'profile.personal_details_heading' => 'Informations personnelles',
    'profile.language_label'       => 'Langue préférée :',
    'profile.lang_site_default'    => 'Par défaut du site',
    'profile.update_details_btn'   => 'Mettre à jour les informations personnelles',
    'profile.email_heading'        => 'Adresse e-mail',
    'profile.current_email_label'  => 'E-mail actuel :',
    'profile.email_verified'       => '(Vérifié)',
    'profile.email_unverified'     => '(Non vérifié - Vérifiez votre boîte de réception)',
    'profile.change_email_label'   => 'Modifier l’adresse e-mail :',
    'profile.aria_new_email'       => 'Nouvelle adresse e-mail',
    'profile.update_email_btn'     => 'Mettre à jour l’e-mail et vérifier',
    'profile.password_heading'     => 'Modifier le mot de passe',
    'profile.current_password_label' => 'Mot de passe actuel :',
    'profile.new_password_label'   => 'Nouveau mot de passe (min. 8 car.) :',
    'profile.confirm_password_label' => 'Confirmer le nouveau mot de passe :',
    'profile.show_passwords_label' => 'Afficher les mots de passe en texte clair',
    'profile.update_password_btn'  => 'Mettre à jour le mot de passe',
    'profile.tfa_heading'          => 'Authentification à deux facteurs (2FA)',
    'profile.tfa_status_label'     => 'Statut :',
    'profile.tfa_enabled'          => 'Activé',
    'profile.tfa_disabled'         => 'Désactivé',
    'profile.setup_tfa_btn'        => 'Configurer Google Authenticator',
    'profile.tfa_active_desc'      => 'Le 2FA protège activement la connexion à votre compte.',
    'profile.backup_codes_heading' => 'Vos nouveaux codes de secours',
    'profile.download_codes_btn'   => 'Télécharger les nouveaux codes au format .txt',
    'profile.generate_codes_confirm' => 'Êtes-vous sûr ? Cela annulera tous les codes de secours existants.',
    'profile.generate_codes_btn'   => 'Générer de nouveaux codes de secours',

    // ------------------------------------------------------------------
    // User Registration
    // ------------------------------------------------------------------
    'register.aria_region'    => 'Inscription utilisateur',
    'register.heading'        => 'Enregistrer un nouveau compte',
    'register.username_label' => 'Nom d’utilisateur :',
    'register.submit_btn'     => 'S’inscrire',

    // ------------------------------------------------------------------
    // Set Password via Secure Token
    // ------------------------------------------------------------------
    'set_password.exit_invalid_token'        => 'Jeton de configuration non valide ou manquant.',
    'set_password.exit_expired_token'        => 'Ce lien de configuration de mot de passe n’est pas valide ou a expiré.',
    'set_password.proceed_login_btn'         => 'Procéder à la connexion',
    'set_password.aria_region'               => 'Configuration du mot de passe',
    'set_password.heading_format'            => 'Veuillez définir votre mot de passe pour %s',
    'set_password.subheading_format'         => 'Bienvenue sur votre nouveau compte, %s ! Veuillez choisir votre mot de passe ci-dessous.',
    'set_password.new_password_label'        => 'Nouveau mot de passe (minimum 8 caractères) :',
    'set_password.confirm_password_label'    => 'Confirmer le mot de passe :',
    'set_password.show_password_label'       => 'Afficher le mot de passe',
    'set_password.save_password_btn'         => 'Enregistrer le mot de passe',

    // ------------------------------------------------------------------
    // Setup 2FA Wizard
    // ------------------------------------------------------------------
    'setup_2fa.aria_region'      => 'Assistant de configuration 2FA',
    'setup_2fa.heading'          => 'Configurer Google Authenticator',
    'setup_2fa.subheading'       => 'Scannez le code QR ci-dessous avec votre application d’authentification.',
    'setup_2fa.qr_alt'           => 'Code QR pour la configuration du 2FA',
    'setup_2fa.manual_prompt'    => 'Ou entrez cette clé secrète manuellement :',
    'setup_2fa.backup_heading'   => 'Codes de récupération de secours d’urgence',
    'setup_2fa.backup_desc'      => 'Conservez ces codes de secours dans un endroit sûr. Chaque code peut être utilisé <strong>une seule fois</strong> si vous perdez l’accès à votre application d’authentification :',
    'setup_2fa.download_btn'     => 'Télécharger les codes au format .txt',
    'setup_2fa.code_label'       => 'Entrez le code à 6 chiffres de l’application pour confirmer et activer :',
    'setup_2fa.aria_code_input'  => 'Code d’authentification à 6 chiffres',
    'setup_2fa.submit_btn'       => 'Vérifier et activer le 2FA',
    'setup_2fa.cancel_link'      => 'Annuler et retourner au profil',

    // ------------------------------------------------------------------
    // Suggest Edit View
    // ------------------------------------------------------------------
    'suggest_edit.aria_region'          => 'Suggérer une modification',
    'suggest_edit.heading_prefix'       => 'Suggérer une modification pour l’enregistrement',
    'suggest_edit.return_btn'           => 'Retourner à l’enregistrement',
    'suggest_edit.success_msg_suffix'   => 'N’hésitez pas à soumettre une autre modification ci-dessous, ou utilisez le lien de retour ci-dessus lorsque vous avez terminé.',
    'suggest_edit.current_values_heading' => 'Valeurs actuelles :',
    'suggest_edit.empty_label'          => '(vide)',
    'suggest_edit.submit_heading'       => 'Soumettre une nouvelle valeur proposée et des preuves',
    'suggest_edit.confirm_prompt'       => 'Êtes-vous sûr d’être prêt à soumettre cette suggestion de modification pour examen par l’administrateur ?',
    'suggest_edit.select_column_label'  => 'Sélectionner la colonne à modifier :',
    'suggest_edit.reasoning_label'      => 'Preuve / Justification / Notes sur la source :',
    'suggest_edit.reasoning_placeholder'=> 'Fournissez un contexte, des citations de sources ou des justifications pour ce changement...',
    'suggest_edit.submit_btn'           => 'Soumettre la suggestion pour examen',
    'suggest_edit.proposed_value_label' => 'Nouvelle valeur proposée :',

    // ------------------------------------------------------------------
    // Verify 2FA Login Challenge
    // ------------------------------------------------------------------
    'verify_2fa.aria_region'     => 'Vérification 2FA',
    'verify_2fa.heading'         => 'Authentification à deux facteurs',
    'verify_2fa.subheading'      => 'Entrez le code à 6 chiffres de votre application d’authentification ou utilisez un code de récupération de secours d’urgence.',
    'verify_2fa.code_label'      => 'Code de vérification / Code de secours :',
    'verify_2fa.aria_code_input' => 'Entrez le code d’authentification ou de secours',
    'verify_2fa.submit_btn'      => 'Vérifier et se connecter',

    // ------------------------------------------------------------------
    // Verify Email
    // ------------------------------------------------------------------
    'verify_email.err_no_token'         => 'Aucun jeton de vérification fourni.',
    'verify_email.err_invalid_token'    => 'Jeton de vérification non valide.',
    'verify_email.msg_already_verified' => 'Votre e-mail a déjà été vérifié. Vous pouvez vous connecter.',
    'verify_email.err_expired_token'    => 'Ce lien de vérification a expiré (délai de 24 heures dépassé). Veuillez vous inscrire à nouveau ou demander un nouveau lien.',
    'verify_email.msg_success'          => 'E-mail vérifié avec succès ! Votre compte est maintenant actif. Vous pouvez procéder à la connexion.',
    'verify_email.err_update_failed'    => 'Une erreur s’est produite lors de la vérification de votre e-mail. Veuillez réessayer.',
    'verify_email.aria_region'          => 'Statut de vérification de l’e-mail',
    'verify_email.heading'              => 'Statut de vérification de l’e-mail',
    'verify_email.login_btn'            => 'Cliquez ici pour vous connecter',

    // ------------------------------------------------------------------
    // Volunteer Form View
    // ------------------------------------------------------------------
    'volunteer.aria_region'          => 'Formulaire de bénévolat',
    'volunteer.honeypot_label'       => 'Laissez ce champ vide :',
    'volunteer.required_field_title'=> 'Champ obligatoire',
    'volunteer.multi_select_hint'    => 'Maintenez Ctrl ou Cmd enfoncé pour en sélectionner plusieurs.',
    'volunteer.submit_btn'           => 'Soumettre l’intérêt pour le bénévolat',
];
