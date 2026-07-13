(function () {
    'use strict';

    const suites = Object.freeze({
        ad: Object.freeze({
            label: 'AD-Anwendungen',
            items: Object.freeze([
                Object.freeze({app: 'adcalendar', label: 'Kalender'}),
                Object.freeze({app: 'adplaner', label: 'Assistenzplanung'}),
                Object.freeze({app: 'adurlaub', label: 'Urlaub'}),
            ]),
        }),
        br: Object.freeze({
            label: 'BR-Anwendungen',
            items: Object.freeze([
                Object.freeze({app: 'brtop', label: 'Sitzungen'}),
                Object.freeze({app: 'brstunden', label: 'Stunden'}),
                Object.freeze({app: 'br_permission_matrix', label: 'Berechtigungsmatrix'}),
            ]),
        }),
    });

    function appUrl(appId) {
        return window.OC.generateUrl('/apps/{appId}/', {appId});
    }

    function mount(host) {
        const definition = suites[host.dataset.suite];
        if (!definition || host.dataset.orgsuiteMounted === 'true') {
            return;
        }

        const nav = document.createElement('nav');
        nav.className = 'orgsuite-nav';
        nav.setAttribute('aria-label', definition.label);

        const list = document.createElement('ul');
        list.className = 'orgsuite-nav__list';

        definition.items.forEach((item) => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            link.className = 'orgsuite-nav__link';
            link.href = appUrl(item.app);
            link.textContent = item.label;
            if (host.dataset.currentApp === item.app) {
                link.classList.add('is-current');
                link.setAttribute('aria-current', 'page');
            }
            listItem.append(link);
            list.append(listItem);
        });

        nav.append(list);
        host.replaceChildren(nav);
        host.dataset.orgsuiteMounted = 'true';
    }

    function mountAll() {
        document.querySelectorAll('[data-orgsuite]').forEach(mount);
    }

    window.OrgSuiteNavigation = Object.freeze({suites, mount, mountAll});
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountAll, {once: true});
    } else {
        mountAll();
    }
}());

