(function() {
    'use strict';

    const endpoint = () => window.OC.generateUrl('/apps/orgsuite/api/admin/suite-label');

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

    function init() {
        const root = document.getElementById('orgsuite-admin');
        if (!root) return;

        const section = document.createElement('section');
        section.className = 'orgsuite-label-admin';
        section.setAttribute('aria-labelledby', 'orgsuite-label-heading');
        const heading = document.createElement('h2');
        heading.id = 'orgsuite-label-heading';
        heading.textContent = 'Suite-Menü';
        const description = document.createElement('p');
        description.textContent = 'Diese Bezeichnung erscheint im Nextcloud-Appmenü und benennt das gemeinsame FLZ-Quermenü.';
        const notice = document.createElement('p');
        notice.className = 'orgsuite-label-notice';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');
        const form = document.createElement('form');
        const label = document.createElement('label');
        const caption = document.createElement('span');
        caption.textContent = 'FLZ-Menübezeichnung';
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'flzLabel';
        input.required = true;
        input.maxLength = 40;
        const save = document.createElement('button');
        save.type = 'submit';
        save.className = 'primary';
        save.textContent = 'Menübezeichnung speichern';
        label.append(caption, input);
        form.append(label, save);
        section.append(heading, description, notice, form);
        root.append(section);

        form.addEventListener('submit', async event => {
            event.preventDefault();
            save.disabled = true;
            try {
                const data = await request({method: 'POST', body: JSON.stringify({flzLabel: input.value.trim()})});
                input.value = typeof data.flzLabel === 'string' ? data.flzLabel : input.value;
                notice.textContent = 'Menübezeichnung gespeichert.';
            } catch (error) {
                notice.textContent = error instanceof Error ? error.message : 'Menübezeichnung konnte nicht gespeichert werden.';
            } finally {
                save.disabled = false;
            }
        });

        request().then(data => {
            input.value = typeof data.flzLabel === 'string' ? data.flzLabel : '';
        }).catch(() => {
            notice.textContent = 'Menübezeichnung konnte nicht geladen werden.';
            input.disabled = true;
            save.disabled = true;
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
})();
