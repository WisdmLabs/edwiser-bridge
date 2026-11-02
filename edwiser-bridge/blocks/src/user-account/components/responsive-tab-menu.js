import React from 'react';
import { Menu, Burger } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { Icons } from './icons';
import { __ } from '@wordpress/i18n';

function ResponsiveTabMenu({ activeTab, onTabChange }) {
  const [opened, { toggle, close }] = useDisclosure();

  const handleTabClick = (tabValue) => {
    onTabChange(tabValue);
    close();
  };

  return (
    <Menu
      shadow="md"
      offset={30}
      withinPortal={false}
      closeOnClickOutside={false}
      opened={opened}
      onClose={close}
    >
      <Menu.Target>
        <Burger
          lineSize={2}
          size="md"
          opened={opened}
          onClick={toggle}
          aria-label="Toggle navigation"
        />
      </Menu.Target>

      <Menu.Dropdown>
        <Menu.Item
          leftSection={<Icons.layout />}
          onClick={() => handleTabClick('dashboard')}
          active={activeTab === 'dashboard'}
          data-active={activeTab === 'dashboard' ? true : false}
        >
          {__('Dashboard', 'edwiser-bridge')}
        </Menu.Item>
        <Menu.Item
          leftSection={<Icons.user />}
          onClick={() => handleTabClick('profile')}
          active={activeTab === 'profile'}
          data-active={activeTab === 'profile' ? true : false}
        >
          {__('Profile', 'edwiser-bridge')}
        </Menu.Item>
        <Menu.Item
          leftSection={<Icons.orders />}
          onClick={() => handleTabClick('orders')}
          active={activeTab === 'orders'}
          data-active={activeTab === 'orders' ? true : false}
        >
          {__('Orders', 'edwiser-bridge')}
        </Menu.Item>
        <Menu.Item
          leftSection={<Icons.book />}
          onClick={() => handleTabClick('my-courses')}
          active={activeTab === 'my-courses'}
          data-active={activeTab === 'my-courses' ? true : false}
        >
          {__('My Courses', 'edwiser-bridge')}
        </Menu.Item>
      </Menu.Dropdown>
    </Menu>
  );
}

export default ResponsiveTabMenu;
