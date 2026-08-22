(function() {
    'use strict';

    const endpoint = () => window.OC.generateUrl('/apps/orgsuite/api/admin/external-links');
    const createDraft = suite => ({
        id: window.crypto.randomUUID().toLowerCase(),
        suite,
        label: '',
        url: 'https://',
        active: true,
    });

    window.OrgSuiteExternalLinksAdmin = Object.freeze({createDraft});

    function init() {
        const root = document.getElementById('orgsuite-admin');
        if (!root) return;

        let links = [];
        const section = document.createElement('section');
        section.className = 'orgsuite-external-admin';
        section.setAttribute('aria-labelledby', 'orgsuite-external-heading');
        const heading = document.createElement('h2');
        heading.id = 'orgsuite-external-heading';
        heading.textContent = 'Externe Menülinks';
        const description = document.createElement('p');
        description.textContent = 'Zusätzliche HTTPS-Links werden für alle angemeldeten Personen im gewählten AD- oder BR-Menü angezeigt. Sie erteilen keine Rechte im Zielsystem.';
        const notice = document.createElement('p');
        notice.className = 'orgsuite-external-notice';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');
        const form = document.createElement('form');
        const toolbar = document.createElement('div');
        toolbar.className = 'orgsuite-external-toolbar';
        toolbar.append(addButton('ad', 'AD-Link hinzufügen'), addButton('br', 'BR-Link hinzufügen'));
        const list = document.createElement('div');
        list.className = 'orgsuite-external-list';
        const save = document.createElement('button');
        save.type = 'submit';
        save.className = 'primary';
        save.textContent = 'Externe Links speichern';
        form.append(toolbar, list, save);
        section.append(heading, description, notice, form);
        root.append(section);

        function addButton(suite, text) {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.action = 'add-link';
            button.dataset.suite = suite;
            button.textContent = text;
            return button;
        }

        function fieldLabel(text, control) {
            const label = document.createElement('label');
            const caption = document.createElement('span');
            caption.textContent = text;
            label.append(caption, control);
            return label;
        }

        function input(name, value, options = {}) {
            const control = document.createElement('input');
            control.name = name;
            control.value = value;
            Object.assign(control, options);
            return control;
        }

        function actionButton(action, text, disabled = false) {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.action = action;
            button.textContent = text;
            button.disabled = disabled;
            return button;
        }

        function row(link, index) {
            const item = document.createElement('fieldset');
            item.className = 'orgsuite-external-row';
            item.dataset.linkId = link.id;
            const legend = document.createElement('legend');
            legend.textContent = `Menülink ${index + 1}`;
            const suite = document.createElement('select');
            suite.name = 'suite';
            for (const [value, text] of [['ad', 'AD'], ['br', 'BR']]) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = text;
                option.selected = link.suite === value;
                suite.append(option);
            }
            const active = input('active', '', {type: 'checkbox', checked: link.active === true});
            const activeLabel = fieldLabel('Aktiv', active);
            activeLabel.className = 'orgsuite-external-active';
            const actions = document.createElement('div');
            actions.className = 'orgsuite-external-actions';
            actions.append(
                actionButton('move-up', 'Nach oben', index === 0),
                actionButton('move-down', 'Nach unten', index === links.length - 1),
                actionButton('remove-link', 'Entfernen'),
            );
            item.append(
                legend,
                fieldLabel('Suite', suite),
                fieldLabel('Bezeichnung', input('label', link.label, {type: 'text', required: true, maxLength: 80})),
                fieldLabel('HTTPS-URL', input('url', link.url, {type: 'url', required: true, maxLength: 2048, pattern: 'https://.*'})),
                activeLabel,
                actions,
            );
            return item;
        }

        function render(focusId = '') {
            list.replaceChildren(...links.map(row));
            if (focusId) list.querySelector(`[data-link-id="${CSS.escape(focusId)}"] input[name="label"]`)?.focus();
        }

        function collect() {
            return [...list.querySelectorAll('[data-link-id]')].map(item => ({
                id: item.dataset.linkId,
                suite: item.querySelector('[name="suite"]').value,
                label: item.querySelector('[name="label"]').value.trim(),
                url: item.querySelector('[name="url"]').value.trim(),
                active: item.querySelector('[name="active"]').checked,
            }));
        }

        async function request(options = {}) {
            const response = await fetch(endpoint(), {
                credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'requesttoken': window.OC.requestToken},
                ...options,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.error || `HTTP ${response.status}`);
            return data;
        }

        toolbar.addEventListener('click', event => {
            const button = event.target.closest('button[data-action="add-link"]');
            if (!button) return;
            const draft = createDraft(button.dataset.suite);
            links.push(draft);
            render(draft.id);
        });

        list.addEventListener('click', event => {
            const button = event.target.closest('button[data-action]');
            const item = button?.closest('[data-link-id]');
            if (!button || !item) return;
            const index = links.findIndex(link => link.id === item.dataset.linkId);
            if (index < 0) return;
            const action = button.dataset.action;
            if (action === 'remove-link') links.splice(index, 1);
            if (action === 'move-up' && index > 0) [links[index - 1], links[index]] = [links[index], links[index - 1]];
            if (action === 'move-down' && index < links.length - 1) [links[index + 1], links[index]] = [links[index], links[index + 1]];
            render();
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            save.disabled = true;
            try {
                const data = await request({method: 'POST', body: JSON.stringify({links: collect()})});
                links = Array.isArray(data.links) ? data.links : [];
                render();
                notice.textContent = 'Externe Links gespeichert.';
            } catch (error) {
                notice.textContent = error instanceof Error ? error.message : 'Externe Links konnten nicht gespeichert werden.';
            } finally {
                save.disabled = false;
            }
        });

        request().then(data => {
            links = Array.isArray(data.links) ? data.links : [];
            render();
        }).catch(error => {
            notice.textContent = error instanceof Error ? error.message : 'Externe Links konnten nicht geladen werden.';
            save.disabled = true;
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
})();
