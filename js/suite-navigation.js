(function () {
    'use strict';

    function loadInitialState(app, key, fallback) {
        const element = document.querySelector(`#initial-state-${app}-${key}`);
        if (!element) {
            return fallback;
        }
        try {
            return JSON.parse(window.atob(element.value));
        } catch (_error) {
            return fallback;
        }
    }

    const suites = Object.freeze(loadInitialState('orgsuite', 'suite-navigation', {}));
    const translate = typeof window.t === 'function' ? window.t : (_app, text) => text;

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
        nav.setAttribute('aria-label', translate('orgsuite', definition.label));

        const list = document.createElement('ul');
        list.className = 'orgsuite-nav__list';

        definition.items.forEach((item) => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            link.className = 'orgsuite-nav__link';
            link.href = item.href || appUrl(item.app);
            link.textContent = translate(item.app, item.label);
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
