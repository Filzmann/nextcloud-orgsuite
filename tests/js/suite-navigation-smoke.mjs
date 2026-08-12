import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import vm from 'node:vm';

const source = readFileSync(new URL('../../js/suite-navigation.js', import.meta.url), 'utf8');
const css = readFileSync(new URL('../../css/suite-navigation.css', import.meta.url), 'utf8');
const sourceFilename = fileURLToPath(new URL('../../js/suite-navigation.js', import.meta.url));

for (const contract of [
    "loadInitialState", "window.t",
    "aria-current", "document.createElement('nav')", "window.OC.generateUrl",
    "document.querySelectorAll('[data-orgsuite]')",
]) {
    if (!source.includes(contract)) {
        throw new Error(`Suite-Menuevertrag fehlt: ${contract}`);
    }
}
for (const contract of ['focus-visible', 'flex-wrap: wrap', 'var(--color-border)', 'position: sticky', 'top: 0', 'z-index: 20', 'background: var(--color-main-background)']) {
    if (!css.includes(contract)) {
        throw new Error(`Suite-CSS-Vertrag fehlt: ${contract}`);
    }
}

class FakeElement {
    constructor(tagName) {
        this.tagName = tagName.toUpperCase();
        this.children = [];
        this.dataset = {};
        this.attributes = {};
        this.className = '';
        this.classList = {add: (...names) => { this.className += ` ${names.join(' ')}`; }};
    }
    setAttribute(name, value) { this.attributes[name] = value; }
    append(...children) { this.children.push(...children); }
    replaceChildren(...children) { this.children = children; }
}

const host = new FakeElement('div');
host.dataset.suite = 'ad';
host.dataset.currentApp = 'adplaner';
const document = {
    readyState: 'complete',
    createElement: (tagName) => new FakeElement(tagName),
    querySelectorAll: (selector) => selector === '[data-orgsuite]' ? [host] : [],
    querySelector: (selector) => selector === '#initial-state-orgsuite-suite-navigation'
        ? {value: Buffer.from(JSON.stringify(navigation)).toString('base64')}
        : null,
    addEventListener: () => {},
};
const navigation = {
    ad: {
        label: 'AD-Anwendungen',
        items: [
            {app: 'adplaner', label: 'Assistenzplanung', href: '/route/adplaner.page.index'},
            {app: 'adrecruitment', label: 'Recruitment'},
        ],
    },
};
const window = {
    OC: {generateUrl: (path, params) => path.replace('{appId}', params.appId)},
    atob: (value) => Buffer.from(value, 'base64').toString('binary'),
    t: (app, label) => `${app}:${label}`,
};
vm.runInNewContext(source, {document, window, Object}, {filename: sourceFilename});

const nav = host.children[0];
const links = nav.children[0].children.map((item) => item.children[0]);
if (nav.tagName !== 'NAV' || nav.attributes['aria-label'] !== 'orgsuite:AD-Anwendungen') {
    throw new Error('Das Suite-Menue muss als beschriftete Navigation gerendert werden.');
}
if (links.length !== 2 || links[0].attributes['aria-current'] !== 'page'
    || links[0].href !== '/route/adplaner.page.index' || links[1].href !== '/apps/adrecruitment/'
    || links[1].textContent !== 'adrecruitment:Recruitment') {
    throw new Error('Aktiver AD-Menuepunkt wurde nicht korrekt gerendert.');
}
if (host.dataset.orgsuiteMounted !== 'true') {
    throw new Error('Mehrfaches Mounten des Suite-Menues wird nicht verhindert.');
}
window.OrgSuiteNavigation.mount(host);

const fallbackHost = new FakeElement('div');
fallbackHost.dataset.suite = 'ad';
const fallbackDocument = {
    readyState: 'complete',
    createElement: (tagName) => new FakeElement(tagName),
    querySelectorAll: () => [fallbackHost],
    querySelector: () => null,
    addEventListener: () => {},
};
const fallbackWindow = {OC: window.OC};
vm.runInNewContext(source, {document: fallbackDocument, window: fallbackWindow, Object}, {filename: sourceFilename});
if (Object.keys(fallbackWindow.OrgSuiteNavigation.suites).length !== 0 || fallbackHost.children.length !== 0) {
    throw new Error('Fehlender Initialzustand erweitert das Suite-Menü unerwartet.');
}

const malformedWindow = {OC: window.OC, atob: window.atob};
const malformedDocument = {...fallbackDocument, querySelector: () => ({value: 'kein-json'}), querySelectorAll: () => []};
vm.runInNewContext(source, {document: malformedDocument, window: malformedWindow, Object}, {filename: sourceFilename});
if (Object.keys(malformedWindow.OrgSuiteNavigation.suites).length !== 0) {
    throw new Error('Ungültiger Initialzustand wird nicht sicher verworfen.');
}

console.log('OrgSuite JavaScript smoke test passed');
