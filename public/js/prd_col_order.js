(function () {
    function applyOrder(table, order) {
        if (!order || !order.length) return;
        var headRow = table.querySelector('thead tr');
        var bodyRows = table.querySelectorAll('tbody tr');
        if (!headRow) return;
        order.forEach(function (id) {
            var th = headRow.querySelector('th[data-col-id="' + id + '"]');
            if (th && id !== 'actions') headRow.appendChild(th);
        });
        var actTh = headRow.querySelector('th[data-col-id="actions"]');
        if (actTh) headRow.appendChild(actTh);
        bodyRows.forEach(function (tr) {
            order.forEach(function (id) {
                var td = tr.querySelector('td[data-col-id="' + id + '"]');
                if (td && id !== 'actions') tr.appendChild(td);
            });
            var act = tr.querySelector('td[data-col-id="actions"]');
            if (act) tr.appendChild(act);
        });
    }
    function currentOrder(table) {
        return Array.prototype.map.call(table.querySelectorAll('thead th[data-col-id]'), function (th) {
            return th.getAttribute('data-col-id');
        }).filter(function (id) { return id && id !== 'actions'; });
    }
    function mq(q) {
        return window.matchMedia && window.matchMedia(q).matches;
    }
    /**
     * Phones off (portrait or landscape). Tablets and desktops on.
     * A landscape phone is wide but short — treat that as a phone.
     */
    function isPhoneViewport() {
        if (mq('(max-width: 575.98px)')) return true;
        if (mq('(orientation: landscape) and (max-height: 500px)')) return true;
        return false;
    }
    function bind(table) {
        var id = table.getAttribute('data-table-id') || '0';
        var key = 'prd-col-order-' + id;
        try {
            var saved = JSON.parse(localStorage.getItem(key) || '[]');
            applyOrder(table, saved);
        } catch (e) {}
        var headRow = table.querySelector('thead tr');
        if (!headRow) return;
        table.addEventListener('prd-rows-updated', function () {
            try {
                applyOrder(table, JSON.parse(localStorage.getItem(key) || '[]'));
            } catch (e) {}
        });
        if (!window.Sortable || isPhoneViewport()) {
            return;
        }
        var coarse = mq('(pointer: coarse)');
        headRow.querySelectorAll('th[data-col-id]:not([data-col-id="actions"])').forEach(function (th) {
            if (!th.querySelector('.prd-col-handle')) {
                var h = document.createElement('button');
                h.type = 'button';
                h.className = 'prd-col-handle btn btn-sm btn-link text-decoration-none p-0 me-1';
                h.setAttribute('aria-label', 'Reorder column');
                h.title = 'Drag to change column order';
                h.textContent = '⋮⋮';
                th.insertBefore(h, th.firstChild);
            }
        });
        window.Sortable.create(headRow, {
            animation: 150,
            handle: '.prd-col-handle',
            draggable: 'th[data-col-id]:not([data-col-id="actions"])',
            filter: '.sortable-label, .sort-indicator',
            preventOnFilter: false,
            forceFallback: true,
            fallbackOnBody: true,
            delay: coarse ? 250 : 0,
            delayOnTouchStart: !!coarse,
            touchStartThreshold: 8,
            onEnd: function () {
                var order = currentOrder(table);
                try { localStorage.setItem(key, JSON.stringify(order)); } catch (e) {}
                applyOrder(table, order);
            }
        });
    }
    document.querySelectorAll('table.prd-col-order').forEach(bind);
})();
