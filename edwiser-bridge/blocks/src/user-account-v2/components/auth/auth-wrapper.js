import React, { useEffect, useState } from 'react';
import { MantineProvider, Skeleton } from '@mantine/core';
import Login from './login';
import Register from './register';
import { useAuth } from '../../hooks/use-auth';

function AuthWrapper({ onAuthSuccess }) {
  const {
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
  } = useAuth();

  const [action, setAction] = useState('eb_login');

  useEffect(() => {
    const getAction = () => {
      const params = new URLSearchParams(window.location.search);
      const a = params.get('action');
      return a === 'eb_register' ? 'eb_register' : 'eb_login';
    };

    const handlePop = () => {
      const newAction = getAction();
      setAction(newAction);
    };

    const initialAction = getAction();
    setAction(initialAction);

    window.addEventListener('popstate', handlePop);
    return () => window.removeEventListener('popstate', handlePop);
  }, []);

  useEffect(() => {
    // If user becomes logged in, call success callback
    if (!isLoading && isLoggedIn) {
      if (onAuthSuccess) {
        onAuthSuccess();
      } else {
        // Fallback to reload if no callback provided
        window.location.reload();
      }
    }
  }, [isLoading, isLoggedIn, onAuthSuccess]);

  if (isLoading) {
    return (
      <MantineProvider>
        <Skeleton height={500} width="100%" />
      </MantineProvider>
    );
  }

  // Force switch to login if registration is disabled
  if (action === 'eb_register' && !enableRegistration) {
    const url = new URL(window.location);
    url.searchParams.set('action', 'eb_login');
    window.history.pushState({}, '', url);
    setAction('eb_login');
  }

  return (
    <MantineProvider>
      {action === 'eb_register' ? (
        <Register
          customFields={customFields}
          enableTermsAndCond={enableTermsAndCond}
          termsAndCond={termsAndCond}
          enableRecaptcha={enableRecaptcha}
          recaptchaType={recaptchaType}
          recaptchaSiteKey={recaptchaSiteKey}
          showRecaptchaOnRegister={showRecaptchaOnRegister}
          onRegister={register}
          registrationError={registrationError}
          isRegistering={isRegistering}
        />
      ) : (
        <Login
          enableRegistration={enableRegistration}
          enableRecaptcha={enableRecaptcha}
          recaptchaType={recaptchaType}
          recaptchaSiteKey={recaptchaSiteKey}
          showRecaptchaOnLogin={showRecaptchaOnLogin}
          lostPasswordUrl={lostPasswordUrl}
          login={login}
          loginError={loginError}
          isLoggingIn={isLoggingIn}
          sso={sso}
        />
      )}
    </MantineProvider>
  );
}

export default AuthWrapper;
