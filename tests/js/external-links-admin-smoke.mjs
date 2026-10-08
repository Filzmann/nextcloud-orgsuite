import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const sourceUrl = new URL('../../js/external-links-admin.js', import.meta.url);
const source = readFileSync(sourceUrl, 'utf8');
const css = readFileSync(new URL('../../css/external-links-admin.css', import.meta.url), 'utf8');

for (const contract of [
    "document.createElement('section')", 'Externe Menülinks', "document.createElement('form')",
    "document.createElement('label')", "document.createElement('input')", "document.createElement('select')",
    "document.createElement('button')", "aria-live", "OC.generateUrl('/apps/orgsuite/api/admin/external-links')",
    "'requesttoken': window.OC.requestToken", "method: 'POST'", 'move-up', 'move-down', 'remove-link',
]) {
    if (!source.includes(contract)) throw new Error(`Externe-Link-Adminvertrag fehlt: ${contract}`);
}
if (source.includes('.innerHTML')) throw new Error('Benutzerdefinierte externe Links dürfen nicht per innerHTML gerendert werden.');
for (const contract of ['focus-visible', 'grid-template-columns', '@media']) {
    if (!css.includes(contract)) throw new Error(`Externe-Link-Admin-CSS-Vertrag fehlt: ${contract}`);
}

class FakeElement {
    constructor(tagName) {
        this.tagName = tagName;
        this.children = [];
        this.dataset = {};
        this.listeners = new Map();
        this.attributes = new Map();
        this.parent = null;
    }

    append(...children) {
        for (const child of children) {
            child.parent = this;
            this.children.push(child);
        }
    }

    replaceChildren(...children) {
        this.children = [];
        this.append(...children);
    }

    setAttribute(name, value) {
        this.attributes.set(name, value);
    }

    addEventListener(type, listener) {
        this.listeners.set(type, listener);
    }

    async trigger(type, target = this) {
        return this.listeners.get(type)?.({target, preventDefault: () => {}});
    }

    matches(selector) {
        if (selector === '[data-link-id]') return this.dataset.linkId !== undefined;
        if (selector === 'button[data-action]') return this.tagName === 'button' && this.dataset.action !== undefined;
        if (selector === 'button[data-action="add-link"]') return this.tagName === 'button' && this.dataset.action === 'add-link';
        const name = selector.match(/^(?:input)?\[name="([^"]+)"\]$/)?.[1];
        return name !== undefined && this.name === name && (!selector.startsWith('input') || this.tagName === 'input');
    }

    closest(selector) {
        for (let current = this; current; current = current.parent) {
            if (current.matches(selector)) return current;
        }
        return null;
    }

    descendants() {
        return this.children.flatMap(child => [child, ...child.descendants()]);
    }

    querySelectorAll(selector) {
        return this.descendants().filter(element => element.matches(selector));
    }

    querySelector(selector) {
        const focusedRow = selector.match(/^\[data-link-id="([^"]+)"\] input\[name="label"\]$/)?.[1];
        if (focusedRow !== undefined) {
            return this.querySelectorAll('[data-link-id]').find(item => item.dataset.linkId === focusedRow)?.querySelector('input[name="label"]') ?? null;
        }
        return this.querySelectorAll(selector)[0] ?? null;
    }

    focus() {
        this.focused = true;
    }
}

const tick = () => new Promise(resolve => setImmediate(resolve));
const response = (data, ok = true, status = 200) => ({ok, status, json: async () => data});
const createRuntime = ({initialResponse = response({links: []}), readyState = 'complete'} = {}) => {
    const root = new FakeElement('main');
    const documentListeners = new Map();
    const document = {
        readyState,
        createElement: tagName => new FakeElement(tagName),
        getElementById: id => id === 'orgsuite-admin' ? root : null,
        addEventListener: (type, listener) => documentListeners.set(type, listener),
    };
    const window = {
        OC: {generateUrl: path => path, requestToken: 'token'},
        crypto: {randomUUID: () => '12345678-1234-1234-1234-123456789abc'},
    };
    const responses = [initialResponse];
    const requests = [];
    const fetch = async (url, options) => {
        requests.push({url, options});
        const next = responses.shift();
        if (next instanceof Error || typeof next === 'string') throw next;
        return next;
    };
    const context = {window, document, console, fetch, CSS: {escape: value => value}};
    vm.runInNewContext(source, context, {filename: sourceUrl.pathname});
    return {root, window, documentListeners, responses, requests};
};

const emptyWindow = {
    OC: {generateUrl: path => path, requestToken: 'token'},
    crypto: {randomUUID: () => '12345678-1234-1234-1234-123456789abc'},
};
vm.runInNewContext(source, {
    window: emptyWindow,
    document: {readyState: 'complete', getElementById: () => null, addEventListener: () => {}},
    console,
    fetch: async () => {},
}, {filename: sourceUrl.pathname});
const draft = emptyWindow.OrgSuiteExternalLinksAdmin.createDraft('br');
if (draft.id !== '12345678-1234-1234-1234-123456789abc' || draft.suite !== 'br' || draft.active !== true) {
    throw new Error('Ein neuer externer Link erhält keinen stabilen sicheren Ausgangszustand.');
}

const runtime = createRuntime({initialResponse: response({links: [
    {id: 'flz-docs', suite: 'flz', label: 'FLZ', url: 'https://flz.example.test', active: true},
    {id: 'br-docs', suite: 'br', label: 'BR', url: 'https://br.example.test', active: false},
]})});
await tick();
const section = runtime.root.children[0];
const form = section.children[3];
const [toolbar, list, save] = form.children;
const notice = section.children[2];
if (list.querySelectorAll('[data-link-id]').length !== 2) throw new Error('Geladene externe Links werden nicht gerendert.');

await toolbar.trigger('click', new FakeElement('span'));
await toolbar.trigger('click', toolbar.children[0]);
if (list.querySelectorAll('[data-link-id]').length !== 3 || !list.querySelector('input[name="label"]')) {
    throw new Error('Ein neuer externer Link wird nicht fokussierbar ergänzt.');
}

await list.trigger('click', new FakeElement('span'));
const missingItem = new FakeElement('fieldset');
missingItem.dataset.linkId = 'missing';
const missingButton = new FakeElement('button');
missingButton.dataset.action = 'remove-link';
missingItem.append(missingButton);
await list.trigger('click', missingButton);

const action = (row, name) => row.descendants().find(element => element.dataset.action === name);
await list.trigger('click', action(list.querySelectorAll('[data-link-id]')[0], 'move-down'));
await list.trigger('click', action(list.querySelectorAll('[data-link-id]')[1], 'move-up'));
await list.trigger('click', action(list.querySelectorAll('[data-link-id]')[2], 'remove-link'));
if (list.querySelectorAll('[data-link-id]').length !== 2) throw new Error('Externe Links werden nicht zuverlässig umgeordnet und entfernt.');

runtime.responses.push(response({links: [{id: 'saved', suite: 'flz', label: 'Gespeichert', url: 'https://saved.example.test', active: true}]}));
await form.trigger('submit');
const savedRequest = runtime.requests.at(-1);
if (savedRequest.options.method !== 'POST' || savedRequest.options.headers.requesttoken !== 'token'
    || JSON.parse(savedRequest.options.body).links.length !== 2 || notice.textContent !== 'Externe Links gespeichert.' || save.disabled) {
    throw new Error('Externe Links werden nicht vollständig und CSRF-geschützt gespeichert.');
}

runtime.responses.push({ok: false, status: 503, json: async () => { throw new Error('defekte Antwort'); }});
await form.trigger('submit');
if (notice.textContent !== 'HTTP 503' || save.disabled) throw new Error('Speicherfehler werden nicht verständlich und bedienbar angezeigt.');

const failedRuntime = createRuntime({initialResponse: 'offline'});
await tick();
const failedSection = failedRuntime.root.children[0];
if (failedSection.children[2].textContent !== 'Externe Links konnten nicht geladen werden.' || !failedSection.children[3].children[2].disabled) {
    throw new Error('Ladefehler hinterlassen keinen sicheren deaktivierten Zustand.');
}

const loadingRuntime = createRuntime({readyState: 'loading'});
loadingRuntime.documentListeners.get('DOMContentLoaded')();
await tick();
if (loadingRuntime.root.children.length !== 1) throw new Error('Die Administration initialisiert nicht nach DOMContentLoaded.');

console.log('OrgSuite external links admin JavaScript smoke passed');
