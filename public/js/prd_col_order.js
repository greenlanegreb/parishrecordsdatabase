(function () {
    function colId(el) {
        return el ? el.getAttribute('data-col-id') : '';
    }

    function applyOrder(table, order) {
        if (!order || !order.length) return;
        var headRow = table.querySelector('thead tr');
        if (!headRow) return;
        order.forEach(function (id) {
            var th = headRow.querySelector('th[data-col-id="' + id + '"]');
            if (th && id !== 'actions') headRow.appendChild(th);
        });
        var actTh = headRow.querySelector('th[data-col-id="actions"]');
        if (actTh) headRow.appendChild(actTh);
        table.querySelectorAll('tbody tr').forEach(function (tr) {
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
            return colId(th);
        }).filter(function (id) { return id && id !== 'actions'; });
    }

    function mq(q) {
        return window.matchMedia && window.matchMedia(q).matches;
    }

    function isCoarse() {
        return mq('(pointer: coarse)');
    }

    function ensureChrome(th) {
        if (colId(th) === 'actions') return;
        th.querySelectorAll('.prd-col-handle').forEach(function (el) { el.parentNode.removeChild(el); });
        if (true) {
            var h = document.createElement('span');
            h.className = 'prd-col-handle';
            h.style.cssText = 'display:inline-block;width:2.5rem;height:2.5rem;min-width:2.5rem;min-height:2.5rem;cursor:grab;margin-right:0.2rem;vertical-align:middle;touch-action:none;-webkit-user-select:none;user-select:none;';
            h.setAttribute('role', 'button');
            h.setAttribute('tabindex', '0');
            h.setAttribute('aria-label', 'Hold then drag to change column order');
            h.title = 'Drag to change column order. On a phone or tablet, hold briefly first.';
            h.textContent = '';
            th.insertBefore(h, th.firstChild);
        }
        if (!th.querySelector('.prd-col-label')) {
            var raw = '';
            Array.prototype.slice.call(th.childNodes).forEach(function (n) {
                if (n.nodeType === 3) raw += n.textContent;
            });
            raw = raw.replace(/[\u2195\u25B2\u25BC\u22EE\u22F0.]/g, '').trim();
            Array.prototype.slice.call(th.childNodes).forEach(function (n) {
                if (n.nodeType === 3) th.removeChild(n);
            });
            var lab = document.createElement('span');
            lab.className = 'prd-col-label';
            lab.textContent = raw || th.getAttribute('data-col-name') || '';
            th.appendChild(lab);
        }
        if (!th.querySelector('.sort-indicator')) {
            var ind = document.createElement('span');
            ind.className = 'sort-indicator';
            ind.setAttribute('aria-hidden', 'true');
            ind.textContent = ' \u2195';
            th.appendChild(ind);
        }
        th.classList.add('sortable');
        if (!th.getAttribute('data-sort') && colId(th)) {
            var cid = colId(th);
            th.setAttribute('data-sort', (cid === 'created_at' || cid === 'created_by') ? cid : ('col_' + cid));
        }
    }

    function bindDrag(table) {
        var headRow = table.querySelector('thead tr');
        if (!headRow || headRow.getAttribute('data-prd-drag') === '1') return;
        headRow.setAttribute('data-prd-drag', '1');

        var dragging = null;
        var holdTimer = null;
        var startX = 0;
        var startY = 0;
        var HOLD_MS = 280;
        var MOVE_CANCEL = 10;

        function clearHold() {
            if (holdTimer) {
                clearTimeout(holdTimer);
                holdTimer = null;
            }
        }

        function startDrag(th) {
            dragging = th;
            th.classList.add('prd-col-dragging');
            document.body.style.userSelect = 'none';
        }

        function moveDrag(clientX, clientY) {
            if (!dragging || dragging.parentNode !== headRow) return;
            var over = document.elementFromPoint(clientX, clientY);
            var target = over && over.closest ? over.closest('th[data-col-id]') : null;
            if (!target || target.parentNode !== headRow || target === dragging || colId(target) === 'actions') return;
            var kids = Array.prototype.slice.call(headRow.children);
            if (kids.indexOf(target) < kids.indexOf(dragging)) {
                headRow.insertBefore(dragging, target);
            } else {
                headRow.insertBefore(dragging, target.nextSibling);
            }
        }

        function endDrag() {
            clearHold();
            if (!dragging) return;
            dragging.classList.remove('prd-col-dragging');
            dragging = null;
            document.body.style.userSelect = '';
            var id = table.getAttribute('data-table-id') || '0';
            var order = currentOrder(table);
            try { localStorage.setItem('prd-col-order-' + id, JSON.stringify(order)); } catch (err) {}
            applyOrder(table, order);
        }

        function onDown(e) {
            var handle = e.target.closest ? e.target.closest('.prd-col-handle') : null;
            if (!handle || !headRow.contains(handle)) return;
            var th = handle.closest('th');
            if (!th || colId(th) === 'actions') return;
            var pt = e.touches && e.touches[0] ? e.touches[0] : e;
            startX = pt.clientX;
            startY = pt.clientY;
            var isTouch = !!(e.touches || e.type === 'touchstart');
            if (isTouch) {
                clearHold();
                holdTimer = setTimeout(function () {
                    holdTimer = null;
                    startDrag(th);
                }, HOLD_MS);
            } else {
                e.preventDefault();
                startDrag(th);
            }
        }

        function onMove(e) {
            var pt = e.touches && e.touches[0] ? e.touches[0] : e;
            if (holdTimer) {
                var dx = Math.abs(pt.clientX - startX);
                var dy = Math.abs(pt.clientY - startY);
                if (dx > MOVE_CANCEL || dy > MOVE_CANCEL) {
                    clearHold();
                }
                return;
            }
            if (!dragging) return;
            if (e.cancelable) e.preventDefault();
            moveDrag(pt.clientX, pt.clientY);
        }

        headRow.addEventListener('mousedown', onDown);
        headRow.addEventListener('touchstart', onDown, { passive: false });
        document.addEventListener('mousemove', onMove);
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('mouseup', endDrag);
        document.addEventListener('touchend', endDrag);
        document.addEventListener('touchcancel', endDrag);
    }

    function bind(table) {
        if (!table) return;
        var id = table.getAttribute('data-table-id') || '0';
        try {
            applyOrder(table, JSON.parse(localStorage.getItem('prd-col-order-' + id) || '[]'));
        } catch (e) {}
        var headRow = table.querySelector('thead tr');
        if (!headRow) return;
        headRow.querySelectorAll('th[data-col-id]').forEach(ensureChrome);
        bindDrag(table);
    }

    function markSort(table, sortKey, dir) {
        if (!table) return;
        table.querySelectorAll('th.sortable .sort-indicator').forEach(function (ind) {
            ind.textContent = ' \u2195';
        });
        table.querySelectorAll('th.sortable').forEach(function (th) {
            if (th.getAttribute('data-sort') === sortKey) {
                var ind = th.querySelector('.sort-indicator');
                if (ind) ind.textContent = dir === 'ASC' ? ' \u25B2' : ' \u25BC';
                th.setAttribute('aria-sort', dir === 'ASC' ? 'ascending' : 'descending');
            } else {
                th.removeAttribute('aria-sort');
            }
        });
    }

    function boot() {
        document.querySelectorAll('table.prd-col-order').forEach(bind);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
    window.addEventListener('load', boot);

    window.prdInitColOrder = bind;
    window.prdMarkSort = markSort;

    document.addEventListener('prd-rows-updated', function (e) {
        var table = e.target;
        if (!table || !table.classList || !table.classList.contains('prd-col-order')) return;
        var id = table.getAttribute('data-table-id') || '0';
        var order = [];
        try { order = JSON.parse(localStorage.getItem('prd-col-order-' + id) || '[]'); } catch (err) { order = []; }
        if (!order.length) order = currentOrder(table);
        applyOrder(table, order);
    }, true);
})();
