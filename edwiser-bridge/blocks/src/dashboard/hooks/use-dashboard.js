import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';

export const useDashboard = (redirect_to) => {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchDashboard = async () => {
      setIsLoading(true);

      try {
        const userData = await apiFetch({
          path: `/eb/api/v1/user-account/dashboard?redirect_to=${redirect_to}`,
        });

        setUser(userData);
      } catch (err) {
        console.error('Error fetching user data:', err);
      } finally {
        setIsLoading(false);
      }
    };

    fetchDashboard();
  }, []);

  return {
    user,
    isLoading,
  };
};
