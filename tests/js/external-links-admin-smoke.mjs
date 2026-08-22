import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../js/external-links-admin.js', import.meta.url), 'utf8');
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

const window = {
    OC: {generateUrl: path => path, requestToken: 'token'},
    crypto: {randomUUID: () => '12345678-1234-1234-1234-123456789abc'},
};
const document = {
    readyState: 'complete',
    getElementById: () => null,
    addEventListener: () => {},
};
vm.runInNewContext(source, {window, document, console, fetch: async () => {}}, {filename: 'external-links-admin.js'});
const draft = window.OrgSuiteExternalLinksAdmin.createDraft('br');
if (draft.id !== '12345678-1234-1234-1234-123456789abc' || draft.suite !== 'br' || draft.active !== true) {
    throw new Error('Ein neuer externer Link erhält keinen stabilen sicheren Ausgangszustand.');
}

console.log('OrgSuite external links admin JavaScript smoke passed');
