import React from 'react';
import { createRoot } from '@wordpress/element';
import AuthWrapper from './components/auth/auth-wrapper';

function onReady() {
  if (window.__EB_UA_V2_INIT__) return;
  window.__EB_UA_V2_INIT__ = true;
  initializeBlock();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', onReady);
} else {
  onReady();
}

// Main initialization function
function initializeBlock() {
  checkAuthAndInitialize();
}

// Check authentication status and initialize appropriate interface
function checkAuthAndInitialize() {
  const urlParams = new URLSearchParams(window.location.search);
  const redirectTo = urlParams.get('redirect_to');
  const isEnroll = urlParams.get('is_enroll');

  const query = new URLSearchParams();
  if (redirectTo) query.append('redirect_to', redirectTo);
  if (isEnroll) query.append('is_enroll', isEnroll);

  fetch(`/wp-json/eb/api/v1/user-account/auth?${query.toString()}`)
    .then((r) => r.json())
    .then((authData) => {
      const isLoggedIn = authData && authData.is_logged_in;

      if (isLoggedIn) {
        // User is logged in, initialize tab interface
        initializeTabInterface();
      } else {
        // User is not logged in, show auth interface
        showAuthInterface();
      }
    })
    .catch((error) => {
      console.error('Error checking auth status:', error);
      // On error, fallback to showing auth interface
      showAuthInterface();
    });
}

// Show authentication interface
function showAuthInterface() {
  const wrapper = document.querySelector(
    '.wp-block-edwiser-bridge-user-account-v2'
  );
  if (!wrapper) return;

  // Clear existing content
  wrapper.innerHTML = '';

  // Create auth wrapper container
  const authWrapper = document.createElement('div');
  authWrapper.className = 'eb-user-account-v2__auth-wrapper';
  wrapper.appendChild(authWrapper);

  // Mount auth component
  const root = createRoot(authWrapper);
  root.render(
    React.createElement(AuthWrapper, {
      onAuthSuccess: handleAuthSuccess,
    })
  );
}

// Handle successful authentication
function handleAuthSuccess() {
  const wrapper = document.querySelector(
    '.wp-block-edwiser-bridge-user-account-v2'
  );
  if (!wrapper) return;

  wrapper.innerHTML = '';
  initializeTabInterface();
}

// Initialize tab interface
function initializeTabInterface() {
  initializeTabPanels();
  handleUrlTabSelection();
  wireTabAndToggleClicks();
}

function wireTabAndToggleClicks() {
  document.addEventListener('click', function (e) {
    if (e.target.closest('.eb-user-account-v2__tab')) {
      const clickedTab = e.target.closest('.eb-user-account-v2__tab');
      const tabName = clickedTab.getAttribute('aria-controls');
      switchToTab(clickedTab);
      updateUrl(tabName);
    }
    if (e.target.closest('.eb-user-account-v2__tabs-toggle')) {
      const toggleButton = e.target.closest('.eb-user-account-v2__tabs-toggle');
      toggleTabs(toggleButton);
    }
  });
}

// Initialize tab panels - hide all except first one or URL-specified tab
function initializeTabPanels() {
  const wrapper = document.querySelector('.eb-user-account-v2__wrapper');
  wrapper.style.display = 'flex';

  const tabPanels = document.querySelectorAll('.eb-user-account-v2__tab-panel');
  const urlTab = getUrlTabName();

  // Check if URL tab exists
  let validUrlTab = false;
  if (urlTab) {
    validUrlTab = Array.from(tabPanels).some(
      (panel) => panel.getAttribute('data-tab-name') === urlTab
    );
  }

  tabPanels.forEach((panel, index) => {
    const panelTabName = panel.getAttribute('data-tab-name');
    const shouldShow = validUrlTab ? panelTabName === urlTab : index === 0;

    if (shouldShow) {
      panel.style.display = 'block';
      panel.classList.add('active');
    } else {
      panel.style.display = 'none';
      panel.classList.remove('active');
    }
  });
}

// Handle URL-based tab selection
function handleUrlTabSelection() {
  const urlTab = getUrlTabName();
  if (!urlTab) return;

  // Find tab with matching aria-controls
  const targetTab = document.querySelector(
    `.eb-user-account-v2__tab[aria-controls="${urlTab}"]`
  );
  if (targetTab) {
    switchToTab(targetTab);
  } else {
    // Invalid tab name - fallback to first tab
    const firstTab = document.querySelector('.eb-user-account-v2__tab');
    if (firstTab) {
      switchToTab(firstTab);
      // Update URL to remove invalid parameter
      updateUrl(firstTab.getAttribute('aria-controls'));
    }
  }
}

// Get tab name from URL parameter
function getUrlTabName() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get('tab');
}

// Switch to specific tab
function switchToTab(targetTab) {
  // Remove active class from all tabs
  document.querySelectorAll('.eb-user-account-v2__tab').forEach((tab) => {
    tab.classList.remove('active');
    tab.setAttribute('aria-selected', 'false');
  });

  // Add active class to target tab
  targetTab.classList.add('active');
  targetTab.setAttribute('aria-selected', 'true');

  // Get tab name and index
  const tabName = targetTab.getAttribute('aria-controls');
  const tabIndex = targetTab.getAttribute('data-tab-index');

  // Hide all tab panels
  document
    .querySelectorAll('.eb-user-account-v2__tab-panel')
    .forEach((panel) => {
      panel.style.display = 'none';
      panel.classList.remove('active');
    });

  // Show target tab panel
  const targetPanel = document.querySelector(
    `.eb-user-account-v2__tab-panel[data-tab-index="${tabIndex}"]`
  );

  if (targetPanel) {
    targetPanel.style.display = 'block';
    targetPanel.classList.add('active');
  }

  // Close mobile menu if it's open
  closeMobileMenu(targetTab);
}

// Update URL without page reload
function updateUrl(tabName) {
  const url = new URL(window.location);
  url.searchParams.set('tab', tabName);
  window.history.pushState({}, '', url);
}

// Toggle tabs
function toggleTabs(button) {
  const isActive = button.getAttribute('data-active') === 'true';
  const tabsList = button
    .closest('.eb-user-account-v2__tabs')
    .querySelector('.eb-user-account-v2__tabs-list');

  const newState = !isActive;
  button.setAttribute('data-active', newState);
  tabsList.setAttribute('data-visible', newState);
}

// Close mobile menu
function closeMobileMenu(targetTab) {
  // Find the toggle button in the same tabs container
  const tabsContainer = targetTab.closest('.eb-user-account-v2__tabs');
  if (!tabsContainer) return;

  const toggleButton = tabsContainer.querySelector(
    '.eb-user-account-v2__tabs-toggle'
  );
  const tabsList = tabsContainer.querySelector(
    '.eb-user-account-v2__tabs-list'
  );

  if (toggleButton && tabsList) {
    // Check if menu is currently open
    const isMenuOpen = toggleButton.getAttribute('data-active') === 'true';

    if (isMenuOpen) {
      // Close the menu
      toggleButton.setAttribute('data-active', 'false');
      tabsList.setAttribute('data-visible', 'false');
    }
  }
}
