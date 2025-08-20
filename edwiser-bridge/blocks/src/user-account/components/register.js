import React, { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import { TextInput, PasswordInput, Checkbox, Modal } from '@mantine/core';
import CustomFields from './custom-fields';
import { Icons } from './icons';

function Register({
  customFields,
  enableTermsAndCond = false,
  termsAndCond = '',
  enableRecaptcha = false,
  recaptchaType = '',
  recaptchaSiteKey = '',
  showRecaptchaOnRegister = false,
  onRegister,
  registrationError = '',
  isRegistering = false,
}) {
  const [formData, setFormData] = useState({
    firstname: '',
    lastname: '',
    email: '',
    password: '',
    confirm_password: '',
    reg_terms_and_cond: false,
  });
  const [customFieldsData, setCustomFieldsData] = useState({});
  const [validationErrors, setValidationErrors] = useState({});
  const [recaptchaResponse, setRecaptchaResponse] = useState('');
  const [termsAndCondOpened, setTermsAndCondOpened] = useState(false);
  const [registrationSuccess, setRegistrationSuccess] = useState('');
  const [recaptchaInitialized, setRecaptchaInitialized] = useState(false);
  const recaptchaWidgetIdRef = useRef(null);
  const scriptLoadingRef = useRef(false);
  const callbackIdRef = useRef(null);

  // Initialize custom fields data when available
  useEffect(() => {
    if (customFields && customFields.length > 0) {
      const initialCustomFieldsData = {};
      customFields.forEach((field) => {
        if (field.type === 'checkbox') {
          if (
            field.value === true ||
            field.value === 'on' ||
            field.value === '1' ||
            field.value === 1
          ) {
            initialCustomFieldsData[field.name] = 'on';
          }
        } else {
          initialCustomFieldsData[field.name] = field.value;
        }
      });
      setCustomFieldsData(initialCustomFieldsData);
    }
  }, [customFields]);

  // Load reCAPTCHA script if needed
  useEffect(() => {
    let scriptElement = null;
    let timeoutId = null;

    if (
      enableRecaptcha &&
      showRecaptchaOnRegister &&
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
                setValidationErrors((prev) => ({
                  ...prev,
                  recaptcha: __(
                    'reCAPTCHA is taking too long to load. Please check your internet connection and refresh the page.',
                    'edwiser-bridge'
                  ),
                }));
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
              setValidationErrors((prev) => ({
                ...prev,
                recaptcha: __(
                  'Failed to load reCAPTCHA. Please check your internet connection, disable ad blockers, and refresh the page.',
                  'edwiser-bridge'
                ),
              }));
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
    showRecaptchaOnRegister,
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
              setValidationErrors((prev) => ({
                ...prev,
                recaptcha: __(
                  'Failed to initialize reCAPTCHA. Please refresh the page and try again.',
                  'edwiser-bridge'
                ),
              }));
            }
          }
        }
      });
    } catch (error) {
      console.error('Error in initializeRecaptcha:', error);
      // Only show error if it's not the "already rendered" error
      if (!error.message.includes('already been rendered')) {
        setValidationErrors((prev) => ({
          ...prev,
          recaptcha: __(
            'Failed to initialize reCAPTCHA. Please refresh the page and try again.',
            'edwiser-bridge'
          ),
        }));
      }
    }
  };

  // Set up reCAPTCHA callback for v3
  useEffect(() => {
    if (
      enableRecaptcha &&
      showRecaptchaOnRegister &&
      recaptchaType === 'v3' &&
      recaptchaSiteKey
    ) {
      // Create a unique identifier for this component instance
      callbackIdRef.current = `register_${Date.now()}_${Math.random()}`;

      // Initialize callback registry if it doesn't exist
      if (!window.ebCaptchaCallbacks) {
        window.ebCaptchaCallbacks = {};
      }

      // Store the callback for this component
      window.ebCaptchaCallbacks[callbackIdRef.current] = (token) => {
        setRecaptchaResponse(token);
        handleSubmit();
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
              '.eb-user-account__register-recaptcha-v3 .g-recaptcha'
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
  }, [
    enableRecaptcha,
    showRecaptchaOnRegister,
    recaptchaType,
    recaptchaSiteKey,
  ]);

  // Reset reCAPTCHA v2 on registration error
  useEffect(() => {
    if (
      registrationError &&
      enableRecaptcha &&
      showRecaptchaOnRegister &&
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
  }, [
    registrationError,
    enableRecaptcha,
    showRecaptchaOnRegister,
    recaptchaType,
  ]);

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

  // Function to validate custom fields
  const validateCustomFields = () => {
    const errors = {};

    customFields.forEach((field) => {
      if (field.required) {
        const value = customFieldsData[field.name];

        if (field.type === 'checkbox') {
          // For checkbox, check if it's checked
          if (!value) {
            errors[field.name] = __(
              `${field.label} is required`,
              'edwiser-bridge'
            );
          }
        } else {
          // For other field types, check if value exists and is not empty
          if (!value || (typeof value === 'string' && value.trim() === '')) {
            errors[field.name] = __(
              `${field.label} is required`,
              'edwiser-bridge'
            );
          }
        }
      }
    });

    // Update validation errors for custom fields
    setValidationErrors((prev) => ({
      ...prev,
      ...errors,
    }));

    return Object.keys(errors).length === 0;
  };

  // Function to clear the registration form
  const clearForm = () => {
    setFormData({
      firstname: '',
      lastname: '',
      email: '',
      password: '',
      confirm_password: '',
      reg_terms_and_cond: false,
    });
    setCustomFieldsData({});
    setValidationErrors({});
    setRecaptchaResponse('');

    // Reset reCAPTCHA v2 if enabled
    if (
      enableRecaptcha &&
      showRecaptchaOnRegister &&
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
  };

  const updateCustomFieldsData = (name, value) => {
    if (value === undefined) {
      const updatedData = { ...customFieldsData };
      delete updatedData[name];
      setCustomFieldsData(updatedData);
    } else {
      setCustomFieldsData({
        ...customFieldsData,
        [name]: value,
      });
    }

    // Clear field-specific validation error when user provides value
    if (value === true || (typeof value === 'string' && value.trim())) {
      setValidationErrors((prev) => ({
        ...prev,
        [name]: '',
      }));
    }
  };

  const handleInputChange = (field, value) => {
    setFormData((prev) => ({
      ...prev,
      [field]: value,
    }));

    // Clear validation error for this field
    if (validationErrors[field]) {
      setValidationErrors((prev) => ({
        ...prev,
        [field]: '',
      }));
    }

    // Clear reCAPTCHA token when user starts typing (for v3)
    if (
      recaptchaResponse &&
      enableRecaptcha &&
      showRecaptchaOnRegister &&
      recaptchaType === 'v3'
    ) {
      setRecaptchaResponse('');
    }
  };

  const validateForm = () => {
    const errors = {};

    if (!formData.firstname.trim()) {
      errors.firstname = __('First name is required.', 'edwiser-bridge');
    }

    if (!formData.lastname.trim()) {
      errors.lastname = __('Last name is required.', 'edwiser-bridge');
    }

    if (!formData.email.trim()) {
      errors.email = __('Email is required.', 'edwiser-bridge');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      errors.email = __(
        'Please enter a valid email address.',
        'edwiser-bridge'
      );
    }

    if (!formData.password) {
      errors.password = __('Password is required.', 'edwiser-bridge');
    }

    if (!formData.confirm_password) {
      errors.confirm_password = __(
        'Please confirm your password.',
        'edwiser-bridge'
      );
    } else if (formData.password !== formData.confirm_password) {
      errors.confirm_password = __(
        'Password and confirm password do not match.',
        'edwiser-bridge'
      );
    }

    if (enableTermsAndCond && !formData.reg_terms_and_cond) {
      errors.reg_terms_and_cond = __(
        'You must accept the terms and conditions.',
        'edwiser-bridge'
      );
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e) => {
    if (e) {
      e.preventDefault();
    }

    // Get reCAPTCHA response for v2
    if (enableRecaptcha && showRecaptchaOnRegister && recaptchaType === 'v2') {
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

      if (!recaptchaResponse) {
        setValidationErrors((prev) => ({
          ...prev,
          recaptcha: __('Please complete the reCAPTCHA.', 'edwiser-bridge'),
        }));
        return;
      }
      setRecaptchaResponse(recaptchaResponse);
    }

    // Validate fields
    if (!validateForm() || !validateCustomFields()) {
      return;
    }

    // Prepare registration data
    const registrationData = {
      ...formData,
      custom_fields: customFieldsData,
    };

    // Call the registration function
    if (onRegister) {
      const response = await onRegister(registrationData);
      // Handle success state to show user-friendly message
      if (response && response.success) {
        if (response.requires_verification) {
          const url = new URL(window.location.href);
          const isEnroll = url.searchParams.get('is_enroll');

          setRegistrationSuccess(
            isEnroll
              ? __(
                  'A verification email has been sent to your email address. Please verify your email address and try enrolling in the course again.',
                  'edwiser-bridge'
                )
              : __(
                  'A verification email has been sent to your email address. Please verify your email address.',
                  'edwiser-bridge'
                )
          );

          // Clear the form when verification email is sent
          clearForm();
        } else {
          setRegistrationSuccess(
            __('Registration successful.', 'edwiser-bridge')
          );

          // Clear the form on successful registration
          clearForm();
        }
      }
    }
  };

  return (
    <div className="eb-user-account__register">
      <div className="eb-user-account__register-header">
        <h1 className="eb-user-account__register-title">
          {__('Create an account', 'edwiser-bridge')}
        </h1>
        <p className="eb-user-account__register-description">
          {__('Create an account to continue', 'edwiser-bridge')}
        </p>
      </div>

      {/* Display registration success */}
      {registrationSuccess && (
        <div className="eb-user-account__register-success">
          {registrationSuccess}
        </div>
      )}

      {/* Display registration error */}
      {registrationError && (
        <div className="eb-user-account__register-error">
          {registrationError}
        </div>
      )}

      <form className="eb-user-account__register-form" onSubmit={handleSubmit}>
        <div className="eb-user-account__register-form-group">
          <TextInput
            type="text"
            placeholder={__('First Name', 'edwiser-bridge')}
            label={__('First Name', 'edwiser-bridge')}
            value={formData.firstname}
            onChange={(e) => handleInputChange('firstname', e.target.value)}
            error={validationErrors.firstname}
            required
          />
          <TextInput
            type="text"
            placeholder={__('Last Name', 'edwiser-bridge')}
            label={__('Last Name', 'edwiser-bridge')}
            value={formData.lastname}
            onChange={(e) => handleInputChange('lastname', e.target.value)}
            error={validationErrors.lastname}
            required
          />
        </div>
        <TextInput
          type="email"
          placeholder={__('Email', 'edwiser-bridge')}
          label={__('Email', 'edwiser-bridge')}
          value={formData.email}
          onChange={(e) => handleInputChange('email', e.target.value)}
          error={validationErrors.email}
          required
        />
        <div className="eb-user-account__register-form-group">
          <PasswordInput
            placeholder={__('Password', 'edwiser-bridge')}
            label={__('Password', 'edwiser-bridge')}
            value={formData.password}
            onChange={(e) => handleInputChange('password', e.target.value)}
            error={validationErrors.password}
            required
          />
          <PasswordInput
            placeholder={__('Confirm Password', 'edwiser-bridge')}
            label={__('Confirm Password', 'edwiser-bridge')}
            value={formData.confirm_password}
            onChange={(e) =>
              handleInputChange('confirm_password', e.target.value)
            }
            error={validationErrors.confirm_password}
            required
          />
        </div>
        {/* Terms and Conditions */}
        {enableTermsAndCond && termsAndCond && (
          <Checkbox
            label={
              <>
                {__('I agree to the ', 'edwiser-bridge')}
                <div
                  className="eb-user-account__register-terms-link"
                  onClick={(e) => {
                    e.preventDefault();
                    setTermsAndCondOpened(true);
                  }}
                >
                  {__('Terms and Conditions', 'edwiser-bridge')}
                </div>
              </>
            }
            checked={formData.reg_terms_and_cond}
            onChange={(e) =>
              handleInputChange('reg_terms_and_cond', e.target.checked)
            }
            error={validationErrors.reg_terms_and_cond}
            required
            className="eb-user-account__register-terms-checkbox"
          />
        )}

        <CustomFields
          customFields={customFields}
          customFieldsData={customFieldsData}
          updateCustomFieldsData={updateCustomFieldsData}
          validationErrors={validationErrors}
        />

        {/* Show recaptcha v2 if enabled and configured for register */}
        {enableRecaptcha &&
          showRecaptchaOnRegister &&
          recaptchaType === 'v2' &&
          recaptchaSiteKey && (
            <div className="eb-user-account__register-recaptcha">
              <div
                className="g-recaptcha"
                data-sitekey={recaptchaSiteKey}
                style={{ minHeight: '78px' }}
              ></div>
            </div>
          )}

        {/* Show reCAPTCHA error */}
        {validationErrors.recaptcha && (
          <div className="eb-user-account__register-recaptcha-error">
            {validationErrors.recaptcha}
          </div>
        )}

        {/* Show recaptcha v3 if enabled and configured for register */}
        {enableRecaptcha &&
        showRecaptchaOnRegister &&
        recaptchaType === 'v3' &&
        recaptchaSiteKey ? (
          <div className="eb-user-account__register-recaptcha-v3">
            <button
              data-sitekey={recaptchaSiteKey}
              data-callback="ebSubmitCaptchaForm"
              data-action="submit"
              className="g-recaptcha eb-reg-button button button-primary et_pb_button et_pb_contact_submit eb-user-account__register-button"
              type="button"
              onClick={handleSubmit}
              disabled={isRegistering}
            >
              {isRegistering && <Icons.loader />}
              {__('Register', 'edwiser-bridge')}
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
            className="eb-user-account__register-button"
            type="submit"
            disabled={isRegistering}
          >
            {isRegistering && <Icons.loader />}
            {__('Register', 'edwiser-bridge')}
          </button>
        )}

        {recaptchaType === 'v3' && showRecaptchaOnRegister && (
          <input
            type="hidden"
            name="register"
            value={__('Register', 'edwiser-bridge')}
          />
        )}
        <p className="eb-user-account__register-login-link">
          {__('Already have an account? ', 'edwiser-bridge')}
          <a
            href="#"
            onClick={(e) => {
              e.preventDefault();
              const url = new URL(window.location);
              url.searchParams.set('action', 'eb_login');
              window.history.pushState({}, '', url);
              window.dispatchEvent(new PopStateEvent('popstate'));
            }}
          >
            {__('Login', 'edwiser-bridge')}
          </a>
        </p>
      </form>

      <Modal
        opened={termsAndCondOpened}
        onClose={() => setTermsAndCondOpened(false)}
        title={__('Terms and Conditions', 'edwiser-bridge')}
        withinPortal={false}
        closeOnClickOutside={false}
        size="md"
      >
        <div className="eb-user-account__register-terms-modal">
          <div
            className="eb-user-account__register-terms-modal-desc"
            dangerouslySetInnerHTML={{ __html: termsAndCond }}
          ></div>
          <div className="eb-user-account__register-terms-modal-action">
            <button
              className="btn__action-cancel"
              onClick={() => {
                handleInputChange('reg_terms_and_cond', false);
                setTermsAndCondOpened(false);
              }}
            >
              {__('Disagree', 'edwiser-bridge')}
            </button>
            <button
              className="btn__action-confirm"
              onClick={() => {
                handleInputChange('reg_terms_and_cond', true);
                setTermsAndCondOpened(false);
              }}
            >
              {__('Agree', 'edwiser-bridge')}
            </button>
          </div>
        </div>
      </Modal>
    </div>
  );
}

export default Register;
