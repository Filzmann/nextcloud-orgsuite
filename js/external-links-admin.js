(function() {
    'use strict';

    const endpoint = () => window.OC.generateUrl('/apps/orgsuite/api/admin/external-links');
    const externalKey = id => `external:${id}`;
    const createDraft = suite => ({
        id: window.crypto.randomUUID().toLowerCase(),
        suite,
        label: '',
        url: 'https://',
        active: true,
        openMode: 'new',
    });

    window.OrgSuiteExternalLinksAdmin = Object.freeze({createDraft});

    function init() {
        const root = document.getElementById('orgsuite-admin');
        if (!root) return;

        let links = [];
        let targets = [];
        let order = {flz: [], br: []};
        let draggedKey = '';
        let draggedSuite = '';

        const section = document.createElement('section');
        section.className = 'orgsuite-external-admin';
        section.setAttribute('aria-labelledby', 'orgsuite-external-heading');
        const heading = document.createElement('h2');
        heading.id = 'orgsuite-external-heading';
        heading.textContent = 'Menüreihenfolge und externe Links';
        const description = document.createElement('p');
        description.textContent = 'Interne Produkte und zusätzliche HTTPS-Links lassen sich gemeinsam per Ziehen oder mit Alt+Pfeiltasten sortieren. Externe Links öffnen standardmäßig in einem neuen Tab und erteilen keine Rechte im Zielsystem.';
        const notice = document.createElement('p');
        notice.className = 'orgsuite-external-notice';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');
        const form = document.createElement('form');
        const toolbar = document.createElement('div');
        toolbar.className = 'orgsuite-external-toolbar';
        toolbar.append(addButton('flz', 'Filzmann-Link hinzufügen'), addButton('br', 'BR-Link hinzufügen'));
        const list = document.createElement('div');
        list.className = 'orgsuite-external-list';
        const save = document.createElement('button');
        save.type = 'submit';
        save.className = 'primary';
        save.textContent = 'Menüreihenfolge und externe Links speichern';
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

        function select(name, value, choices) {
            const control = document.createElement('select');
            control.name = name;
            control.value = value;
            for (const [optionValue, text] of choices) {
                const option = document.createElement('option');
                option.value = optionValue;
                option.textContent = text;
                option.selected = optionValue === value;
                control.append(option);
            }
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

        function itemForKey(key) {
            if (key.startsWith('external:')) {
                const link = links.find(candidate => externalKey(candidate.id) === key);
                return link ? {key, suite: link.suite, label: link.label, kind: 'external', link} : null;
            }
            return targets.find(target => target.key === key) ?? null;
        }

        function row(key, suite, index, count) {
            const item = itemForKey(key);
            if (!item) return null;
            const fieldset = document.createElement('fieldset');
            fieldset.className = `orgsuite-external-row is-${item.kind}`;
            fieldset.dataset.navKey = key;
            fieldset.dataset.suite = suite;
            fieldset.draggable = true;
            fieldset.tabIndex = 0;
            fieldset.setAttribute('aria-label', `${item.label || 'Unbenannter Link'} verschieben`);
            if (item.kind === 'external') fieldset.dataset.linkId = item.link.id;

            const legend = document.createElement('legend');
            legend.textContent = item.kind === 'internal' ? item.label : `Externer Menülink ${index + 1}`;
            const actions = document.createElement('div');
            actions.className = 'orgsuite-external-actions';
            actions.append(
                actionButton('move-up', 'Nach oben', index === 0),
                actionButton('move-down', 'Nach unten', index === count - 1),
            );

            if (item.kind === 'internal') {
                const type = document.createElement('p');
                type.className = 'orgsuite-external-kind';
                type.textContent = `Internes Produkt (${item.app})`;
                fieldset.append(legend, type, actions);
                return fieldset;
            }

            actions.append(actionButton('remove-link', 'Entfernen'));
            const suiteControl = select('suite', item.link.suite, [['flz', 'Filzmann'], ['br', 'BR']]);
            const active = input('active', '', {type: 'checkbox', checked: item.link.active === true});
            const activeLabel = fieldLabel('Aktiv', active);
            activeLabel.className = 'orgsuite-external-active';
            fieldset.append(
                legend,
                fieldLabel('Suite', suiteControl),
                fieldLabel('Bezeichnung', input('label', item.link.label, {type: 'text', required: true, maxLength: 80})),
                fieldLabel('HTTPS-URL', input('url', item.link.url, {type: 'url', required: true, maxLength: 2048, pattern: 'https://.*'})),
                fieldLabel('Öffnen', select('openMode', item.link.openMode || 'new', [['new', 'Neuer Tab'], ['same', 'Gleicher Tab']])),
                activeLabel,
                actions,
            );
            return fieldset;
        }

        function render(focusKey = '', focusLabel = false) {
            const content = [];
            for (const [suite, title] of [['flz', 'Filzmann'], ['br', 'BR']]) {
                const suiteHeading = document.createElement('h3');
                suiteHeading.textContent = `${title}-Menü`;
                const suiteList = document.createElement('div');
                suiteList.className = 'orgsuite-external-suite-list';
                suiteList.dataset.suiteList = suite;
                const rows = order[suite].map((key, index) => row(key, suite, index, order[suite].length)).filter(Boolean);
                suiteList.replaceChildren(...rows);
                content.push(suiteHeading, suiteList);
            }
            list.replaceChildren(...content);
            if (focusKey) {
                const focusedRow = list.querySelector(`[data-nav-key="${CSS.escape(focusKey)}"]`);
                if (focusLabel) focusedRow?.querySelector('[name="label"]')?.focus();
                else focusedRow?.focus();
            }
        }

        function applyState(data) {
            links = Array.isArray(data.links) ? data.links : [];
            targets = Array.isArray(data.targets) ? data.targets : [];
            const available = {
                flz: [...targets.filter(target => target.suite === 'flz').map(target => target.key), ...links.filter(link => link.suite === 'flz').map(link => externalKey(link.id))],
                br: [...targets.filter(target => target.suite === 'br').map(target => target.key), ...links.filter(link => link.suite === 'br').map(link => externalKey(link.id))],
            };
            order = {flz: [], br: []};
            for (const suite of ['flz', 'br']) {
                const configured = Array.isArray(data.order?.[suite]) ? data.order[suite] : [];
                order[suite] = configured.filter((key, index) => available[suite].includes(key) && configured.indexOf(key) === index);
                for (const key of available[suite]) if (!order[suite].includes(key)) order[suite].push(key);
            }
        }

        function syncLinksFromDom() {
            for (const item of list.querySelectorAll('[data-link-id]')) {
                const link = links.find(candidate => candidate.id === item.dataset.linkId);
                if (!link) continue;
                link.label = item.querySelector('[name="label"]').value.trim();
                link.url = item.querySelector('[name="url"]').value.trim();
                link.active = item.querySelector('[name="active"]').checked;
                link.openMode = item.querySelector('[name="openMode"]').value;
            }
        }

        function moveBy(key, suite, delta) {
            const index = order[suite].indexOf(key);
            const target = index + delta;
            if (index < 0 || target < 0 || target >= order[suite].length) return false;
            [order[suite][index], order[suite][target]] = [order[suite][target], order[suite][index]];
            return true;
        }

        function moveBefore(key, suite, targetKey) {
            if (key === targetKey) return false;
            const sourceIndex = order[suite].indexOf(key);
            if (sourceIndex < 0) return false;
            order[suite].splice(sourceIndex, 1);
            const targetIndex = order[suite].indexOf(targetKey);
            order[suite].splice(targetIndex < 0 ? order[suite].length : targetIndex, 0, key);
            return true;
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
            syncLinksFromDom();
            const draft = createDraft(button.dataset.suite);
            links.push(draft);
            order[draft.suite].push(externalKey(draft.id));
            render(externalKey(draft.id), true);
        });

        list.addEventListener('click', event => {
            const button = event.target.closest('button[data-action]');
            const item = button?.closest('[data-nav-key]');
            if (!button || !item) return;
            syncLinksFromDom();
            const key = item.dataset.navKey;
            const suite = item.dataset.suite;
            const action = button.dataset.action;
            if (action === 'remove-link' && key.startsWith('external:')) {
                links = links.filter(link => externalKey(link.id) !== key);
                order[suite] = order[suite].filter(candidate => candidate !== key);
                render();
                return;
            }
            const moved = action === 'move-up' ? moveBy(key, suite, -1) : action === 'move-down' ? moveBy(key, suite, 1) : false;
            if (moved) render(key);
        });

        list.addEventListener('change', event => {
            if (event.target.name !== 'suite') return;
            syncLinksFromDom();
            const item = event.target.closest('[data-link-id]');
            const link = links.find(candidate => candidate.id === item?.dataset.linkId);
            const nextSuite = event.target.value;
            if (!link || !['flz', 'br'].includes(nextSuite) || link.suite === nextSuite) return;
            const key = externalKey(link.id);
            order[link.suite] = order[link.suite].filter(candidate => candidate !== key);
            link.suite = nextSuite;
            order[nextSuite].push(key);
            render(key);
        });

        list.addEventListener('keydown', event => {
            const item = event.target.closest('[data-nav-key]');
            if (!item || !event.altKey || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
            event.preventDefault();
            syncLinksFromDom();
            if (moveBy(item.dataset.navKey, item.dataset.suite, event.key === 'ArrowUp' ? -1 : 1)) render(item.dataset.navKey);
        });

        list.addEventListener('dragstart', event => {
            const item = event.target.closest('[data-nav-key]');
            if (!item) return;
            syncLinksFromDom();
            draggedKey = item.dataset.navKey;
            draggedSuite = item.dataset.suite;
            item.className += ' is-dragging';
            event.dataTransfer?.setData('text/plain', draggedKey);
        });
        list.addEventListener('dragover', event => {
            const item = event.target.closest('[data-nav-key]');
            if (item?.dataset.suite === draggedSuite) event.preventDefault();
        });
        list.addEventListener('drop', event => {
            const item = event.target.closest('[data-nav-key]');
            if (!item || item.dataset.suite !== draggedSuite) return;
            event.preventDefault();
            if (moveBefore(draggedKey, draggedSuite, item.dataset.navKey)) render(draggedKey);
            draggedKey = '';
            draggedSuite = '';
        });
        list.addEventListener('dragend', () => {
            if (draggedKey) render(draggedKey);
            draggedKey = '';
            draggedSuite = '';
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            save.disabled = true;
            syncLinksFromDom();
            try {
                const data = await request({method: 'POST', body: JSON.stringify({links, order})});
                applyState(data);
                render();
                notice.textContent = 'Menüreihenfolge und externe Links gespeichert.';
            } catch (error) {
                notice.textContent = error instanceof Error ? error.message : 'Menüreihenfolge und externe Links konnten nicht gespeichert werden.';
            } finally {
                save.disabled = false;
            }
        });

        request().then(data => {
            applyState(data);
            render();
        }).catch(error => {
            notice.textContent = error instanceof Error ? error.message : 'Menüreihenfolge und externe Links konnten nicht geladen werden.';
            save.disabled = true;
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
})();
