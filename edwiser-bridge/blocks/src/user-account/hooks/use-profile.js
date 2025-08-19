import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';

export const useProfile = () => {
  const [user, setUser] = useState([]);
  const [customFields, setCustomFields] = useState([]);
  const [countries, setCountries] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchProfile = async () => {
      setIsLoading(true);

      try {
        const response = await apiFetch({
          path: `/eb/api/v1/user-account/profile`,
        });

        setUser(response.profile || []);
        setCustomFields(response.custom_fields || []);
        setCountries(response.countries || []);
      } catch (err) {
        console.error('Error fetching profile data:', err);
      } finally {
        setIsLoading(false);
      }
    };

    fetchProfile();
  }, []);

  const updateProfile = async (data) => {
    try {
      const response = await apiFetch({
        path: `/eb/api/v1/user-account/profile`,
        method: 'POST',
        data,
      });

      return { success: true, data: response };
    } catch (error) {
      return { success: false, error };
    }
  };

  return {
    user,
    customFields,
    countries,
    isLoading,
    updateProfile,
  };
};
