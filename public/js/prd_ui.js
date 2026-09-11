(function () {
    document.querySelectorAll('.prd-lang-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var bar = btn.closest('.prd-a11y-bar');
            if (!bar) return;
            var on = bar.classList.toggle('prd-lang-open');
            btn.setAttribute('aria-expanded', on ? 'true' : 'false');
        });
    });
    var collapse = document.getElementById('mainNavbarContent');
    if (collapse) {
        collapse.addEventListener('shown.bs.collapse', function () {
            document.body.classList.add('prd-nav-open');
        });
        collapse.addEventListener('hidden.bs.collapse', function () {
            document.body.classList.remove('prd-nav-open');
        });
    }
    if (typeof bootstrap === 'undefined' || !bootstrap.Dropdown) {
        return;
    }
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (toggle) {
        try {
            bootstrap.Dropdown.getOrCreateInstance(toggle, {
                popperConfig: function (defaultConfig) {
                    return Object.assign({}, defaultConfig, {
                        strategy: 'fixed',
                        modifiers: (defaultConfig.modifiers || []).concat([
                            { name: 'flip', options: { fallbackPlacements: ['top', 'bottom'] } },
                            { name: 'preventOverflow', options: { boundary: 'viewport', padding: 8 } }
                        ])
                    });
                }
            });
        } catch (e) {}
    });
})();
