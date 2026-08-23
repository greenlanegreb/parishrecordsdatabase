<?php
declare(strict_types=1);
/** @var array<string,mixed> $table */
/** @var array<int,array<string,mixed>> $columns */
/** @var string $tileUrl */
/** @var int $activeTableId */
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$defaultTiles = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
$tiles = trim($tileUrl) !== '' ? $tileUrl : $defaultTiles;
require_once ROOT_PATH . '/partials/header.php';
?>
<div class="container-fluid px-3" role="region" aria-labelledby="map-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0" id="map-heading"><?= htmlspecialchars((__('map.heading') !== 'map.heading' ? __('map.heading') : 'Map') . ': ' . (string) ($table['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/?table_id=<?= (int) $activeTableId ?>"><?= htmlspecialchars(__('map.back_to_table') !== 'map.back_to_table' ? __('map.back_to_table') : 'Back to table', ENT_QUOTES, 'UTF-8') ?></a>
    </div>
    <p class="small text-muted"><?= htmlspecialchars(__('map.help') !== 'map.help' ? __('map.help') : 'Zoom to load places in view. Use the filters to match the table search.', ENT_QUOTES, 'UTF-8') ?></p>
    <div class="row g-3">
        <div class="col-lg-3">
            <form id="map-filter-form" class="card card-body shadow-sm border-0" onsubmit="return false;">
                <p class="fw-bold small mb-2"><?= htmlspecialchars(__('map.filters') !== 'map.filters' ? __('map.filters') : 'Filters', ENT_QUOTES, 'UTF-8') ?></p>
                <?php foreach ($columns as $col): ?>
                    <?php
                        $cId = (int) ($col['id'] ?? 0);
                        $cName = (string) ($col['column_name'] ?? '');
                        $dt = (string) ($col['data_type'] ?? '');
                        if ($dt === 'LOCATION') {
                            continue;
                        }
                    ?>
                    <label class="form-label small mb-1" for="mapf_<?= $cId ?>"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" class="form-control form-control-sm mb-2 js-map-filter" id="mapf_<?= $cId ?>" data-col="<?= $cId ?>" autocomplete="off">
                <?php endforeach; ?>
                <button type="button" class="btn btn-sm btn-primary" id="map-apply-filters"><?= htmlspecialchars(__('map.apply_filters') !== 'map.apply_filters' ? __('map.apply_filters') : 'Apply filters', ENT_QUOTES, 'UTF-8') ?></button>
            </form>
            <ul id="map-list" class="list-group list-group-flush mt-3 small" aria-live="polite"></ul>
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
    const map = L.map('prd-map').setView([54.5, -3], 6);
    L.tileLayer(tileUrl, { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
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
                (data.points || []).forEach(function (p) {
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
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.innerHTML = '<button type="button" class="btn btn-link p-0 text-start">' + escapeHtml(p.title || p.label) + '</button>';
                    li.querySelector('button').addEventListener('click', function () {
                        map.setView([p.lat, p.lng], 13);
                        m.openPopup();
                    });
                    list.appendChild(li);
                });
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
