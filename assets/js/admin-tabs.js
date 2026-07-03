// admin-tabs.js
// Handles admin page tab activation via submenu anchors and hash navigation
(function(){
    function activateTabByName(tabName, pushHistory = true) {
        if (!tabName) return;
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

        const targetTab = document.getElementById(tabName);
        if (targetTab) targetTab.classList.add('active');

        const btn = document.querySelector('.tab-btn[data-target="' + tabName + '"]') || document.querySelector('.tab-btn[href="#' + tabName + '"]');
        if (btn) btn.classList.add('active');

        if (pushHistory) {
            try { history.replaceState(null, null, '#'+tabName); } catch (e) { location.hash = '#'+tabName; }
        }
    }

    function initTabs() {
        const nav = document.querySelector('.tabs');
        console.debug('admin-tabs: initTabs called, nav=', !!nav);
        if (!nav) return;

        // Delegated click handler
        nav.addEventListener('click', function(e){
            const el = e.target.closest('.tab-btn');
            if (!el) return;
            e.preventDefault();
            const target = el.dataset.target || (el.getAttribute('href') || '').replace('#','');
            console.debug('admin-tabs: clicked', el, 'target=', target);
            if (target) activateTabByName(target);
        });

        // initial activation
        const initial = (location.hash || '#cadastro').replace('#','');
        console.debug('admin-tabs: initial tab=', initial);
        activateTabByName(initial, false);

        window.addEventListener('hashchange', function(){
            const h = (location.hash || '').replace('#','');
            console.debug('admin-tabs: hashchange to', h);
            if (h) activateTabByName(h, false);
        });
    }

    // Expose for debugging and call appropriately depending on readiness
    window.activateTabByName = activateTabByName;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTabs);
    } else {
        initTabs();
    }
})();
