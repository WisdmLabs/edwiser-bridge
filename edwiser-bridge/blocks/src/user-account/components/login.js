import React, { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import { PasswordInput, Checkbox, TextInput } from '@mantine/core';
import { Icons } from './icons';

function Login({
  enableRegistration = false,
  enableRecaptcha = false,
  recaptchaType = '',
  recaptchaSiteKey = '',
  showRecaptchaOnLogin = false,
  lostPasswordUrl = '',
  login,
  loginError = '',
  isLoggingIn = false,
  sso = {},
}) {
  const [formData, setFormData] = useState({
    username: '',
    password: '',
    remember: false,
  });
  const [recaptchaToken, setRecaptchaToken] = useState('');
  const [fieldErrors, setFieldErrors] = useState({
    username: '',
    password: '',
    general: '',
  });
  const [captchaError, setCaptchaError] = useState('');
  const [recaptchaInitialized, setRecaptchaInitialized] = useState(false);
  const recaptchaWidgetIdRef = useRef(null);
  const scriptLoadingRef = useRef(false);
  const callbackIdRef = useRef(null);

  // Load reCAPTCHA script if needed
  useEffect(() => {
    let scriptElement = null;
    let timeoutId = null;

    if (
      enableRecaptcha &&
      showRecaptchaOnLogin &&
      recaptchaSiteKey &&
      !recaptchaInitialized
    ) {
      if (recaptchaType === 'v2') {
        // Load reCAPTCHA v2 script (without render parameter for proper v2 functionality)
        if (!window.grecaptcha && !scriptLoadingRef.current) {
          // Check if script is already being loaded
          const existingScript = document.querySelector(
            'script[src*="google.com/recaptcha/api.js"]'
          );
          if (!existingScript) {
            scriptLoadingRef.current = true;
            scriptElement = document.createElement('script');
            scriptElement.src = `https://www.google.com/recaptcha/api.js`;
            scriptElement.async = true;
            scriptElement.defer = true;

            // Set timeout for script loading
            timeoutId = setTimeout(() => {
              if (!window.grecaptcha) {
                console.error('reCAPTCHA script loading timeout');
                scriptLoadingRef.current = false;
                setCaptchaError(
                  __(
                    'reCAPTCHA is taking too long to load. Please check your internet connection and refresh the page.',
                    'edwiser-bridge'
                  )
                );
              }
            }, 10000); // 10 second timeout

            scriptElement.onload = () => {
              clearTimeout(timeoutId);
              scriptLoadingRef.current = false;
              // Initialize reCAPTCHA after script loads
              initializeRecaptcha();
            };

            scriptElement.onerror = () => {
              clearTimeout(timeoutId);
              scriptLoadingRef.current = false;
              console.error('Failed to load reCAPTCHA script');
              // Show error message to user
              setCaptchaError(
                __(
                  'Failed to load reCAPTCHA. Please check your internet connection, disable ad blockers, and refresh the page.',
                  'edwiser-bridge'
                )
              );
            };
            document.head.appendChild(scriptElement);
          } else {
            // Script already loaded, initialize reCAPTCHA
            setTimeout(() => {
              initializeRecaptcha();
            }, 100);
          }
        } else if (window.grecaptcha && !recaptchaInitialized) {
          // Script already loaded, initialize reCAPTCHA
          setTimeout(() => {
            initializeRecaptcha();
          }, 100);
        }
      } else if (recaptchaType === 'v3') {
        // Load reCAPTCHA v3 script (same as frontend form handler)
        if (!window.grecaptcha && !scriptLoadingRef.current) {
          // Check if script is already being loaded
          const existingScript = document.querySelector(
            'script[src*="google.com/recaptcha/api.js"]'
          );
          if (!existingScript) {
            scriptLoadingRef.current = true;
            scriptElement = document.createElement('script');
            scriptElement.src = `https://www.google.com/recaptcha/api.js`;
            scriptElement.async = true;
            scriptElement.defer = true;
            document.head.appendChild(scriptElement);
            scriptLoadingRef.current = false;
          }
        }
      }
    }

    // Cleanup function
    return () => {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      // Note: We don't remove the script element as it might be used by other components
      // The script will be reused if already loaded
    };
  }, [
    enableRecaptcha,
    showRecaptchaOnLogin,
    recaptchaSiteKey,
    recaptchaType,
    recaptchaInitialized,
  ]);

  // Function to initialize reCAPTCHA v2
  const initializeRecaptcha = () => {
    if (
      !window.grecaptcha ||
      !window.grecaptcha.render ||
      recaptchaInitialized
    ) {
      return;
    }

    try {
      const recaptchaElements = document.querySelectorAll('.g-recaptcha');
      recaptchaElements.forEach((element) => {
        // Check if reCAPTCHA is already rendered on this element
        if (
          !element.getAttribute('data-widget-id') &&
          !recaptchaWidgetIdRef.current
        ) {
          try {
            const widgetId = window.grecaptcha.render(element, {
              sitekey: recaptchaSiteKey,
            });

            // Store the widget ID to prevent duplicate rendering
            recaptchaWidgetIdRef.current = widgetId;
            element.setAttribute('data-widget-id', widgetId);
            setRecaptchaInitialized(true);

            // Check if reCAPTCHA widget is visible after rendering
            setTimeout(() => {
              const iframe = element.querySelector('iframe');
              if (!iframe || iframe.style.display === 'none') {
                console.warn('reCAPTCHA widget may not be visible');
                // Force re-render if widget is not visible
                if (
                  window.grecaptcha &&
                  window.grecaptcha.reset &&
                  recaptchaWidgetIdRef.current
                ) {
                  window.grecaptcha.reset(recaptchaWidgetIdRef.current);
                }
              }
            }, 500);
          } catch (error) {
            console.error('Error rendering reCAPTCHA:', error);
            // Only show error if it's not the "already rendered" error
            if (!error.message.includes('already been rendered')) {
              setCaptchaError(
                __(
                  'Failed to initialize reCAPTCHA. Please refresh the page and try again.',
                  'edwiser-bridge'
                )
              );
            }
          }
        }
      });
    } catch (error) {
      console.error('Error in initializeRecaptcha:', error);
      // Only show error if it's not the "already rendered" error
      if (!error.message.includes('already been rendered')) {
        setCaptchaError(
          __(
            'Failed to initialize reCAPTCHA. Please refresh the page and try again.',
            'edwiser-bridge'
          )
        );
      }
    }
  };

  // Helper function to determine if input is email or username
  const isEmail = (value) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(value.trim());
  };

  // Validate form fields with support for both username and email formats
  const validateField = (field, value) => {
    let error = '';

    switch (field) {
      case 'username':
        if (!value.trim()) {
          error = __('Username or email is required.', 'edwiser-bridge');
        } else {
          // Check if it's a valid email format
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          // Check if it's a valid username format (minimal restrictions, only block problematic characters)
          const usernameRegex = /^[^\s<>\"'&]{1,50}$/;

          if (
            !emailRegex.test(value.trim()) &&
            !usernameRegex.test(value.trim())
          ) {
            error = __(
              'Please enter a valid username or email address.',
              'edwiser-bridge'
            );
          }
        }
        break;
      case 'password':
        if (!value.trim()) {
          error = __('Password is required.', 'edwiser-bridge');
        }
        break;
      default:
        break;
    }

    return error;
  };

  const handleInputChange = (field, value) => {
    setFormData((prev) => ({
      ...prev,
      [field]: value,
    }));

    // Clear field error when user starts typing
    if (fieldErrors[field]) {
      setFieldErrors((prev) => ({
        ...prev,
        [field]: '',
      }));
    }

    // Clear general error when user starts typing
    if (fieldErrors.general) {
      setFieldErrors((prev) => ({
        ...prev,
        general: '',
      }));
    }

    // Clear reCAPTCHA token when user starts typing (for v3)
    if (
      recaptchaToken &&
      enableRecaptcha &&
      showRecaptchaOnLogin &&
      recaptchaType === 'v3'
    ) {
      setRecaptchaToken('');
    }

    // Clear captcha error when user starts typing
    if (captchaError) {
      setCaptchaError('');
    }
  };

  const validateForm = () => {
    const errors = {
      username: validateField('username', formData.username),
      password: validateField('password', formData.password),
      general: '',
    };

    setFieldErrors(errors);

    return !errors.username && !errors.password && !errors.general;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Clear previous errors
    setFieldErrors({
      username: '',
      password: '',
      general: '',
    });
    setCaptchaError('');

    // Validate form first
    if (!validateForm()) {
      return;
    }

    // Get reCAPTCHA response for v2
    if (enableRecaptcha && showRecaptchaOnLogin && recaptchaType === 'v2') {
      let recaptchaResponse = '';

      if (recaptchaWidgetIdRef.current) {
        try {
          recaptchaResponse = window.grecaptcha?.getResponse(
            recaptchaWidgetIdRef.current
          );
        } catch (error) {
          console.warn('Error getting reCAPTCHA response:', error);
        }
      } else {
        // Fallback to getResponse() without widget ID
        recaptchaResponse = window.grecaptcha?.getResponse();
      }

      console.log('recaptchaToken', recaptchaResponse);
      if (!recaptchaResponse) {
        setCaptchaError(__('Please complete the reCAPTCHA.', 'edwiser-bridge'));
        return;
      }
      setRecaptchaToken(recaptchaResponse);
    }

    // Prepare credentials for API
    const credentials = {
      username: formData.username.trim(),
      password: formData.password,
      remember: formData.remember,
      isEmail: isEmail(formData.username),
    };

    // Call login function
    await login(credentials);
  };

  // Handle recaptcha v3 callback
  useEffect(() => {
    if (
      enableRecaptcha &&
      showRecaptchaOnLogin &&
      recaptchaType === 'v3' &&
      recaptchaSiteKey
    ) {
      // Create a unique identifier for this component instance
      callbackIdRef.current = `login_${Date.now()}_${Math.random()}`;

      // Initialize callback registry if it doesn't exist
      if (!window.ebCaptchaCallbacks) {
        window.ebCaptchaCallbacks = {};
      }

      // Store the callback for this component
      window.ebCaptchaCallbacks[callbackIdRef.current] = (token) => {
        setRecaptchaToken(token);
        // Auto-submit form after getting token
        const form = document.querySelector('.eb-user-account__login-form');
        if (form) {
          form.dispatchEvent(new Event('submit', { bubbles: true }));
        }
      };

      // Set the global callback to use our registry (only if not already set)
      if (!window.ebSubmitCaptchaForm) {
        window.ebSubmitCaptchaForm = (token) => {
          // Execute all registered callbacks
          if (window.ebCaptchaCallbacks) {
            Object.values(window.ebCaptchaCallbacks).forEach((callback) => {
              try {
                callback(token);
              } catch (error) {
                console.warn('Error executing reCAPTCHA callback:', error);
              }
            });
          }
        };
      }

      // Initialize reCAPTCHA v3 after a short delay to ensure DOM is ready
      const initTimer = setTimeout(() => {
        if (window.grecaptcha && window.grecaptcha.render) {
          try {
            // Render the hidden reCAPTCHA badge
            const recaptchaElement = document.querySelector(
              '.eb-user-account__login-recaptcha-v3 .g-recaptcha'
            );
            if (
              recaptchaElement &&
              !recaptchaElement.getAttribute('data-widget-id')
            ) {
              window.grecaptcha.render(recaptchaElement, {
                sitekey: recaptchaSiteKey,
                size: 'invisible',
                callback: 'ebSubmitCaptchaForm',
                'expired-callback': () => {
                  console.log('reCAPTCHA v3 expired');
                },
              });
            }
          } catch (error) {
            console.warn('Error rendering reCAPTCHA v3:', error);
          }
        }
      }, 100);

      return () => clearTimeout(initTimer);
    }

    // Cleanup function for v3 callback
    return () => {
      if (callbackIdRef.current && window.ebCaptchaCallbacks) {
        // Remove this component's callback from the registry
        delete window.ebCaptchaCallbacks[callbackIdRef.current];

        // Only clean up if this was the last callback and we're the one who created the global function
        if (Object.keys(window.ebCaptchaCallbacks).length === 0) {
          // Don't delete the global function - let other components handle it
          // Just clean up our local registry
          delete window.ebCaptchaCallbacks;
        }
      }
    };
  }, [enableRecaptcha, showRecaptchaOnLogin, recaptchaType, recaptchaSiteKey]);

  // Cleanup effect for component unmounting
  useEffect(() => {
    return () => {
      // Clean up reCAPTCHA widget reference
      if (recaptchaWidgetIdRef.current) {
        recaptchaWidgetIdRef.current = null;
      }
      // Reset initialization state
      setRecaptchaInitialized(false);
      // Clean up callback ID reference
      if (callbackIdRef.current) {
        callbackIdRef.current = null;
      }
    };
  }, []);

  // Display API error if available
  useEffect(() => {
    if (loginError) {
      setFieldErrors((prev) => ({
        ...prev,
        general: loginError,
      }));

      // Reset reCAPTCHA v2 on error
      if (
        enableRecaptcha &&
        showRecaptchaOnLogin &&
        recaptchaType === 'v2' &&
        window.grecaptcha &&
        recaptchaWidgetIdRef.current
      ) {
        try {
          window.grecaptcha.reset(recaptchaWidgetIdRef.current);
        } catch (error) {
          console.warn('Error resetting reCAPTCHA:', error);
        }
      }
    }
  }, [loginError, enableRecaptcha, showRecaptchaOnLogin, recaptchaType]);

  return (
    <div className="eb-user-account__login">
      <div className="eb-user-account__login-header">
        <h1 className="eb-user-account__login-title">
          {__('Login', 'edwiser-bridge')}
        </h1>
        <p className="eb-user-account__login-description">
          {__('Login to your account to continue', 'edwiser-bridge')}
        </p>
      </div>

      {fieldErrors.general && (
        <div className="eb-user-account__login-error">
          <p dangerouslySetInnerHTML={{ __html: fieldErrors.general }} />
        </div>
      )}

      <form className="eb-user-account__login-form" onSubmit={handleSubmit}>
        <TextInput
          placeholder={__('john.doe / john@example.com', 'edwiser-bridge')}
          label={__('Username / Email', 'edwiser-bridge')}
          required
          value={formData.username}
          onChange={(e) => handleInputChange('username', e.target.value)}
          disabled={isLoggingIn}
          error={fieldErrors.username}
        />
        <div className="eb-user-account__login-password-wrapper">
          <PasswordInput
            placeholder={__('Password', 'edwiser-bridge')}
            label={__('Password', 'edwiser-bridge')}
            required
            value={formData.password}
            onChange={(e) => handleInputChange('password', e.target.value)}
            disabled={isLoggingIn}
            error={fieldErrors.password}
          />
          <a
            className="eb-user-account__login-password-forgot"
            href={lostPasswordUrl || `/my-account/lost-password/`}
          >
            {__('Forgot password?', 'edwiser-bridge')}
          </a>
        </div>
        <Checkbox
          label={__('Remember me', 'edwiser-bridge')}
          name="remember_me"
          checked={formData.remember}
          onChange={(e) => handleInputChange('remember', e.target.checked)}
          disabled={isLoggingIn}
        />

        {/* Show recaptcha v2 if enabled and configured for login */}
        {enableRecaptcha &&
          showRecaptchaOnLogin &&
          recaptchaType === 'v2' &&
          recaptchaSiteKey && (
            <div className="eb-user-account__login-recaptcha">
              <div
                className="g-recaptcha"
                data-sitekey={recaptchaSiteKey}
                style={{ minHeight: '78px' }}
              ></div>
            </div>
          )}

        {/* Show recaptcha error for v2 */}
        {enableRecaptcha &&
          showRecaptchaOnLogin &&
          recaptchaType === 'v2' &&
          captchaError && (
            <div className="eb-user-account__login-recaptcha-error">
              {captchaError}
            </div>
          )}

        {/* Show recaptcha v3 if enabled and configured for login */}
        {enableRecaptcha &&
        showRecaptchaOnLogin &&
        recaptchaType === 'v3' &&
        recaptchaSiteKey ? (
          <div className="eb-user-account__login-recaptcha-v3">
            <button
              data-sitekey={recaptchaSiteKey}
              data-callback="ebSubmitCaptchaForm"
              data-action="submit"
              className="g-recaptcha eb-login-button button button-primary et_pb_button et_pb_contact_submit eb-user-account__login-button"
              type="button"
              onClick={handleSubmit}
              disabled={isLoggingIn}
            >
              {isLoggingIn && <Icons.loader />}
              {__('Login', 'edwiser-bridge')}
            </button>
            {/* Hidden reCAPTCHA v3 badge for compliance */}
            <div
              className="g-recaptcha"
              data-sitekey={recaptchaSiteKey}
              data-size="invisible"
              style={{
                position: 'absolute',
                left: '-9999px',
                visibility: 'hidden',
                pointerEvents: 'none',
              }}
            ></div>
          </div>
        ) : (
          <button
            className="eb-user-account__login-button"
            type="submit"
            disabled={isLoggingIn}
          >
            {isLoggingIn && <Icons.loader />}
            {__('Login', 'edwiser-bridge')}
          </button>
        )}

        {enableRegistration && (
          <p className="eb-user-account__login-register-link">
            {__("Don't have an account? ", 'edwiser-bridge')}
            <a
              href="#"
              onClick={(e) => {
                e.preventDefault();
                const url = new URL(window.location);
                url.searchParams.set('action', 'eb_register');
                window.history.pushState({}, '', url);
                window.dispatchEvent(new PopStateEvent('popstate'));
              }}
            >
              {__('Register', 'edwiser-bridge')}
            </a>
          </p>
        )}

        {/* SSO Buttons */}
        {(sso.google || sso.facebook) && (
          <div className="eb-user-account__login-sso">
            <div className="eb-user-account__login-sso-divider">
              <span>{__('Or', 'edwiser-bridge')}</span>
            </div>

            <div className="eb-user-account__login-sso-buttons">
              {sso.google && (
                <a
                  href={sso.google}
                  className="eb-user-account__login-sso-button eb-user-account__login-sso-button--google"
                  disabled={isLoggingIn}
                >
                  <Icons.google />
                  {__('Continue with Google', 'edwiser-bridge')}
                </a>
              )}

              {sso.facebook && (
                <a
                  href={sso.facebook}
                  className="eb-user-account__login-sso-button eb-user-account__login-sso-button--facebook"
                  disabled={isLoggingIn}
                >
                  <Icons.facebook />
                  {__('Continue with Facebook', 'edwiser-bridge')}
                </a>
              )}
            </div>
          </div>
        )}
      </form>
    </div>
  );
}

export default Login;
