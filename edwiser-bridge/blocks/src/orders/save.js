import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes, clientId }) {
  return (
    <div {...useBlockProps.save()}>
      <div
        id={`eb-orders-${clientId}`}
        data-page-title={attributes.pageTitle || ''}
        data-hide-page-title={attributes.hidePageTitle}
        data-enable-edwiser-orders={attributes.enableEdwiserOrders}
        data-enable-woo-commerce-orders={attributes.enableWooCommerceOrders}
        data-default-tab={attributes.defaultTab}
      ></div>
    </div>
  );
}
