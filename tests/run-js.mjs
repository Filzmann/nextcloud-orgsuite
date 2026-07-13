import {execFileSync} from 'node:child_process';

execFileSync('node', ['--check', new URL('../js/suite-navigation.js', import.meta.url).pathname], {stdio: 'inherit'});
execFileSync('node', [new URL('./js/suite-navigation-smoke.mjs', import.meta.url).pathname], {stdio: 'inherit'});
console.log('OrgSuite JavaScript tests passed');

