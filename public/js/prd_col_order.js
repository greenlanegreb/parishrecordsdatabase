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
    function bind(table) {
        var id = table.getAttribute('data-table-id') || '0';
        var key = 'prd-col-order-' + id;
        try {
            var saved = JSON.parse(localStorage.getItem(key) || '[]');
            applyOrder(table, saved);
        } catch (e) {}
        var headRow = table.querySelector('thead tr');
        if (!headRow || !window.Sortable) return;
        window.Sortable.create(headRow, {
            animation: 150,
            draggable: 'th[data-col-id]:not([data-col-id="actions"])',
            onEnd: function () {
                var order = currentOrder(table);
                try { localStorage.setItem(key, JSON.stringify(order)); } catch (e) {}
                applyOrder(table, order);
            }
        });
        table.addEventListener('prd-rows-updated', function () {
            try {
                applyOrder(table, JSON.parse(localStorage.getItem(key) || '[]'));
            } catch (e) {}
        });
    }
    document.querySelectorAll('table.prd-col-order').forEach(bind);
})();
