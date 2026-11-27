import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const sourceUrl = new URL('../../js/suite-label-admin.js', import.meta.url);
const source = readFileSync(sourceUrl, 'utf8');

for (const contract of [
    "OC.generateUrl('/apps/orgsuite/api/admin/suite-label')", "document.createElement('section')",
    "document.createElement('form')", "document.createElement('label')", "document.createElement('input')",
    "document.createElement('button')", "'requesttoken': window.OC.requestToken", "method: 'POST'", 'aria-live',
]) {
    if (!source.includes(contract)) throw new Error(`Suite-Bezeichnungs-Adminvertrag fehlt: ${contract}`);
}
if (source.includes('.innerHTML')) throw new Error('Die Suite-Bezeichnung darf nicht per innerHTML ausgegeben werden.');

class FakeElement {
    constructor(tagName) {
        this.tagName = tagName;
        this.children = [];
        this.listeners = new Map();
        this.attributes = new Map();
    }
    append(...children) { this.children.push(...children); }
    setAttribute(name, value) { this.attributes.set(name, value); }
    addEventListener(type, listener) { this.listeners.set(type, listener); }
    async trigger(type) { return this.listeners.get(type)?.({preventDefault: () => {}}); }
}

const tick = () => new Promise(resolve => setImmediate(resolve));
const response = (data, ok = true, status = 200) => ({ok, status, json: async () => data});
const createRuntime = ({responses = [response({flzLabel: 'Filzmann'})], readyState = 'complete'} = {}) => {
    const root = new FakeElement('main');
    const documentListeners = new Map();
    const requests = [];
    const document = {
        readyState,
        createElement: tagName => new FakeElement(tagName),
        getElementById: id => id === 'orgsuite-admin' ? root : null,
        addEventListener: (type, listener) => documentListeners.set(type, listener),
    };
    const fetch = async (url, options) => {
        requests.push({url, options});
        const next = responses.shift();
        if (next instanceof Error || typeof next === 'string') throw next;
        return next;
    };
    vm.runInNewContext(source, {
        window: {OC: {generateUrl: path => path, requestToken: 'token'}}, document, fetch, console,
    }, {filename: sourceUrl.pathname});
    return {root, requests, responses, documentListeners};
};

vm.runInNewContext(source, {
    window: {OC: {generateUrl: path => path, requestToken: 'token'}},
    document: {readyState: 'complete', getElementById: () => null, addEventListener: () => {}},
    fetch: async () => {}, console,
}, {filename: sourceUrl.pathname});

const runtime = createRuntime();
await tick();
const section = runtime.root.children[0];
const notice = section.children[2];
const form = section.children[3];
const input = form.children[0].children[1];
const save = form.children[1];
if (input.value !== 'Filzmann' || input.maxLength !== 40 || save.disabled) {
    throw new Error('Die vorhandene Suite-Bezeichnung wird nicht als bedienbarer Ausgangszustand geladen.');
}

input.value = 'FLZ Demo';
runtime.responses.push(response({flzLabel: 'FLZ Demo'}));
await form.trigger('submit');
const saved = runtime.requests.at(-1);
if (saved.url !== '/apps/orgsuite/api/admin/suite-label' || saved.options.method !== 'POST'
    || saved.options.headers.requesttoken !== 'token' || JSON.parse(saved.options.body).flzLabel !== 'FLZ Demo'
    || notice.textContent !== 'Menübezeichnung gespeichert.' || save.disabled) {
    throw new Error('Die Suite-Bezeichnung wird nicht vollständig und CSRF-geschützt gespeichert.');
}

runtime.responses.push(response({error: 'Ungültig'}, false, 400));
await form.trigger('submit');
if (notice.textContent !== 'Ungültig' || save.disabled) throw new Error('Validierungsfehler bleiben nicht verständlich und bedienbar.');

const failed = createRuntime({responses: ['offline']});
await tick();
const failedSection = failed.root.children[0];
if (failedSection.children[2].textContent !== 'Menübezeichnung konnte nicht geladen werden.'
    || !failedSection.children[3].children[0].children[1].disabled || !failedSection.children[3].children[1].disabled) {
    throw new Error('Ladefehler hinterlassen keinen sicheren deaktivierten Zustand.');
}

const loading = createRuntime({readyState: 'loading'});
loading.documentListeners.get('DOMContentLoaded')();
await tick();
if (loading.root.children.length !== 1) throw new Error('Die Suite-Administration initialisiert nicht nach DOMContentLoaded.');

console.log('OrgSuite suite label admin JavaScript smoke passed');
