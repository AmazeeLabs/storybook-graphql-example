import { storiesOf } from '@storybook/html';
import Menu from './menu.html.twig';

const menuData = {
  links: [
    {
      url: '#',
      label: 'Home',
      links: [],
    },
    {
      url: '#',
      label: 'About',
      links: [],
    },
    {
      url: '#',
      label: 'Services',
      links: [
        {
          url: '#',
          label: 'Web Development',
          links: [],
        },
        {
          url: '#',
          label: 'Mobile Apps',
          links: [],
        },
        {
          url: '#',
          label: 'Design',
          links: [
            {
              url: '#',
              label: 'Web Design',
            },
            {
              url: '#',
              label: 'Graphic Design',
            },
            {
              url: '#',
              label: 'Logo Design',
            },
          ],
        },
      ],
    },
    {
      url: '#',
      label: 'Gallery',
      links: [],
    },
    {
      url: '#',
      label: 'Contact',
      links: [],
    },
  ],
};

storiesOf('Menu', module)
  .add('Dropdown Menu', () => Menu(menuData));
