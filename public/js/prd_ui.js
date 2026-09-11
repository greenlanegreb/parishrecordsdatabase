(function () {
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
