<?php
// scripts/langSortUtility/extract_fixed_keys.php

$langDir = realpath(__DIR__ . '/../../lang');
$enFile = $langDir . '/en.php';
$tempOut = '/tmp/extracted_english_snippet.php';

if (!file_exists($enFile)) {
    die("Error: Could not find en.php at {$enFile}\n");
}

$englishTranslations = include $enFile;
if (!is_array($englishTranslations)) {
    die("Error: en.php did not return a valid array.\n");
}

// These are the exact keys from your snippet
$keysToExtract = [
    'index.filter_aria',
    'manage_tables.type_choice',
    'manage_tables.choice_options_label',
    'manage_tables.choice_options_help',
    'manage_tables.allow_multiple_label',
    'manage_tables.min_value_label',
    'manage_tables.max_value_label',
    'manage_tables.save_and_add_col',
    'gh.heading',
    'gh.intro_p1',
    'gh.intro_step1',
    'gh.intro_step2',
    'gh.intro_step3',
    'gh.consent_title',
    'gh.consent_text',
    'gh.consent_agree',
    'gh.creating_new',
    'gh.switch_new',
    'gh.select_type',
    'gh.type_bug',
    'gh.type_enhancement',
    'gh.type_documentation',
    'gh.type_translation',
    'gh.duplicate_warning',
    'gh.duplicate_confirm_text',
    'gh.title_summary',
    'gh.title_placeholder',
    'gh.detailed_description',
    'gh.bug_desc_placeholder',
    'gh.steps_to_reproduce',
    'gh.expected_behavior',
    'gh.expected_placeholder',
    'gh.severity_level',
    'gh.sev_low',
    'gh.sev_low_desc',
    'gh.sev_med',
    'gh.sev_med_desc',
    'gh.sev_high',
    'gh.sev_high_desc',
    'gh.feature_problem',
    'gh.feature_solution',
    'gh.solution_placeholder',
    'gh.doc_related',
    'gh.doc_placeholder',
    'gh.doc_paragraphs',
    'gh.quote_placeholder',
    'gh.doc_proposed',
    'gh.proposed_placeholder',
    'gh.doc_reasoning',
    'gh.reasoning_placeholder',
    'gh.trans_offending',
    'gh.paste_placeholder',
    'gh.trans_action',
    'gh.action_placeholder',
    'gh.additional_context',
    'gh.additional_placeholder',
    'gh.select_audit_logs',
    'gh.select_audit_logs_desc',
    'gh.no_recent_logs',
    'gh.attach_logs',
    'gh.optional_diagnostic',
    'gh.submit_btn',
    'gh.similar_issues_title',
    'gh.similar_issues_desc',
    'gh.type_to_search',
    'gh.type_at_least_3',
    'gh.no_similar_found',
    'gh.click_to_comment',
    'gh.failed_check_issues',
    'gh.commenting_on',
    'gh.post_comment_btn',
    'gh.locked_onto_issue',
    'gh.add_comment_below',
    'gh.posting_comment',
    'gh.submitting',
    'gh.issue_created_label',
    'gh.created_success',
    'gh.save_tracking_link',
    'gh.tracking_link_aria',
    'gh.make_note_link',
    'gh.error_occurred',
    'gh.network_error',
    'gh.preview_heading',
    'gh.preview_reminder',
    'gh.preview_title_label',
    'gh.preview_body_label',
    'gh.preview_back',
    'gh.preview_send',
    'gh.sensitive_warn',
    'gh.sensitive_confirm',
    'gh.public_confirm',
    'gh.sending',
    'gh.comment_label',
    'gh.comment_help',
    'gh.open_on_github',
    'install.heading',
    'install.done_heading',
    'install.done_message',
    'install.subheading',
    'install.req_heading',
    'install.req_fail_msg',
    'install.demo_heading',
    'install.demo_help',
    'install.demo_choose',
    'install.demo_parish',
    'install.demo_parish_desc',
    'install.demo_library',
    'install.demo_library_desc',
    'install.demo_what',
    'install.demo_schema_only',
    'install.demo_schema_data',
    'install.demo_skip',
    'install.demo_continue',
    'install.msg_admin_created',
    'install.close_alert',
    'install.req_all_ok',
    'install.req_php_ok',
    'install.req_php_fail',
    'install.req_pdo_ok',
    'install.req_pdo_fail',
    'install.req_logs_ok',
    'install.req_logs_fail',
    'install.req_probe_ok',
    'install.req_probe_fail',
    'install.remove_folder_btn',
    'install.delete_folder_hint',
    'install.msg_install_removed',
    'install.err_install_not_removed',
    'demo.heading',
    'demo.intro',
    'demo.install_heading',
    'demo.install_help',
    'demo.choose_packs',
    'demo.already_installed',
    'demo.already_installed_hint',
    'demo.what_to_add',
    'demo.schema_only',
    'demo.schema_and_data',
    'demo.install_btn',
    'demo.remove_heading',
    'demo.none_installed',
    'demo.remove_data_help',
    'demo.remove_data_btn',
    'demo.remove_pack_help',
    'demo.remove_pack_btn',
    'demo.remove_pack_confirm',
    'save_data_entry.err_invalid_choice',
    'save_data_entry.err_not_number',
    'save_data_entry.err_min',
    'save_data_entry.err_max',
    'data_entry.multiselect_hint',
    'suggest_edit.reasoning_optional'
];

$maxKeyLen = 0;
foreach ($keysToExtract as $key) {
    $maxKeyLen = max($maxKeyLen, strlen("'" . $key . "'"));
}

$outputLines = ["<?php\n\nreturn [\n"];
$matchedCount = 0;

foreach ($keysToExtract as $key) {
    $formattedKey = "'" . $key . "'";
    $paddedKey = str_pad($formattedKey, $maxKeyLen, ' ', STR_PAD_RIGHT);

    if (isset($englishTranslations[$key])) {
        $val = addcslashes($englishTranslations[$key], "'\\");
        $outputLines[] = "    {$paddedKey} => '{$val}',\n";
        $matchedCount++;
    } else {
        $outputLines[] = "    // MISSING IN en.php: {$key}\n";
    }
}

$outputLines[] = "];\n";

file_put_contents($tempOut, implode('', $outputLines));

echo "Successfully extracted {$matchedCount} English translations!\n";
echo "Saved to temporary file: {$tempOut}\n";
