import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';

export const useAuth = () => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [customFields, setCustomFields] = useState([]);
  const [enableRegistration, setEnableRegistration] = useState(false);
  const [enableTermsAndCond, setEnableTermsAndCond] = useState(false);
  const [termsAndCond, setTermsAndCond] = useState('');
  const [enableRecaptcha, setEnableRecaptcha] = useState(false);
  const [recaptchaType, setRecaptchaType] = useState('');
  const [recaptchaSiteKey, setRecaptchaSiteKey] = useState('');
  const [showRecaptchaOnLogin, setShowRecaptchaOnLogin] = useState(false);
  const [showRecaptchaOnRegister, setShowRecaptchaOnRegister] = useState(false);
  const [lostPasswordUrl, setLostPasswordUrl] = useState('');
  const [loginError, setLoginError] = useState('');
  const [isLoggingIn, setIsLoggingIn] = useState(false);
  const [registrationError, setRegistrationError] = useState('');
  const [isRegistering, setIsRegistering] = useState(false);
  const [sso, setSso] = useState({});

  useEffect(() => {
    const checkAuth = async () => {
      setIsLoading(true);

      const urlParams = new URLSearchParams(window.location.search);
      const redirectTo = urlParams.get('redirect_to');
      const isEnroll = urlParams.get('is_enroll');

      try {
        const query = new URLSearchParams();

        if (redirectTo) {
          query.append('redirect_to', redirectTo);
        }
        if (isEnroll) {
          query.append('is_enroll', isEnroll);
        }

        const response = await fetch(
          `/wp-json/eb/api/v1/user-account/auth?${query.toString()}`
        );
        const authData = await response.json();

        setIsLoggedIn(authData.is_logged_in);

        // Set all form data if available (when not logged in)
        if (authData.custom_fields) {
          setCustomFields(authData.custom_fields);
        }
        if (authData.enable_registration !== undefined) {
          setEnableRegistration(authData.enable_registration);
        }
        if (authData.enable_terms_and_cond !== undefined) {
          setEnableTermsAndCond(authData.enable_terms_and_cond);
        }
        if (authData.terms_and_cond !== undefined) {
          setTermsAndCond(authData.terms_and_cond);
        }
        if (authData.enable_recaptcha !== undefined) {
          setEnableRecaptcha(authData.enable_recaptcha);
        }
        if (authData.recaptcha_type !== undefined) {
          setRecaptchaType(authData.recaptcha_type);
        }
        if (authData.recaptcha_site_key !== undefined) {
          setRecaptchaSiteKey(authData.recaptcha_site_key);
        }
        if (authData.show_recaptcha_on_login !== undefined) {
          setShowRecaptchaOnLogin(authData.show_recaptcha_on_login);
        }
        if (authData.show_recaptcha_on_register !== undefined) {
          setShowRecaptchaOnRegister(authData.show_recaptcha_on_register);
        }
        if (authData.lost_password_url !== undefined) {
          setLostPasswordUrl(authData.lost_password_url);
        }
        if (authData.sso !== undefined) {
          setSso(authData.sso);
        }
      } catch (err) {
        console.error('Error checking authentication:', err);
        setIsLoggedIn(false);
      } finally {
        setIsLoading(false);
      }
    };

    checkAuth();
  }, []);

  const login = async (credentials) => {
    setIsLoggingIn(true);
    setLoginError('');

    const urlParams = new URLSearchParams(window.location.search);
    const redirectTo = urlParams.get('redirect_to');
    const isEnroll = urlParams.get('is_enroll');

    try {
      const response = await apiFetch({
        path: `/eb/api/v1/user-account/login`,
        method: 'POST',
        data: {
          ...credentials,
          redirect_to: redirectTo || '',
          is_enroll: isEnroll || '',
        },
      });

      if (response.success) {
        setIsLoggedIn(true);

        // Priority 1: Redirect to Moodle SSO URL if available (highest priority)
        if (
          response.moodle_sso &&
          response.moodle_sso.enabled &&
          response.moodle_sso.moodle_url
        ) {
          // Direct redirect to Moodle SSO URL
          window.location.href = response.moodle_sso.moodle_url;
          return; // Exit early to prevent other redirects
        }

        // Priority 2: Always prioritize frontend redirect_to parameter
        if (redirectTo) {
          let redirectUrl = redirectTo;

          // Add auto_enroll parameter if needed
          if (isEnroll === 'true') {
            const separator = redirectUrl.includes('?') ? '&' : '?';
            redirectUrl += `${separator}auto_enroll=true`;
          }

          // Redirect to frontend URL
          window.location.href = redirectUrl;
        } else if (response.redirect_url) {
          // Priority 3: Fallback to server redirect if no frontend redirect
          window.location.href = response.redirect_url;
        }
        // If no redirect_to is set, stay on the current page
      }
    } catch (err) {
      console.error('Login error:', err);
      // Handle different types of errors
      if (
        err.code === 'validation_error' ||
        err.code === 'username_required' ||
        err.code === 'password_required' ||
        err.code === 'recaptcha_error' ||
        err.code === 'invalid_captcha' ||
        err.code === 'captcha_required' ||
        err.code === 'login_failed'
      ) {
        setLoginError(err.message);
      } else {
        setLoginError(__('Login failed. Please try again.', 'edwiser-bridge'));
      }
    } finally {
      setIsLoggingIn(false);
    }
  };

  const register = async (registrationData) => {
    setIsRegistering(true);
    setRegistrationError('');

    try {
      // Get URL parameters for redirect handling
      const urlParams = new URLSearchParams(window.location.search);
      const redirectTo = urlParams.get('redirect_to');
      const isEnroll = urlParams.get('is_enroll');

      // Prepare registration data with redirect information
      const data = {
        ...registrationData,
        redirect_to: redirectTo || '',
        is_enroll: isEnroll || '',
      };

      const response = await apiFetch({
        path: `/eb/api/v1/user-account/register`,
        method: 'POST',
        data: data,
      });

      if (response.success) {
        setIsLoggedIn(!response.requires_verification);

        // Handle redirection on frontend
        if (redirectTo) {
          let redirectUrl = redirectTo;

          // Add auto_enroll parameter if needed
          if (isEnroll === 'true') {
            const separator = redirectUrl.includes('?') ? '&' : '?';
            redirectUrl += `${separator}auto_enroll=true`;
          }

          window.location.href = redirectUrl;
        } else if (response.redirect_url && response.should_redirect) {
          window.location.href = response.redirect_url;
        }
        // If no redirect is set, stay on the current page
      }

      return response;
    } catch (err) {
      console.error('Registration error:', err);
      // Handle different types of registration errors
      if (
        err.code === 'terms_required' ||
        err.code === 'email_required' ||
        err.code === 'invalid_email' ||
        err.code === 'email_exists' ||
        err.code === 'firstname_required' ||
        err.code === 'lastname_required' ||
        err.code === 'password_required' ||
        err.code === 'password_mismatch' ||
        err.code === 'recaptcha_error' ||
        err.code === 'invalid_captcha' ||
        err.code === 'captcha_required' ||
        err.code === 'registration_failed'
      ) {
        setRegistrationError(err.message);
      } else {
        setRegistrationError(
          __('Registration failed. Please try again.', 'edwiser-bridge')
        );
      }
      return null;
    } finally {
      setIsRegistering(false);
    }
  };

  return {
    isLoggedIn,
    isLoading,
    customFields,
    enableRegistration,
    enableTermsAndCond,
    termsAndCond,
    enableRecaptcha,
    recaptchaType,
    recaptchaSiteKey,
    showRecaptchaOnLogin,
    showRecaptchaOnRegister,
    lostPasswordUrl,
    login,
    loginError,
    isLoggingIn,
    register,
    registrationError,
    isRegistering,
    sso,
  };
};
