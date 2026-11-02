import React, { useState, useEffect } from 'react';
import { Skeleton, MantineProvider, Tabs } from '@mantine/core';
import EbOrders from './components/eb-orders';
import WcOrders from './components/wc-orders';
import { __ } from '@wordpress/i18n';

function Orders({
  pageTitle,
  hidePageTitle,
  enableEdwiserOrders,
  enableWooCommerceOrders,
  defaultTab,
}) {
  const [activeTab, setActiveTab] = useState(defaultTab || 'eb-orders');

  // Handle URL parameter for tab switching
  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('order-type');

    const availableTabs = [];
    if (enableEdwiserOrders) availableTabs.push('eb-orders');
    if (enableWooCommerceOrders) availableTabs.push('wc-orders');

    if (tabParam && availableTabs.includes(tabParam)) {
      setActiveTab(tabParam);
    } else if (availableTabs.length > 0 && !tabParam) {
      setActiveTab(availableTabs[0]);
    }
  }, [enableEdwiserOrders, enableWooCommerceOrders]);

  // Update URL when tab changes
  const handleTabChange = (value) => {
    setActiveTab(value);

    const url = new URL(window.location);
    url.searchParams.set('order-type', value);
    window.history.replaceState({}, '', url);
  };

  return (
    <MantineProvider>
      <div className="eb-user-account__orders">
        {!hidePageTitle && <h3 className="eb-orders__title">{pageTitle}</h3>}

        {enableEdwiserOrders && enableWooCommerceOrders && (
          <Tabs
            value={activeTab}
            onChange={handleTabChange}
            style={{ marginTop: '3em' }}
          >
            <Tabs.List>
              <Tabs.Tab value="eb-orders">
                {__('Edwiser Orders', 'edwiser-bridge')}
              </Tabs.Tab>
              <Tabs.Tab value="wc-orders">
                {__('WooCommerce Orders', 'edwiser-bridge')}
              </Tabs.Tab>
            </Tabs.List>
            <Tabs.Panel value="eb-orders">
              <EbOrders />
            </Tabs.Panel>
            <Tabs.Panel value="wc-orders">
              <WcOrders />
            </Tabs.Panel>
          </Tabs>
        )}
        {enableEdwiserOrders && !enableWooCommerceOrders && (
          <div style={{ marginTop: '3em' }}>
            <EbOrders />
          </div>
        )}
        {enableWooCommerceOrders && !enableEdwiserOrders && (
          <div style={{ marginTop: '3em' }}>
            <WcOrders />
          </div>
        )}
      </div>
    </MantineProvider>
  );
}

export default Orders;

export const OrdersSkeleton = ({ pageTitle, hidePageTitle }) => {
  return (
    <div className="eb-user-account__orders">
      {!hidePageTitle && <h3 className="eb-orders__title">{pageTitle}</h3>}
      <div className="eb-orders__filter">
        <Skeleton width={160} height={36} />
        <Skeleton width={280} height={36} />
      </div>
      <div className="eb-orders__table-wrapper">
        <Skeleton width="100%" height={480} />
      </div>
      <div className="eb-orders__pagination">
        <Skeleton width={180} height={20} />
        <Skeleton width={240} height={32} />
      </div>
    </div>
  );
};
