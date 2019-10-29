import { storiesOf } from '@storybook/html';

import Page from './page.html.twig';

import { menuData } from '../menu/menu.stories';

const pageData = {
  nav: menuData,
};

storiesOf('Page', module)
  .add('Page', () => Page({
    ...pageData,
    node: {
      gettype: 'news',
    },
  }));
