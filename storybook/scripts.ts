// Globally add web components polyfills.
import '@babel/polyfill';
import '@webcomponents/webcomponentsjs/webcomponents-bundle';

import './styles.css';

const components = require.context('./twig', true, /\/index\.(ts|js)$/);

components.keys().forEach(filename => components(filename));
