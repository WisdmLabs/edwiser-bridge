import { Pagination, Select, Skeleton, TextInput } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React, { useEffect, useState } from 'react';
import { useWcOrders } from '../hooks/use-wc-orders';
import { Icons } from './icons';
import OrderDetails from './order-details';

const useDebounce = (value, delay) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

function WcOrders() {
  const {
    orders,
    isLoading,
    totalOrders,
    totalPages,
    currentPage,
    perPage,
    setCurrentPage,
    setPerPage,
    search,
    setSearch,
    orderBy,
    setOrderBy,
    order,
    setOrder,
  } = useWcOrders();

  const [sortBy, setSortBy] = useState(orderBy);
  const [reverseSortDirection, setReverseSortDirection] = useState(
    order === 'desc'
  );
  const [localSearch, setLocalSearch] = useState(search);
  const [cancellationNotice, setCancellationNotice] = useState({
    type: '',
    text: '',
  });
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [showOrderDetails, setShowOrderDetails] = useState(false);
  const [isLoadingOrderDetails, setIsLoadingOrderDetails] = useState(false);

  const debouncedSearch = useDebounce(localSearch, 300);

  // Clear cancellation notice
  const clearCancellationNotice = () => {
    setCancellationNotice({ type: '', text: '' });
  };

  // Handle view order details - Load WooCommerce template via AJAX
  const handleViewOrder = async (order) => {
    try {
      setIsLoadingOrderDetails(true);
      setShowOrderDetails(true);
      setSelectedOrder(order);

      // Prepare form data for AJAX request
      const formData = new FormData();
      formData.append('action', 'eb_get_order_details');
      formData.append('order_id', order.id);
      formData.append('nonce', window.eb_order_details?.nonce || '');

      // Make AJAX request
      const response = await fetch(
        window.eb_order_details?.ajax_url || '/wp-admin/admin-ajax.php',
        {
          method: 'POST',
          body: formData,
        }
      );

      if (!response.ok) {
        throw new Error('Failed to fetch order details');
      }

      const data = await response.json();

      if (data.success && data.data?.html) {
        // Successfully loaded WooCommerce template
        setSelectedOrder({
          ...order,
          detailsHtml: data.data.html,
        });
      } else {
        // No HTML content received
        setSelectedOrder({
          ...order,
          detailsHtml: '',
        });
      }
    } catch (error) {
      console.error('Error loading order details:', error);
    } finally {
      setIsLoadingOrderDetails(false);
    }
  };

  // Handle close order details
  const handleCloseOrderDetails = () => {
    setShowOrderDetails(false);
    setSelectedOrder(null);
    setIsLoadingOrderDetails(false);
  };

  // Check for order cancellation notice
  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const cancelled = urlParams.get('cancelled');

    if (cancelled === 'true') {
      setCancellationNotice({
        type: 'success',
        text: __('Your order was cancelled.', 'edwiser-bridge'),
      });

      // Clear the URL parameter to prevent showing the notice again on refresh
      const newUrl = new URL(window.location);
      newUrl.searchParams.delete('cancelled');
      window.history.replaceState({}, '', newUrl);

      // Auto-hide the notice after 5 seconds
      const timer = setTimeout(() => {
        setCancellationNotice({ type: '', text: '' });
      }, 5000);

      return () => clearTimeout(timer);
    }
  }, []);

  useEffect(() => {
    if (debouncedSearch !== search) {
      setSearch(debouncedSearch);
    }
  }, [debouncedSearch, search, setSearch]);

  useEffect(() => {
    setLocalSearch(search);
  }, [search]);

  useEffect(() => {
    setSortBy(orderBy);
    setReverseSortDirection(order === 'desc');
  }, [orderBy, order]);

  const setSorting = (field) => {
    if (sortBy !== field) {
      setSortBy(field);
      setReverseSortDirection(false);
      setOrderBy(field);
      setOrder('asc');
    } else if (!reverseSortDirection) {
      setReverseSortDirection(true);
      setOrder('desc');
    } else {
      if (field === 'date') {
        setReverseSortDirection(false);
        setOrder('asc');
      } else {
        setSortBy('date');
        setReverseSortDirection(true);
        setOrderBy('date');
        setOrder('desc');
      }
    }
  };

  const getSortIcon = (field) => {
    const isActive = sortBy === field;
    const isDescending = reverseSortDirection;

    return (
      <div className="eb-orders__table-header-item-icon">
        <div
          style={{
            color: isActive && !isDescending ? '#008b91' : '#abbebe',
          }}
        >
          &#9650;
        </div>
        <div
          style={{
            color: isActive && isDescending ? '#008b91' : '#abbebe',
          }}
        >
          &#9660;
        </div>
      </div>
    );
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
    }).format(amount);
  };

  const formatOrderTotal = (order) => {
    const originalTotal = parseFloat(order.total) || 0;
    const refunds = order.refunds || [];
    const totalRefunded = refunds.reduce(
      (sum, refund) => sum + Math.abs(parseFloat(refund.total)),
      0
    );
    const netTotal = originalTotal - totalRefunded;
    const currency = order.currency;

    if (totalRefunded > 0) {
      return (
        <span className="eb-orders__total-with-refund">
          <span className="eb-orders__original-total">
            {formatCurrency(originalTotal, currency)}
          </span>{' '}
          <span className="eb-orders__net-total">
            {formatCurrency(netTotal, currency)}
          </span>
        </span>
      );
    }

    return <strong>{formatCurrency(originalTotal, currency)}</strong>;
  };

  const renderTableContent = () => {
    if (isLoading) {
      return (
        <div className="eb-orders__table-wrapper">
          <Skeleton width="100%" height={480} />
        </div>
      );
    }

    if (orders.length === 0) {
      return (
        <div className="eb-orders__empty-state">
          <h4 className="eb-orders__empty-state-title">
            {__('No orders found', 'edwiser-bridge')}
          </h4>
          <p className="eb-orders__empty-state-description">
            {localSearch ? (
              <>
                {__('No orders matching your search', 'edwiser-bridge')}{' '}
                {__('for', 'edwiser-bridge')} {localSearch}
              </>
            ) : (
              __(
                "You haven't placed any orders yet. Browse our products to get started!",
                'edwiser-bridge'
              )
            )}
          </p>
        </div>
      );
    }

    return (
      <div className="eb-orders__table-wrapper">
        <table className="eb-orders__table">
          <thead>
            <tr className="eb-orders__table-header">
              <th
                className="eb-orders__table-header-item eb-orders__table-header-item--order-id"
                onClick={() => setSorting('id')}
              >
                <div className="eb-orders__table-header-item-content">
                  {__('Order', 'edwiser-bridge')}
                  {getSortIcon('id')}
                </div>
              </th>
              <th className="eb-orders__table-header-item eb-orders__table-header-item--total">
                <div className="eb-orders__table-header-item-content">
                  {__('Total', 'edwiser-bridge')}
                </div>
              </th>
              <th
                className="eb-orders__table-header-item eb-orders__table-header-item--date"
                onClick={() => setSorting('date')}
              >
                <div className="eb-orders__table-header-item-content">
                  {__('Date', 'edwiser-bridge')}
                  {getSortIcon('date')}
                </div>
              </th>
              <th className="eb-orders__table-header-item eb-orders__table-header-item--status">
                <div className="eb-orders__table-header-item-content">
                  {__('Status', 'edwiser-bridge')}
                </div>
              </th>
              <th className="eb-orders__table-header-item eb-orders__table-header-item--actions">
                <div className="eb-orders__table-header-item-content">
                  {__('Actions', 'edwiser-bridge')}
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            {orders.map((row) => (
              <tr key={row.id} className="eb-orders__table-row">
                <td className="eb-orders__table-row-item eb-orders__table-row-item--order-id">
                  #{row.number || row.id}
                </td>
                <td className="eb-orders__table-row-item eb-orders__table-row-item--total">
                  {formatOrderTotal(row)} {__(' for', 'edwiser-bridge')}{' '}
                  {row.line_items.reduce((sum, item) => sum + item.quantity, 0)}{' '}
                  {__('items', 'edwiser-bridge')}
                </td>
                <td className="eb-orders__table-row-item eb-orders__table-row-item--date">
                  {formatDate(row.date_created)}
                </td>
                <td className="eb-orders__table-row-item eb-orders__table-row-item--status">
                  <span
                    className={`eb-orders__status eb-orders__status--${row.status}`}
                  >
                    {row.status}
                  </span>
                </td>
                <td className="eb-orders__table-row-item eb-orders__table-row-item--actions">
                  <div className="row-actions">
                    {['pending', 'failed'].includes(row.status) &&
                      row.needs_payment && (
                        <a href={row.payment_url} rel="noopener noreferrer">
                          {__('Pay', 'edwiser-bridge')}
                        </a>
                      )}
                    <button
                      type="button"
                      className="eb-orders__view-button"
                      onClick={() => handleViewOrder(row)}
                    >
                      {__('View', 'edwiser-bridge')}
                    </button>
                    {['pending', 'processing'].includes(row.status) &&
                      row.created_via !== 'subscription' &&
                      !row.meta_data?.some(
                        (meta) => meta.key === '_subscription_renewal'
                      ) &&
                      row.is_editable && (
                        <a
                          href={`${
                            window.wc_add_to_cart_params?.cart_url || '/'
                          }?cancel_order=true&order=${row.order_key}&order_id=${
                            row.id
                          }&redirect=${encodeURIComponent(
                            `${window.location.href}${
                              window.location.href.includes('?') ? '&' : '?'
                            }cancelled=true`
                          )}&_wpnonce=${
                            window.wc_params?.cancel_order_nonce || ''
                          }`}
                          rel="noopener noreferrer"
                        >
                          {__('Cancel', 'edwiser-bridge')}
                        </a>
                      )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    );
  };

  return (
    <div className="eb-user-account__wc-orders">
      {!showOrderDetails && (
        <div className="eb-orders__filter">
          <Select
            value={perPage === totalOrders ? 'all' : perPage.toString()}
            onChange={(value) => {
              if (value === 'all') {
                setPerPage(totalOrders);
              } else {
                setPerPage(Number(value));
              }
              setCurrentPage(1);
            }}
            data={[
              { value: '5', label: __('5 per page', 'edwiser-bridge') },
              { value: '10', label: __('10 per page', 'edwiser-bridge') },
              { value: '25', label: __('25 per page', 'edwiser-bridge') },
              { value: 'all', label: __('All', 'edwiser-bridge') },
            ]}
            comboboxProps={{ withinPortal: false }}
            checkIconPosition="right"
            maxDropdownHeight={350}
            rightSection={<Icons.chevronDown />}
            leftSection={<Icons.listOrder />}
            allowDeselect={false}
          />

          <TextInput
            placeholder={__('Search by product name', 'edwiser-bridge')}
            value={localSearch}
            onChange={(event) => {
              setCurrentPage(1);
              setLocalSearch(event.currentTarget.value);
            }}
            leftSection={<Icons.search />}
            leftSectionPointerEvents="none"
          />
        </div>
      )}

      {cancellationNotice.text && (
        <div
          className={`eb-orders__notice eb-orders__notice--${cancellationNotice.type} eb-orders__notice--animated`}
        >
          <span className="eb-orders__notice-text">
            {cancellationNotice.text}
          </span>
          <button
            type="button"
            className="eb-orders__notice-close"
            onClick={clearCancellationNotice}
            aria-label={__('Close notice', 'edwiser-bridge')}
          >
            <Icons.close />
          </button>
        </div>
      )}

      {!showOrderDetails && renderTableContent()}

      {!showOrderDetails && orders.length > 0 && !isLoading && (
        <div className="eb-orders__pagination">
          <span className="eb-orders__pagination-text">
            {__('Showing', 'edwiser-bridge')}{' '}
            {Math.min((currentPage - 1) * perPage + 1, totalOrders)}{' '}
            {__('to', 'edwiser-bridge')}{' '}
            {Math.min(currentPage * perPage, totalOrders)}{' '}
            {__('of', 'edwiser-bridge')} {totalOrders}{' '}
            {__('entries', 'edwiser-bridge')}
          </span>

          <Pagination
            total={totalPages}
            value={currentPage}
            onChange={setCurrentPage}
            size="sm"
          />
        </div>
      )}

      {showOrderDetails && selectedOrder && (
        <OrderDetails
          order={selectedOrder}
          onClose={handleCloseOrderDetails}
          isLoading={isLoadingOrderDetails}
        />
      )}
    </div>
  );
}

export default WcOrders;
