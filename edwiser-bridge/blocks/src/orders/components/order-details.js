import { Skeleton } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Icons } from './icons';

const OrderDetails = ({ order, onClose, isLoading = false }) => {
  if (!order) return null;

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  return (
    <div className="eb-order-details">
      {isLoading ? (
        <>
          <Skeleton height={40} width={120} />
          <div
            className="eb-order-details__content"
            style={{ marginTop: '24px' }}
          >
            <Skeleton height={450} width={'100%'} />
          </div>
        </>
      ) : order && order.detailsHtml ? (
        <>
          <div className="eb-order-details__header">
            <button
              className="eb-order-details__back-button"
              onClick={onClose}
              aria-label={__('Back to orders', 'edwiser-bridge')}
            >
              <Icons.arrowLeft /> {__('Back to Orders', 'edwiser-bridge')}
            </button>
            <div className="eb-order-details__meta">
              {__('Order', 'edwiser-bridge')} <strong>#{order.id}</strong>{' '}
              {__('was placed on', 'edwiser-bridge')}{' '}
              <strong>{formatDate(order.date_created)}</strong>{' '}
              {__('and is currently', 'edwiser-bridge')}{' '}
              <strong style={{ textTransform: 'capitalize' }}>
                {__(order.status, 'edwiser-bridge')}
              </strong>
            </div>
          </div>
          <div
            className="eb-order-details__woocommerce-content"
            dangerouslySetInnerHTML={{ __html: order.detailsHtml }}
          />
        </>
      ) : (
        <>
          <div className="eb-order-details__content">
            <div className="eb-order-details__error">
              <h4>{__('Failed to load order details', 'edwiser-bridge')}</h4>
              <p>
                {__(
                  'There was an error loading the order details.',
                  'edwiser-bridge'
                )}
              </p>
              <button
                className="eb-order-details__back-button"
                onClick={onClose}
                aria-label={__('Back to orders', 'edwiser-bridge')}
              >
                <Icons.arrowLeft /> {__('Back to Orders', 'edwiser-bridge')}
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
};

export default OrderDetails;
