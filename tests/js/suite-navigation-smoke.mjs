import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../js/suite-navigation.js', import.meta.url), 'utf8');
const css = readFileSync(new URL('../../css/suite-navigation.css', import.meta.url), 'utf8');

for (const contract of [
    "adcalendar", "adplaner", "adurlaub", "adroom",
    "brtop", "brstunden", "br_permission_matrix",
    "aria-current", "document.createElement('nav')", "window.OC.generateUrl",
    "document.querySelectorAll('[data-orgsuite]')",
]) {
    if (!source.includes(contract)) {
        throw new Error(`Suite-Menuevertrag fehlt: ${contract}`);
    }
}
for (const contract of ['focus-visible', 'flex-wrap: wrap', 'var(--color-border)']) {
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
    addEventListener: () => {},
};
const window = {OC: {generateUrl: (path, params) => path.replace('{appId}', params.appId)}};
vm.runInNewContext(source, {document, window, Object});

const nav = host.children[0];
const links = nav.children[0].children.map((item) => item.children[0]);
if (nav.tagName !== 'NAV' || nav.attributes['aria-label'] !== 'AD-Anwendungen') {
    throw new Error('Das Suite-Menue muss als beschriftete Navigation gerendert werden.');
}
if (links.length !== 4 || links[1].attributes['aria-current'] !== 'page' || links[1].href !== '/apps/adplaner/') {
    throw new Error('Aktiver AD-Menuepunkt wurde nicht korrekt gerendert.');
}
if (host.dataset.orgsuiteMounted !== 'true') {
    throw new Error('Mehrfaches Mounten des Suite-Menues wird nicht verhindert.');
}

console.log('OrgSuite JavaScript smoke test passed');
