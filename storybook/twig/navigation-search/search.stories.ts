import { storiesOf } from '@storybook/html';
import './index';

import Search from './search.html.twig';

storiesOf('Header /', module)
  .add('Search', () => Search());
