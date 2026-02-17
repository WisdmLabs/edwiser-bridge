import React from 'react';
import ReactDOM from 'react-dom/client';
import Orders from './orders';

document.addEventListener('DOMContentLoaded', function () {
  const elems = document.querySelectorAll('[id^="eb-orders-"]');

  elems.forEach((elem) => {
    if (elem) {
      const attributes = {
        pageTitle: elem.dataset.pageTitle || '',
        hidePageTitle: elem.dataset.hidePageTitle === 'true',
        enableEdwiserOrders: elem.dataset.enableEdwiserOrders === 'true',
        enableWooCommerceOrders:
          elem.dataset.enableWooCommerceOrders === 'true',
        defaultTab: elem.dataset.defaultTab || 'eb-orders',
      };

      const root = ReactDOM.createRoot(elem);
      root.render(<Orders {...attributes} />);
    }
  });
});
