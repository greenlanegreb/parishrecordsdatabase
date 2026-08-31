<?php
declare(strict_types=1);
/**
 * Direct record edit form (permission: edit_records).
 * Variables expected from UserEditRecordController:
 * $record, $columns, $values, $currentUser, $systemName, $returnUrl,
 * $message, $error, $fieldErrors
 */
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$recordId = (int) ($record['id'] ?? 0);
$tableName = (string) ($record['table_name'] ?? 'Record');
?>
<?php require_once dirname(__DIR__, 3) . '/partials/header.php'; ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h1 class="h4 fw-bold mb-0">
                        <?= htmlspecialchars(__('edit_record.heading') !== 'edit_record.heading' ? __('edit_record.heading') : 'Edit record', ENT_QUOTES, 'UTF-8') ?>
                        #<?= $recordId ?>
                        <span class="text-muted fw-normal small">(<?= htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') ?>)</span>
                    </h1>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= htmlspecialchars($basePath . '/records/' . $recordId . '/edit', ENT_QUOTES, 'UTF-8') ?>" novalidate>
                        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                        <input type="hidden" name="return_url" value="<?= htmlspecialchars((string) $returnUrl, ENT_QUOTES, 'UTF-8') ?>">

                        <?php foreach ($columns as $col): ?>
                            <?php
                                $cid   = (int) ($col['id'] ?? 0);
                                $cname = (string) ($col['column_name'] ?? '');
                                $type  = strtoupper((string) ($col['data_type'] ?? 'TEXT'));
                                $req   = !empty($col['is_required']);
                                $cur   = $values[$cid] ?? '';
                                $opts  = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($col['field_options'] ?? ''))));
                                $multi = !empty($col['allow_multiple']);
                                $min   = $col['min_value'] ?? null;
                                $max   = $col['max_value'] ?? null;
                                $err   = is_array($fieldErrors ?? null)
                                    ? ($fieldErrors[$cid] ?? ($fieldErrors['fields'][$cid] ?? ''))
                                    : '';
                            ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold" for="field_<?= $cid ?>">
                                    <?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($req): ?><span class="text-danger">*</span><?php endif; ?>
                                </label>

                                <?php if ($type === 'SELECT'): ?>
                                    <select name="fields[<?= $cid ?>]<?= $multi ? '[]' : '' ?>"
                                            id="field_<?= $cid ?>"
                                            class="form-select<?= $err ? ' is-invalid' : '' ?>"
                                            <?= $multi ? 'multiple aria-multiselectable="true"' : '' ?>
                                            <?= $req ? 'required' : '' ?>>
                                        <?php if (!$multi): ?>
                                            <option value=""><?= htmlspecialchars(__('feedback.select_placeholder') !== 'feedback.select_placeholder' ? __('feedback.select_placeholder') : '— select —', ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endif; ?>
                                        <?php
                                            $selected = $multi
                                                ? array_map('trim', explode(',', (string) $cur))
                                                : [(string) $cur];
                                        ?>
                                        <?php foreach ($opts as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= in_array($opt, $selected, true) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($multi): ?>
                                        <div class="form-text"><?= htmlspecialchars(__('data_entry.multiselect_hint') !== 'data_entry.multiselect_hint' ? __('data_entry.multiselect_hint') : 'Hold Ctrl (or Cmd) to choose more than one.', ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>

                                <?php elseif ($type === 'INT'): ?>
                                    <input type="number" name="fields[<?= $cid ?>]" id="field_<?= $cid ?>"
                                           class="form-control<?= $err ? ' is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars((string) $cur, ENT_QUOTES, 'UTF-8') ?>"
                                           <?= $min !== null && $min !== '' ? 'min="' . htmlspecialchars((string) $min, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                                           <?= $max !== null && $max !== '' ? 'max="' . htmlspecialchars((string) $max, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                                           <?= $req ? 'required' : '' ?>>

                                <?php elseif ($type === 'BOOLEAN'):
                                    $fmt = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                                        ? $col['boolean_display_format'] : 'yes_no';
                                    $opt1 = __('data_entry.bool_yes_true');
                                    $opt2 = __('data_entry.bool_no_false');
                                    if ($fmt === 'male_female') {
                                        $opt1 = __('data_entry.bool_male');
                                        $opt2 = __('data_entry.bool_female');
                                    } elseif ($fmt === 'true_false') {
                                        $opt1 = __('data_entry.bool_true');
                                        $opt2 = __('data_entry.bool_false');
                                    } elseif ($fmt === 'tick_cross') {
                                        $opt1 = __('data_entry.bool_tick');
                                        $opt2 = __('data_entry.bool_cross');
                                    }
                                    if ($opt1 === 'data_entry.bool_yes_true') { $opt1 = 'Yes'; }
                                    if ($opt2 === 'data_entry.bool_no_false') { $opt2 = 'No'; }
                                    if ($opt1 === 'data_entry.bool_male') { $opt1 = 'Male'; }
                                    if ($opt2 === 'data_entry.bool_female') { $opt2 = 'Female'; }
                                    if ($opt1 === 'data_entry.bool_true') { $opt1 = 'True'; }
                                    if ($opt2 === 'data_entry.bool_false') { $opt2 = 'False'; }
                                    if ($opt1 === 'data_entry.bool_tick') { $opt1 = 'Yes'; }
                                    if ($opt2 === 'data_entry.bool_cross') { $opt2 = 'No'; }
                                ?>
                                    <select name="fields[<?= $cid ?>]" id="field_<?= $cid ?>"
                                            class="form-select<?= $err ? ' is-invalid' : '' ?>"
                                            <?= $req ? 'required' : '' ?>>
                                        <option value=""><?= htmlspecialchars(__('feedback.select_placeholder') !== 'feedback.select_placeholder' ? __('feedback.select_placeholder') : '— select —', ENT_QUOTES, 'UTF-8') ?></option>
                                        <option value="1" <?= ((string) $cur === '1') ? 'selected' : '' ?>><?= htmlspecialchars($opt1, ENT_QUOTES, 'UTF-8') ?></option>
                                        <option value="0" <?= ((string) $cur === '0') ? 'selected' : '' ?>><?= htmlspecialchars($opt2, ENT_QUOTES, 'UTF-8') ?></option>
                                    </select>

                                <?php elseif ($type === 'LOCATION'): ?>
                                    <?php
                                        $colId = $cid;
                                        $loc = \App\Services\LocationValueService::decode(is_string($cur) ? $cur : null);
                                        $namePrefix = 'fields[' . $cid . ']';
                                        $showOnMapToggle = true;
                                        require dirname(__DIR__, 3) . '/partials/location_field_inputs.php';
                                    ?>
                                <?php elseif ($type === 'DATE'):
                                    $userDateFmt = (isset($currentUser['date_format']) && is_string($currentUser['date_format']) && $currentUser['date_format'] !== '')
                                        ? $currentUser['date_format']
                                        : ((function_exists('get_site_datetime_defaults') && isset($pdo) && $pdo instanceof PDO)
                                            ? (get_site_datetime_defaults($pdo)[1] ?? 'd/m/Y')
                                            : 'd/m/Y');
                                    $shown = function_exists('format_display_date')
                                        ? format_display_date((string) $cur, $userDateFmt)
                                        : (string) $cur;
                                    $shown = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?(\s*[AaPp][Mm])?$/', '', $shown) ?? $shown;
                                    $ph = function_exists('get_date_placeholder') ? get_date_placeholder($userDateFmt) : $userDateFmt;
                                ?>
                                    <input type="text" name="fields[<?= $cid ?>]" id="field_<?= $cid ?>"
                                           class="form-control date-input<?= $err ? ' is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars($shown, ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="<?= htmlspecialchars($ph, ENT_QUOTES, 'UTF-8') ?>"
                                           autocomplete="off"
                                           <?= $req ? 'required' : '' ?>>
                                                                <?php elseif ($type === 'TIME'):
                                    $tpref = function_exists('resolve_viewer_time_format')
                                        ? resolve_viewer_time_format(isset($currentUser) && is_array($currentUser) ? $currentUser : [], $pdo ?? null)
                                        : '24';
                                    $tshown = function_exists('format_display_time') ? format_display_time((string) $cur, $tpref) : (string) $cur;
                                    $tph = function_exists('get_time_placeholder') ? get_time_placeholder($tpref) : ($tpref === '12' ? '2:30 PM' : '14:30');
                                ?>
                                    <input type="text" name="fields[<?= $cid ?>]" id="field_<?= $cid ?>"
                                           class="form-control time-input<?= $err ? ' is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars($tshown, ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="<?= htmlspecialchars($tph, ENT_QUOTES, 'UTF-8') ?>"
                                           inputmode="numeric" autocomplete="off"
                                           <?= $req ? 'required' : '' ?>>
                                <?php else: /* TEXT etc. */ ?>
                                    <input type="text" name="fields[<?= $cid ?>]" id="field_<?= $cid ?>"
                                           class="form-control<?= $err ? ' is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars((string) $cur, ENT_QUOTES, 'UTF-8') ?>"
                                           <?= $req ? 'required' : '' ?>>
                                <?php endif; ?>

                                <?php if ($err): ?>
                                    <div class="invalid-feedback d-block"><?= htmlspecialchars((string) $err, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <?= htmlspecialchars(__('btn.save') !== 'btn.save' ? __('btn.save') : 'Save changes', ENT_QUOTES, 'UTF-8') ?>
                            </button>
                            <a href="<?= htmlspecialchars((string) $returnUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">
                                <?= htmlspecialchars(__('btn.cancel') !== 'btn.cancel' ? __('btn.cancel') : 'Cancel', ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 3) . '/partials/location_field_script.php'; ?>
<?php require_once dirname(__DIR__, 3) . '/partials/date_input_script.php'; ?>
<?php require_once dirname(__DIR__, 3) . '/partials/footer.php'; ?>
