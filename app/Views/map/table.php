<?php
declare(strict_types=1);
/** @var array<string,mixed> $table */
/** @var array<int,array<string,mixed>> $columns */
/** @var string $tileUrl */
/** @var string $tileAttribution */
/** @var int $activeTableId */
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$defaultTiles = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
$tiles = (isset($tileUrl) && is_string($tileUrl) && trim($tileUrl) !== '') ? $tileUrl : $defaultTiles;
$tilesLower = strtolower($tiles);
$hasKey = str_contains($tilesLower, 'access_token=') || str_contains($tilesLower, 'api_key=')
    || str_contains($tilesLower, '?key=') || str_contains($tilesLower, '&key=');
if (!$hasKey && (
    str_contains($tilesLower, 'stadiamaps.com')
    || str_contains($tilesLower, 'mapbox.com')
    || str_contains($tilesLower, 'basemaps.cartocdn.com')
)) {
    $tiles = $defaultTiles;
}
$tileAttribution = (isset($tileAttribution) && is_string($tileAttribution) && $tileAttribution !== '')
    ? $tileAttribution
    : '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>';
require_once ROOT_PATH . '/partials/header.php';
?>
<div class="container-fluid px-3" role="region" aria-labelledby="map-heading">
    <a class="visually-hidden-focusable btn btn-sm btn-outline-secondary mb-2" href="#prd-map"><?= htmlspecialchars(__('map.skip_to_map') !== 'map.skip_to_map' ? __('map.skip_to_map') : 'Skip to map', ENT_QUOTES, 'UTF-8') ?></a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0" id="map-heading"><?= htmlspecialchars((__('map.heading') !== 'map.heading' ? __('map.heading') : 'Map') . ': ' . (string) ($table['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/?table_id=<?= (int) $activeTableId ?>"><?= htmlspecialchars(__('map.back_to_table') !== 'map.back_to_table' ? __('map.back_to_table') : 'Back to table', ENT_QUOTES, 'UTF-8') ?></a>
    </div>
    <p class="small text-muted"><?= htmlspecialchars(__('map.help') !== 'map.help' ? __('map.help') : 'Zoom to load places in view. Use the filters to match the table search.', ENT_QUOTES, 'UTF-8') ?></p>
    <div class="row g-3">
        <div class="col-lg-3">
            <form id="map-filter-form" class="card card-body shadow-sm border-0" onsubmit="return false;" aria-labelledby="map-filters-heading">
                <p class="fw-bold small mb-2" id="map-filters-heading"><?= htmlspecialchars(__('map.filters') !== 'map.filters' ? __('map.filters') : 'Filters', ENT_QUOTES, 'UTF-8') ?></p>
                <?php
                /** @var string $datePlaceholder from TableMapController (profile → site default) */
                $datePlaceholder = isset($datePlaceholder) && is_string($datePlaceholder) && $datePlaceholder !== ''
                    ? $datePlaceholder
                    : (function_exists('get_date_placeholder') ? get_date_placeholder(null) : 'YYYY-MM-DD');
                foreach ($columns as $col):
                    $cId = (int) ($col['id'] ?? 0);
                    $cName = (string) ($col['column_name'] ?? '');
                    $dt = (string) ($col['data_type'] ?? '');
                    if ($dt === 'LOCATION' || $cId < 1) {
                        continue;
                    }
                ?>
                    <?php if ($dt === 'DATE'): ?>
                        <fieldset class="border-0 p-0 mb-2">
                            <legend class="form-label small fw-bold mb-1"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></legend>
                            <div class="d-flex flex-column gap-1">
                                <label class="form-label small mb-0" for="mapf_from_<?= $cId ?>"><?= htmlspecialchars(__('data_entry.date_from_label') !== 'data_entry.date_from_label' ? __('data_entry.date_from_label') : 'From', ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" id="mapf_from_<?= $cId ?>" class="form-control form-control-sm js-map-date-from" data-col="<?= $cId ?>" autocomplete="off" placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($cName . ' — ' . (__('data_entry.date_from_label') !== 'data_entry.date_from_label' ? __('data_entry.date_from_label') : 'From'), ENT_QUOTES, 'UTF-8') ?>">
                                <label class="form-label small mb-0" for="mapf_to_<?= $cId ?>"><?= htmlspecialchars(__('data_entry.date_to_label') !== 'data_entry.date_to_label' ? __('data_entry.date_to_label') : 'To', ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" id="mapf_to_<?= $cId ?>" class="form-control form-control-sm js-map-date-to" data-col="<?= $cId ?>" autocomplete="off" placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($cName . ' — ' . (__('data_entry.date_to_label') !== 'data_entry.date_to_label' ? __('data_entry.date_to_label') : 'To'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </fieldset>
                    <?php elseif ($dt === 'BOOLEAN'): ?>
                        <?php
                            $displayFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                            $opt1Text = __('data_entry.bool_yes_true');
                            $opt2Text = __('data_entry.bool_no_false');
                            if ($displayFormat === 'male_female') { $opt1Text = __('data_entry.bool_male'); $opt2Text = __('data_entry.bool_female'); }
                            elseif ($displayFormat === 'true_false') { $opt1Text = __('data_entry.bool_true'); $opt2Text = __('data_entry.bool_false'); }
                            elseif ($displayFormat === 'tick_cross') { $opt1Text = __('data_entry.bool_tick'); $opt2Text = __('data_entry.bool_cross'); }
                        ?>
                        <label class="form-label small mb-1" for="mapf_<?= $cId ?>"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="mapf_<?= $cId ?>" class="form-select form-select-sm mb-2 js-map-filter" data-col="<?= $cId ?>">
                            <option value=""><?= htmlspecialchars(__('feedback.select_placeholder') !== 'feedback.select_placeholder' ? __('feedback.select_placeholder') : 'Any', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="1"><?= htmlspecialchars($opt1Text, ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="0"><?= htmlspecialchars($opt2Text, ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    <?php elseif ($dt === 'SELECT'): ?>
                        <?php
                            $optsRaw = isset($col['field_options']) && is_string($col['field_options']) ? $col['field_options'] : '';
                            $opts = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $optsRaw) ?: [])));
                        ?>
                        <label class="form-label small mb-1" for="mapf_<?= $cId ?>"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="mapf_<?= $cId ?>" class="form-select form-select-sm mb-2 js-map-filter" data-col="<?= $cId ?>">
                            <option value=""><?= htmlspecialchars(__('feedback.select_placeholder') !== 'feedback.select_placeholder' ? __('feedback.select_placeholder') : 'Any', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php foreach ($opts as $opt): ?>
                                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <label class="form-label small mb-1" for="mapf_<?= $cId ?>"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="text" class="form-control form-control-sm mb-2 js-map-filter" id="mapf_<?= $cId ?>" data-col="<?= $cId ?>" autocomplete="off">
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="button" class="btn btn-sm btn-primary" id="map-apply-filters"><?= htmlspecialchars(__('map.apply_filters') !== 'map.apply_filters' ? __('map.apply_filters') : 'Apply filters', ENT_QUOTES, 'UTF-8') ?></button>
            </form>
            <p class="fw-bold small mb-1 mt-3" id="map-list-heading"><?= htmlspecialchars(__('map.list_heading') !== 'map.list_heading' ? __('map.list_heading') : 'Places in this view', ENT_QUOTES, 'UTF-8') ?></p>
            <ul id="map-list" class="list-group list-group-flush small" style="max-height: 50vh; overflow-y: auto;" aria-labelledby="map-list-heading" aria-live="polite"></ul>
        </div>
        <div class="col-lg-9">
            <div id="prd-map" style="min-height: 70vh; border: 1px solid #ccc;" role="application" aria-label="<?= htmlspecialchars(__('map.canvas_aria') !== 'map.canvas_aria' ? __('map.canvas_aria') : 'Map of records', ENT_QUOTES, 'UTF-8') ?>"></div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
(function () {
    const tableId = <?= (int) $activeTableId ?>;
    const base = <?= json_encode($basePath) ?>;
    const tileUrl = <?= json_encode($tiles) ?>;
    const tileAttr = <?= json_encode($tileAttribution, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const map = L.map('prd-map').setView([54.5, -3], 6);
    L.tileLayer(tileUrl, {
        maxZoom: 18,
        attribution: tileAttr
    }).addTo(map);
    if (map.attributionControl) {
        map.attributionControl.setPrefix('');
    }
    const cluster = L.markerClusterGroup();
    map.addLayer(cluster);
    const list = document.getElementById('map-list');

    function filtersQuery() {
        const parts = [];
        document.querySelectorAll('.js-map-filter').forEach(function (el) {
            if (el.value.trim() !== '') {
                parts.push('filters[' + el.getAttribute('data-col') + ']=' + encodeURIComponent(el.value.trim()));
            }
        });
        document.querySelectorAll('.js-map-date-from').forEach(function (el) {
            if (el.value.trim() !== '') {
                parts.push('date_filters[' + el.getAttribute('data-col') + '][from]=' + encodeURIComponent(el.value.trim()));
            }
        });
        document.querySelectorAll('.js-map-date-to').forEach(function (el) {
            if (el.value.trim() !== '') {
                parts.push('date_filters[' + el.getAttribute('data-col') + '][to]=' + encodeURIComponent(el.value.trim()));
            }
        });
        return parts.join('&');
    }

    function loadPoints() {
        const b = map.getBounds();
        let url = base + '/api/map-points?table_id=' + tableId
            + '&south=' + b.getSouth() + '&north=' + b.getNorth()
            + '&west=' + b.getWest() + '&east=' + b.getEast();
        const extra = filtersQuery();
        if (extra) url += '&' + extra;
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cluster.clearLayers();
                list.innerHTML = '';
                const points = data.points || [];
                const markers = [];
                points.forEach(function (p) {
                    const m = L.circleMarker([p.lat, p.lng], {
                        radius: 8,
                        color: '#000',
                        weight: 1,
                        fillColor: p.color || '#c0392b',
                        fillOpacity: 0.9
                    });
                    const html = '<strong>' + escapeHtml(p.title || p.label) + '</strong><br>' + escapeHtml(p.body || '') +
                        (p.label ? '<br><em>' + escapeHtml(p.label) + '</em>' : '');
                    m.bindPopup(html);
                    cluster.addLayer(m);
                    markers.push({ p: p, m: m });
                });
                // List is for navigation only — page it; map still shows every pin in view
                const pageSize = 25;
                let shown = 0;
                function appendListBatch() {
                    const end = Math.min(shown + pageSize, markers.length);
                    for (let i = shown; i < end; i++) {
                        (function (item) {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.innerHTML = '<button type="button" class="btn btn-link p-0 text-start">' + escapeHtml(item.p.title || item.p.label) + '</button>';
                            li.querySelector('button').addEventListener('click', function () {
                                map.setView([item.p.lat, item.p.lng], 13);
                                item.m.openPopup();
                            });
                            list.appendChild(li);
                        })(markers[i]);
                    }
                    shown = end;
                    const moreBtn = list.querySelector('.js-map-list-more');
                    if (moreBtn) {
                        moreBtn.parentElement.remove();
                    }
                    if (shown < markers.length) {
                        const li = document.createElement('li');
                        li.className = 'list-group-item border-0 px-0';
                        const left = markers.length - shown;
                        const label = <?= json_encode(__('map.list_show_more') !== 'map.list_show_more' ? __('map.list_show_more') : 'Show more in list (:n left)', JSON_UNESCAPED_UNICODE) ?>.replace(':n', String(left));
                        li.innerHTML = '<button type="button" class="btn btn-sm btn-outline-secondary w-100 js-map-list-more">' + escapeHtml(label) + '</button>';
                        li.querySelector('button').addEventListener('click', appendListBatch);
                        list.appendChild(li);
                    }
                }
                if (markers.length === 0) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item text-muted';
                    li.textContent = <?= json_encode(__('map.list_empty') !== 'map.list_empty' ? __('map.list_empty') : 'No places in this view.', JSON_UNESCAPED_UNICODE) ?>;
                    list.appendChild(li);
                } else {
                    appendListBatch();
                }
            })
            .catch(function () { /* ignore */ });
    }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]);
        });
    }
    map.on('moveend', loadPoints);
    document.getElementById('map-apply-filters').addEventListener('click', loadPoints);
    loadPoints();
})();
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
