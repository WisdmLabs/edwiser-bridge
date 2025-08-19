import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';

export const useOrders = () => {
  const [orders, setOrders] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [totalOrders, setTotalOrders] = useState(0);

  useEffect(() => {
    const fetchOrders = async () => {
      setIsLoading(true);

      try {
        const response = await apiFetch({
          path: `/eb/api/v1/user-account/orders`,
        });

        setOrders(response.orders || []);
        setTotalOrders(response.total_orders || 0);
      } catch (err) {
        console.error('Error fetching orders data:', err);
      } finally {
        setIsLoading(false);
      }
    };

    fetchOrders();
  }, []);

  return {
    orders,
    totalOrders,
    isLoading,
  };
};
