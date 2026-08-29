<?php
declare(strict_types=1);
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
?>
<script>
(function () {
    const base = <?= json_encode($basePath, JSON_UNESCAPED_UNICODE) ?>;
    window.bindLocationSearch = function bindLocationSearch() {
        document.querySelectorAll('.js-loc-search').forEach(function (btn) {
            if (btn.dataset.bound === '1') return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                const col = btn.getAttribute('data-col');
                const qEl = document.getElementById('loc_q_' + col);
                const box = document.getElementById('loc_results_' + col);
                if (!qEl || !box) return;
                const q = qEl.value.trim();
                if (q.length < 2) return;
                box.innerHTML = '';
                fetch(base + '/api/geocode?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        const hits = data.results || [];
                        if (!hits.length) {
                            box.innerHTML = '<div class="text-danger" role="status"><?= htmlspecialchars(__('data_entry.location_none') !== 'data_entry.location_none' ? __('data_entry.location_none') : 'No matching place. Try a nearby town.', ENT_QUOTES, 'UTF-8') ?></div>';
                            return;
                        }
                        hits.forEach(function (h) {
                            const b = document.createElement('button');
                            b.type = 'button';
                            b.className = 'list-group-item list-group-item-action';
                            b.textContent = h.label || q;
                            b.addEventListener('click', function () {
                                const hq = document.getElementById('loc_hid_q_' + col);
                                const lat = document.getElementById('loc_lat_' + col);
                                const lng = document.getElementById('loc_lng_' + col);
                                const lab = document.getElementById('loc_label_' + col);
                                if (hq) hq.value = h.q || q;
                                if (lat) lat.value = h.lat;
                                if (lng) lng.value = h.lng;
                                if (lab) lab.value = h.label || q;
                                const title = document.getElementById('loc_title_' + col);
                                if (title && !title.value) title.value = h.label || q;
                                box.innerHTML = '';
                            });
                            box.appendChild(b);
                        });
                    })
                    .catch(function () {
                        box.innerHTML = '<div class="text-danger" role="status"><?= htmlspecialchars(__('data_entry.location_busy') !== 'data_entry.location_busy' ? __('data_entry.location_busy') : 'Place search is busy. Try again in a minute.', ENT_QUOTES, 'UTF-8') ?></div>';
                    });
            });
        });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.bindLocationSearch);
    } else {
        window.bindLocationSearch();
    }
})();
</script>
