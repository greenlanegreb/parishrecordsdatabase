<?php
declare(strict_types=1);
/**
 * Shared location field inputs (data entry, direct edit, suggest edit).
 *
 * @var int $colId
 * @var array{q?:string,label?:string,lat?:float,lng?:float,title?:string,body?:string,color?:string,show_on_map?:bool} $loc
 * @var string $namePrefix e.g. "fields[12]" or "proposed_value"
 * @var bool $showOnMapToggle
 */
$colId = (int) ($colId ?? 0);
$loc = is_array($loc ?? null) ? $loc : [];
$namePrefix = isset($namePrefix) && is_string($namePrefix) ? $namePrefix : 'fields[' . $colId . ']';
$showOnMapToggle = !isset($showOnMapToggle) || $showOnMapToggle;
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$loc = array_merge([
    'q' => '', 'label' => '', 'lat' => 0.0, 'lng' => 0.0,
    'title' => '', 'body' => '', 'color' => \App\Services\LocationValueService::defaultColor(),
    'show_on_map' => true,
], $loc);
?>
<p class="form-text small"><?= htmlspecialchars(__('data_entry.location_help') !== 'data_entry.location_help' ? __('data_entry.location_help') : 'Search for the place as it is known today, pick a match, then you may word the label as the old name. Title and short text are required for the map popup.', ENT_QUOTES, 'UTF-8') ?></p>
<label class="form-label small" for="loc_q_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_search') !== 'data_entry.location_search' ? __('data_entry.location_search') : 'Find place', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
<div class="input-group input-group-sm mb-2">
    <input type="text" id="loc_q_<?= $colId ?>" class="form-control js-loc-q" autocomplete="off" value="<?= htmlspecialchars((string)($loc['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <button type="button" class="btn btn-outline-secondary js-loc-search" data-col="<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_search_btn') !== 'data_entry.location_search_btn' ? __('data_entry.location_search_btn') : 'Search', ENT_QUOTES, 'UTF-8') ?></button>
</div>
<div id="loc_results_<?= $colId ?>" class="list-group mb-2 small" role="listbox" aria-label="<?= htmlspecialchars(__('data_entry.location_results') !== 'data_entry.location_results' ? __('data_entry.location_results') : 'Did you mean', ENT_QUOTES, 'UTF-8') ?>"></div>
<input type="hidden" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[q]" id="loc_hid_q_<?= $colId ?>" value="<?= htmlspecialchars((string)($loc['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[lat]" id="loc_lat_<?= $colId ?>" value="<?= htmlspecialchars((string)($loc['lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[lng]" id="loc_lng_<?= $colId ?>" value="<?= htmlspecialchars((string)($loc['lng'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="form-label small" for="loc_label_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_label') !== 'data_entry.location_label' ? __('data_entry.location_label') : 'Name to show (you may use the historic name)', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
<input type="text" class="form-control form-control-sm mb-2" id="loc_label_<?= $colId ?>" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[label]" value="<?= htmlspecialchars((string)($loc['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label class="form-label small" for="loc_title_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_title') !== 'data_entry.location_title' ? __('data_entry.location_title') : 'Popup title', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
<input type="text" class="form-control form-control-sm mb-2" id="loc_title_<?= $colId ?>" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[title]" value="<?= htmlspecialchars((string)($loc['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
<label class="form-label small" for="loc_body_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_body') !== 'data_entry.location_body' ? __('data_entry.location_body') : 'Popup text', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
<textarea class="form-control form-control-sm mb-2" id="loc_body_<?= $colId ?>" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[body]" rows="2" required><?= htmlspecialchars((string)($loc['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
<fieldset class="mb-2">
    <legend class="form-label small"><?= htmlspecialchars(__('data_entry.location_color') !== 'data_entry.location_color' ? __('data_entry.location_color') : 'Pin colour', ENT_QUOTES, 'UTF-8') ?></legend>
    <?php foreach (\App\Services\LocationValueService::palette() as $sw): ?>
        <label class="me-2 small">
            <input type="radio" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[color]" value="<?= htmlspecialchars($sw['hex'], ENT_QUOTES, 'UTF-8') ?>" <?= (($loc['color'] ?? '') === $sw['hex'] || (($loc['color'] ?? '') === '' && $sw['hex'] === \App\Services\LocationValueService::defaultColor())) ? 'checked' : '' ?>>
            <span style="display:inline-block;width:0.9rem;height:0.9rem;background:<?= htmlspecialchars($sw['hex'], ENT_QUOTES, 'UTF-8') ?>;border:1px solid #000;vertical-align:middle;" title="<?= htmlspecialchars($sw['label'], ENT_QUOTES, 'UTF-8') ?>"></span>
            <span class="visually-hidden"><?= htmlspecialchars($sw['label'], ENT_QUOTES, 'UTF-8') ?></span>
        </label>
    <?php endforeach; ?>
</fieldset>
<?php if ($showOnMapToggle): ?>
<div class="form-check mb-2">
    <input type="hidden" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[show_on_map]" value="0">
    <input class="form-check-input" type="checkbox" name="<?= htmlspecialchars($namePrefix, ENT_QUOTES, 'UTF-8') ?>[show_on_map]" value="1" id="loc_show_<?= $colId ?>" <?= (!isset($loc['show_on_map']) || $loc['show_on_map']) ? 'checked' : '' ?>>
    <label class="form-check-label small" for="loc_show_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_show_on_map') !== 'data_entry.location_show_on_map' ? __('data_entry.location_show_on_map') : 'Show this place on the map', ENT_QUOTES, 'UTF-8') ?></label>
</div>
<?php endif; ?>
