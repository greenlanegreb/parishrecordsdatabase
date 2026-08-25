<?php
declare(strict_types=1);
/**
 * Direct record edit — same field types as data entry.
 * @var array<string, mixed> $record
 * @var array<int, array<string, mixed>> $columns
 * @var array<int, mixed> $values
 * @var array{id?: int|string, date_format?: string} $currentUser
 * @var string $returnUrl
 * @var string $message
 * @var string $error
 * @var array<int|string, string> $fieldErrors
 */
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$recordId = (int) ($record['id'] ?? 0);
$tableName = (string) ($record['table_name'] ?? 'Record');
$fieldErrors = is_array($fieldErrors ?? null) ? $fieldErrors : [];
$userDateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format'])
    ? $currentUser['date_format'] : null;
$datePlaceholder = function_exists('get_date_placeholder')
    ? get_date_placeholder($userDateFormat) : 'YYYY-MM-DD';
if (!function_exists('parse_column_options') && is_file(ROOT_PATH . '/includes/column_options.php')) {
    require_once ROOT_PATH . '/includes/column_options.php';
}
if (!function_exists('field_has_error') && is_file(ROOT_PATH . '/includes/form_fields.php')) {
    require_once ROOT_PATH . '/includes/form_fields.php';
}
?>
<div class="container py-4" style="max-width: 720px;" role="region" aria-label="<?= htmlspecialchars(__('edit_record.heading') !== 'edit_record.heading' ? __('edit_record.heading') : 'Edit record', ENT_QUOTES, 'UTF-8') ?>">
    <h1 class="h4 fw-bold text-dark mb-3">
        <?= htmlspecialchars(__('edit_record.heading') !== 'edit_record.heading' ? __('edit_record.heading') : 'Edit record', ENT_QUOTES, 'UTF-8') ?>
        #<?= $recordId ?>
        <span class="text-muted fw-normal small">(<?= htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') ?>)</span>
    </h1>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($basePath . '/records/' . $recordId . '/edit', ENT_QUOTES, 'UTF-8') ?>" id="edit-record-form" class="card shadow-sm border-0">
        <div class="card-body">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            <input type="hidden" name="return_url" value="<?= htmlspecialchars((string) $returnUrl, ENT_QUOTES, 'UTF-8') ?>">

            <?php foreach ($columns as $col): ?>
                <?php
                    $cid = isset($col['id']) ? (int) $col['id'] : 0;
                    if ($cid < 1) {
                        continue;
                    }
                    $cname = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    $isRequired = !empty($col['is_required']);
                    $cur = $values[$cid] ?? '';
                    $fid = 'edit_field_' . $cid;
                    $errId = 'edit_err_' . $cid;
                    $hasErr = function_exists('field_has_error') && field_has_error($fieldErrors, $cid);
                    $invClass = $hasErr ? ' is-invalid' : '';
                    $inv = function_exists('field_invalid_attr') ? field_invalid_attr($fieldErrors, $cid, $errId) : '';
                ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="<?= $dataType === 'LOCATION' ? 'loc_q_' . $cid : $fid ?>">
                        <?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($isRequired): ?><span class="text-danger" aria-hidden="true">*</span><?php endif; ?>
                    </label>

                    <?php if ($dataType === 'BOOLEAN'): ?>
                        <?php
                            $displayFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                                ? $col['boolean_display_format'] : 'yes_no';
                            $opt1Text = __('data_entry.bool_yes_true');
                            $opt2Text = __('data_entry.bool_no_false');
                            if ($displayFormat === 'male_female') {
                                $opt1Text = __('data_entry.bool_male');
                                $opt2Text = __('data_entry.bool_female');
                            } elseif ($displayFormat === 'true_false') {
                                $opt1Text = __('data_entry.bool_true');
                                $opt2Text = __('data_entry.bool_false');
                            } elseif ($displayFormat === 'tick_cross') {
                                $opt1Text = __('data_entry.bool_tick');
                                $opt2Text = __('data_entry.bool_cross');
                            }
                            $curS = is_scalar($cur) ? (string) $cur : '';
                        ?>
                        <select id="<?= $fid ?>" name="fields[<?= $cid ?>]" class="form-select form-select-sm<?= $invClass ?>"
                                <?= $isRequired ? 'required' : '' ?><?= $inv ?>>
                            <option value=""><?= htmlspecialchars(__('feedback.select_placeholder') !== 'feedback.select_placeholder' ? __('feedback.select_placeholder') : '— select —', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="1" <?= ($curS === '1') ? 'selected' : '' ?>><?= htmlspecialchars($opt1Text, ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="0" <?= ($curS === '0') ? 'selected' : '' ?>><?= htmlspecialchars($opt2Text, ENT_QUOTES, 'UTF-8') ?></option>
                        </select>

                    <?php elseif ($dataType === 'DATE'): ?>
                        <?php $curS = is_scalar($cur) ? (string) $cur : ''; ?>
                        <input type="text" id="<?= $fid ?>" name="fields[<?= $cid ?>]"
                               value="<?= htmlspecialchars($curS, ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                               class="form-control form-control-sm date-input<?= $invClass ?>"
                               title="<?= htmlspecialchars(__('data_entry.date_title_hint') !== 'data_entry.date_title_hint' ? __('data_entry.date_title_hint') : 'Use a 4-digit year', ENT_QUOTES, 'UTF-8') ?>"
                               <?= $isRequired ? 'required' : '' ?><?= $inv ?>>

                    <?php elseif ($dataType === 'SELECT'): ?>
                        <?php
                            $opts = function_exists('parse_column_options')
                                ? parse_column_options($col['field_options'] ?? '')
                                : [];
                            $multi = !empty($col['allow_multiple']);
                            $curS = is_scalar($cur) ? (string) $cur : '';
                            $selected = $multi
                                ? (function_exists('explode_stored_column_values')
                                    ? explode_stored_column_values($curS)
                                    : array_filter(array_map('trim', explode(',', $curS))))
                                : [$curS];
                        ?>
                        <select id="<?= $fid ?>"
                                name="fields[<?= $cid ?>]<?= $multi ? '[]' : '' ?>"
                                class="form-select form-select-sm<?= $invClass ?>"
                                <?= $multi ? 'multiple aria-multiselectable="true"' : '' ?>
                                <?= $isRequired ? 'required' : '' ?>
                                <?= $inv ?>>
                            <?php if (!$multi): ?>
                                <option value=""><?= htmlspecialchars(__('feedback.select_placeholder') !== 'feedback.select_placeholder' ? __('feedback.select_placeholder') : '— select —', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endif; ?>
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

                    <?php elseif ($dataType === 'INT'): ?>
                        <?php $curS = is_scalar($cur) ? (string) $cur : ''; ?>
                        <input type="number" id="<?= $fid ?>" name="fields[<?= $cid ?>]"
                               value="<?= htmlspecialchars($curS, ENT_QUOTES, 'UTF-8') ?>"
                               class="form-control form-control-sm<?= $invClass ?>"
                               <?= isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== ''
                                   ? 'min="' . htmlspecialchars((string) $col['min_value'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                               <?= isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== ''
                                   ? 'max="' . htmlspecialchars((string) $col['max_value'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                               <?= $isRequired ? 'required' : '' ?><?= $inv ?>>

                    <?php elseif ($dataType === 'LOCATION'): ?>
                        <?php
                            $locRaw = is_string($cur) ? $cur : '';
                            $loc = \App\Services\LocationValueService::decode($locRaw !== '' ? $locRaw : null);
                            if (is_array($cur)) {
                                $loc = \App\Services\LocationValueService::fromPosted($cur) ?? $loc;
                            }
                            $loc = is_array($loc) ? $loc : ['q' => '', 'label' => '', 'lat' => '', 'lng' => '', 'title' => '', 'body' => '', 'color' => \App\Services\LocationValueService::defaultColor()];
                        ?>
                        <p class="form-text small"><?= htmlspecialchars(__('data_entry.location_help') !== 'data_entry.location_help' ? __('data_entry.location_help') : 'Search for the place as it is known today, pick a match, then you may word the label as the old name. Title and short text are required for the map popup.', ENT_QUOTES, 'UTF-8') ?></p>
                        <label class="form-label small" for="loc_q_<?= $cid ?>"><?= htmlspecialchars(__('data_entry.location_search') !== 'data_entry.location_search' ? __('data_entry.location_search') : 'Find place', ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" id="loc_q_<?= $cid ?>" class="form-control js-loc-q" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary js-loc-search" data-col="<?= $cid ?>"><?= htmlspecialchars(__('data_entry.location_search_btn') !== 'data_entry.location_search_btn' ? __('data_entry.location_search_btn') : 'Search', ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                        <div id="loc_results_<?= $cid ?>" class="list-group mb-2 small" role="listbox" aria-label="<?= htmlspecialchars(__('data_entry.location_results') !== 'data_entry.location_results' ? __('data_entry.location_results') : 'Did you mean', ENT_QUOTES, 'UTF-8') ?>"></div>
                        <input type="hidden" name="fields[<?= $cid ?>][q]" id="loc_hid_q_<?= $cid ?>" value="<?= htmlspecialchars((string) ($loc['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="fields[<?= $cid ?>][lat]" id="loc_lat_<?= $cid ?>" value="<?= htmlspecialchars((string) ($loc['lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="fields[<?= $cid ?>][lng]" id="loc_lng_<?= $cid ?>" value="<?= htmlspecialchars((string) ($loc['lng'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <label class="form-label small" for="loc_label_<?= $cid ?>"><?= htmlspecialchars(__('data_entry.location_label') !== 'data_entry.location_label' ? __('data_entry.location_label') : 'Name to show (you may use the historic name)', ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="text" class="form-control form-control-sm mb-2" id="loc_label_<?= $cid ?>" name="fields[<?= $cid ?>][label]" value="<?= htmlspecialchars((string) ($loc['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <label class="form-label small" for="loc_title_<?= $cid ?>"><?= htmlspecialchars(__('data_entry.location_title') !== 'data_entry.location_title' ? __('data_entry.location_title') : 'Popup title', ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="text" class="form-control form-control-sm mb-2" id="loc_title_<?= $cid ?>" name="fields[<?= $cid ?>][title]" value="<?= htmlspecialchars((string) ($loc['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <label class="form-label small" for="loc_body_<?= $cid ?>"><?= htmlspecialchars(__('data_entry.location_body') !== 'data_entry.location_body' ? __('data_entry.location_body') : 'Popup text', ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea class="form-control form-control-sm mb-2" id="loc_body_<?= $cid ?>" name="fields[<?= $cid ?>][body]" rows="2"><?= htmlspecialchars((string) ($loc['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <label class="form-label small" for="loc_color_<?= $cid ?>"><?= htmlspecialchars(__('data_entry.location_color') !== 'data_entry.location_color' ? __('data_entry.location_color') : 'Pin colour', ENT_QUOTES, 'UTF-8') ?></label>
                        <select class="form-select form-select-sm" id="loc_color_<?= $cid ?>" name="fields[<?= $cid ?>][color]">
                            <?php foreach (\App\Services\LocationValueService::palette() as $swatch): ?>
                                <option value="<?= htmlspecialchars($swatch['hex'], ENT_QUOTES, 'UTF-8') ?>"
                                    <?= ((string) ($loc['color'] ?? '') === $swatch['hex']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($swatch['label'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php else: ?>
                        <?php $curS = is_scalar($cur) ? (string) $cur : ''; ?>
                        <input type="text" id="<?= $fid ?>" name="fields[<?= $cid ?>]"
                               value="<?= htmlspecialchars($curS, ENT_QUOTES, 'UTF-8') ?>"
                               class="form-control form-control-sm<?= $invClass ?>"
                               <?= $isRequired ? 'required' : '' ?><?= $inv ?>>
                    <?php endif; ?>

                    <?php if (function_exists('field_error_html')): ?>
                        <?= field_error_html($fieldErrors, $cid, $errId) ?>
                    <?php elseif ($hasErr): ?>
                        <div class="invalid-feedback d-block" id="<?= htmlspecialchars($errId, ENT_QUOTES, 'UTF-8') ?>" role="alert"><?= htmlspecialchars((string) $fieldErrors[$cid], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <?= htmlspecialchars(__('btn.save') !== 'btn.save' ? __('btn.save') : 'Save changes', ENT_QUOTES, 'UTF-8') ?>
                </button>
                <a href="<?= htmlspecialchars((string) $returnUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm">
                    <?= htmlspecialchars(__('btn.cancel') !== 'btn.cancel' ? __('btn.cancel') : 'Cancel', ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loc-search').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var col = btn.getAttribute('data-col');
            var qEl = document.getElementById('loc_q_' + col);
            var box = document.getElementById('loc_results_' + col);
            var q = qEl ? qEl.value.trim() : '';
            if (q.length < 2 || !box) return;
            box.textContent = '';
            var base = <?= json_encode($basePath) ?>;
            fetch(base + '/api/geocode?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var hits = data.results || [];
                    if (!hits.length) {
                        box.innerHTML = '<div class="text-danger"><?= htmlspecialchars(__('data_entry.location_none') !== 'data_entry.location_none' ? __('data_entry.location_none') : 'No matching place. Try a nearby town.', ENT_QUOTES, 'UTF-8') ?></div>';
                        return;
                    }
                    hits.forEach(function (h) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'list-group-item list-group-item-action';
                        b.textContent = h.label;
                        b.addEventListener('click', function () {
                            document.getElementById('loc_hid_q_' + col).value = h.q || q;
                            document.getElementById('loc_lat_' + col).value = h.lat;
                            document.getElementById('loc_lng_' + col).value = h.lng;
                            var lab = document.getElementById('loc_label_' + col);
                            if (lab && lab.value.trim() === '') lab.value = h.label;
                            box.innerHTML = '';
                        });
                        box.appendChild(b);
                    });
                })
                .catch(function () {
                    box.innerHTML = '<div class="text-danger"><?= htmlspecialchars(__('data_entry.location_busy') !== 'data_entry.location_busy' ? __('data_entry.location_busy') : 'Place search is busy. Try again in a minute.', ENT_QUOTES, 'UTF-8') ?></div>';
                });
        });
    });
});
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
