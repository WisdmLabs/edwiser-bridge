import { MantineProvider, Skeleton, Tabs } from '@mantine/core';
import { useMediaQuery } from '@mantine/hooks';
import React, { useState, useEffect } from 'react';
import { Icons } from './components/icons';
import ResponsiveTabMenu from './components/responsive-tab-menu';
import Dashboard from './components/tabs/dashboard';
import Orders from './components/tabs/orders';
import MyCourses from './components/tabs/my-courses';
import Profile from './components/tabs/profile';
import { __ } from '@wordpress/i18n';
import Login from './components/login';
import Register from './components/register';
import { useAuth } from './hooks/use-auth';

function UserAccount({
  pageTitle,
  hidePageTitle,
  showCourseProgress,
  showRecommendedCourses,
  recommendedCoursesCount,
}) {
  const isMobile = useMediaQuery('(max-width: 1023px)');
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

  // Get initial tab from URL or default to dashboard
  const getInitialTab = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const validTabs = ['dashboard', 'profile', 'orders', 'my-courses'];
    return validTabs.includes(tabParam) ? tabParam : 'dashboard';
  };

  const [activeTab, setActiveTab] = useState(getInitialTab);

  // Get current action from URL
  const getCurrentAction = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('action');
  };

  const [currentAction, setCurrentAction] = useState(getCurrentAction);

  // Update URL when tab changes
  const updateURL = (tab) => {
    const url = new URL(window.location);
    if (tab === 'dashboard') {
      url.searchParams.delete('tab'); // Remove tab param for dashboard (cleaner URL)
    } else {
      url.searchParams.set('tab', tab);
    }
    window.history.pushState({}, '', url);
  };

  // Listen for browser back/forward button
  useEffect(() => {
    const handlePopState = () => {
      const newTab = getInitialTab();
      const newAction = getCurrentAction();
      setActiveTab(newTab);
      setCurrentAction(newAction);
    };

    window.addEventListener('popstate', handlePopState);
    return () => window.removeEventListener('popstate', handlePopState);
  }, []);

  const handleTabChange = (value) => {
    setActiveTab(value);
    updateURL(value);
  };

  // Show loading state
  if (isLoading) {
    return (
      <MantineProvider>
        <Skeleton height={500} width="100%" />
      </MantineProvider>
    );
  }

  // Show login/register forms if not logged in
  if (!isLoggedIn) {
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');

    // Clean up URL by removing tab parameter and setting action if needed
    const url = new URL(window.location);
    url.searchParams.delete('tab'); // Remove tab parameter

    // If registration is disabled but user tries to access register page, redirect to login
    if (action === 'eb_register' && !enableRegistration) {
      url.searchParams.set('action', 'eb_login');
      window.history.pushState({}, '', url);
      return (
        <MantineProvider>
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
        </MantineProvider>
      );
    }

    if (!action) {
      url.searchParams.set('action', 'eb_login');
    }

    // Update URL if it changed
    if (url.search !== window.location.search) {
      window.history.pushState({}, '', url);
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

  return (
    <MantineProvider>
      <div className="eb-user-account__wrapper">
        <Tabs
          className="eb-user-account__tabs"
          orientation="vertical"
          value={activeTab}
          onChange={handleTabChange}
        >
          <Tabs.List>
            <div className="eb-user-account__tabs-title-wrapper">
              {!hidePageTitle && (
                <h3 className="eb-user-account__tabs-title">{pageTitle}</h3>
              )}
              {isMobile && (
                <ResponsiveTabMenu
                  activeTab={activeTab}
                  onTabChange={handleTabChange}
                />
              )}
            </div>

            {!isMobile && (
              <>
                <Tabs.Tab value="dashboard" leftSection={<Icons.layout />}>
                  {__('Dashboard', 'edwiser-bridge')}
                </Tabs.Tab>
                <Tabs.Tab value="profile" leftSection={<Icons.user />}>
                  {__('Profile', 'edwiser-bridge')}
                </Tabs.Tab>
                <Tabs.Tab value="orders" leftSection={<Icons.orders />}>
                  {__('Orders', 'edwiser-bridge')}
                </Tabs.Tab>
                <Tabs.Tab value="my-courses" leftSection={<Icons.book />}>
                  {__('My Courses', 'edwiser-bridge')}
                </Tabs.Tab>
              </>
            )}
          </Tabs.List>

          <Tabs.Panel value="dashboard">
            <Dashboard />
          </Tabs.Panel>

          <Tabs.Panel value="profile">
            <Profile />
          </Tabs.Panel>

          <Tabs.Panel value="orders">
            <Orders />
          </Tabs.Panel>

          <Tabs.Panel value="my-courses">
            <MyCourses
              showCourseProgress={showCourseProgress}
              showRecommendedCourses={showRecommendedCourses}
              recommendedCoursesCount={recommendedCoursesCount}
            />
          </Tabs.Panel>
        </Tabs>
      </div>
    </MantineProvider>
  );
}

export default UserAccount;
