<?php
declare(strict_types=1);
/**
 * Guide and check date fields (.date-input) using the viewer's date format
 * (profile, else site default). Stored values stay Y-m-d / Y / Y-m via PHP.
 */
$__pdo = $pdo ?? ($GLOBALS['pdo'] ?? null);
$__user = (isset($currentUser) && is_array($currentUser)) ? $currentUser : [];
if (function_exists('get_site_datetime_defaults')) {
    [, $siteDate] = array_pad(get_site_datetime_defaults($__pdo instanceof PDO ? $__pdo : null), 2, 'd/m/Y');
} else {
    $siteDate = 'd/m/Y';
}
$__fmt = (isset($__user['date_format']) && is_string($__user['date_format']) && $__user['date_format'] !== '')
    ? $__user['date_format']
    : (string) $siteDate;
$__ph = function_exists('get_date_placeholder') ? get_date_placeholder($__fmt) : $__fmt;
$__hint = (__('data_entry.date_format_hint') !== 'data_entry.date_format_hint')
    ? __('data_entry.date_format_hint')
    : 'Please use the date format shown in the box.';
?>
<script>
(function () {
    var fmt = <?= json_encode($__fmt, JSON_UNESCAPED_UNICODE) ?>;
    var ph = <?= json_encode($__ph, JSON_UNESCAPED_UNICODE) ?>;
    var hint = <?= json_encode($__hint, JSON_UNESCAPED_UNICODE) ?>;

    function sep() {
        if (fmt.indexOf('.') !== -1) return '.';
        if (fmt.indexOf('-') !== -1) return '-';
        return '/';
    }

    function looksYear(v) { return /^\d{4}$/.test(v); }
    function looksYearMonth(v) { return /^\d{4}[\/.\-]\d{1,2}$/.test(v) || /^\d{1,2}[\/.\-]\d{4}$/.test(v); }

    function parseBits(v) {
        var s = sep();
        var parts = v.split(/[\/.\-]/);
        if (parts.length !== 3) return null;
        var y, m, d;
        if (fmt.charAt(0) === 'Y') { y = parts[0]; m = parts[1]; d = parts[2]; }
        else if (fmt.charAt(0) === 'm') { m = parts[0]; d = parts[1]; y = parts[2]; }
        else { d = parts[0]; m = parts[1]; y = parts[2]; }
        y = parseInt(y, 10); m = parseInt(m, 10); d = parseInt(d, 10);
        if (!y || !m || !d) return null;
        if (m < 1 || m > 12 || d < 1 || d > 31) return null;
        var dt = new Date(Date.UTC(y, m - 1, d));
        if (dt.getUTCFullYear() !== y || dt.getUTCMonth() !== m - 1 || dt.getUTCDate() !== d) return null;
        return true;
    }

    function valid(v) {
        v = (v || '').trim();
        if (v === '') return true;
        if (looksYear(v) || looksYearMonth(v)) return true;
        return parseBits(v) !== null;
    }

    function guide(el) {
        var raw = el.value.replace(/[^\d]/g, '');
        if (raw.length === 0) return;
        if (raw.length <= 4 && fmt.charAt(0) === 'Y') {
            el.value = raw;
            return;
        }
        var s = sep();
        var a, b, c;
        if (fmt.charAt(0) === 'Y') {
            a = raw.slice(0, 4);
            b = raw.slice(4, 6);
            c = raw.slice(6, 8);
        } else {
            a = raw.slice(0, 2);
            b = raw.slice(2, 4);
            c = raw.slice(4, 8);
        }
        var out = a;
        if (b) out += s + b;
        if (c) out += s + c;
        el.value = out;
    }

    function mark(el, ok) {
        el.classList.toggle('is-invalid', !ok);
        var id = el.getAttribute('aria-describedby');
        var msg = id ? document.getElementById(id) : null;
        if (!ok) {
            if (!msg) {
                msg = document.createElement('div');
                msg.id = el.id ? (el.id + '-date-err') : '';
                msg.className = 'invalid-feedback d-block';
                msg.textContent = hint + ' (' + ph + ')';
                el.insertAdjacentElement('afterend', msg);
                if (msg.id) el.setAttribute('aria-describedby', msg.id);
            }
            msg.hidden = false;
        } else if (msg) {
            msg.hidden = true;
        }
    }

    function bind(el) {
        if (el.dataset.prdDateBound === '1') return;
        el.dataset.prdDateBound = '1';
        if (!el.getAttribute('placeholder')) el.setAttribute('placeholder', ph);
        el.setAttribute('inputmode', 'numeric');
        el.setAttribute('autocomplete', 'off');
        el.addEventListener('input', function () { guide(el); });
        el.addEventListener('blur', function () { mark(el, valid(el.value)); });
    }

    function scan() {
        document.querySelectorAll('.date-input').forEach(bind);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scan);
    } else {
        scan();
    }
    document.addEventListener('prd:dates-ready', scan);
})();
</script>
