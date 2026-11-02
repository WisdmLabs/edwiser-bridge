import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState, useCallback } from 'react';

export const useWcOrders = (enabled = true) => {
  const [orders, setOrders] = useState([]);
  const [isLoading, setIsLoading] = useState(enabled);
  const [totalOrders, setTotalOrders] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(10);
  const [totalPages, setTotalPages] = useState(0);
  const [search, setSearch] = useState('');
  const [orderBy, setOrderBy] = useState('date');
  const [order, setOrder] = useState('desc');

  const buildQueryParams = useCallback(() => {
    const params = new URLSearchParams();

    params.append('page', currentPage.toString());
    params.append('per_page', perPage.toString());
    params.append('orderby', orderBy);
    params.append('order', order);
    params.append(
      'status',
      'pending,processing,on-hold,completed,cancelled,refunded,failed'
    );

    if (search) params.append('search', search);

    return params.toString();
  }, [currentPage, perPage, search, orderBy, order]);

  const fetchOrders = useCallback(async () => {
    if (!enabled) {
      setIsLoading(false);
      return;
    }

    setIsLoading(true);

    try {
      let userId = null;

      if (window.ebCurrentUser?.ID) {
        userId = window.ebCurrentUser.ID;
      } else {
        try {
          const user = await apiFetch({ path: '/wp/v2/users/me' });
          userId = user.id;
        } catch (userError) {
          console.error('Error fetching user ID:', userError);
          setOrders([]);
          setTotalOrders(0);
          setTotalPages(0);
          setIsLoading(false);
          return;
        }
      }

      const queryString = buildQueryParams();
      // const path = `/wc/v3/orders?customer=${Number(userId)}&${queryString}`;
      const path = `/eb/api/v1/user-account/wc-orders?${queryString}`;

      const response = await apiFetch({
        path,
        parse: false,
      });

      setTotalPages(parseInt(response.headers.get('X-WP-TotalPages')));
      setTotalOrders(parseInt(response.headers.get('X-WP-Total')));

      const data = await response.json();

      setOrders(data);
    } catch (err) {
      console.error('Error fetching WooCommerce orders:', err);
      setOrders([]);
      setTotalOrders(0);
    } finally {
      setIsLoading(false);
    }
  }, [enabled, buildQueryParams]);

  useEffect(() => {
    fetchOrders();
  }, [fetchOrders]);

  return {
    orders,
    totalOrders,
    totalPages,
    isLoading,
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
  };
};
