import {
  InnerBlocks,
  InspectorControls,
  useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Icons } from './components/icons';
import './editor.scss';
import './tab-block';

export default function Edit({ attributes, setAttributes, clientId }) {
  const {
    tabLabelsArray,
    tabIconsArray,
    tabClassnamesArray,
    hidePageTitle,
    pageTitle,
    hideTabIcons,
  } = attributes;
  const tabsContentRef = useRef(null);
  const previousTabCount = useRef(0);
  const observerRef = useRef(null);
  const timeoutRef = useRef(null);
  const [isTabsVisible, setIsTabsVisible] = useState(false);

  const childBlocks = useSelect(
    (select) => select('core/block-editor').getBlocks(clientId),
    [clientId]
  );

  const tabData = useMemo(() => {
    const tabLabels = [];
    const tabIcons = [];
    const tabClassnames = [];

    childBlocks.forEach((block, index) => {
      const attrs = block.attributes;
      tabLabels.push(attrs.tabLabel || '');
      tabIcons.push(attrs.tabIcon || 'layout-dashboard');
      tabClassnames.push(attrs.tabClassName || '');
    });

    return { tabLabels, tabIcons, tabClassnames };
  }, [childBlocks]);

  // Check if data has changed to update attributes
  const hasDataChanged = useMemo(() => {
    // More efficient comparison using length and shallow comparison
    if (
      tabData.tabLabels.length !== tabLabelsArray.length ||
      tabData.tabIcons.length !== tabIconsArray.length ||
      tabData.tabClassnames.length !== tabClassnamesArray.length
    ) {
      return true;
    }

    // Shallow comparison for arrays
    return (
      tabData.tabLabels.some(
        (label, index) => label !== tabLabelsArray[index]
      ) ||
      tabData.tabIcons.some((icon, index) => icon !== tabIconsArray[index]) ||
      tabData.tabClassnames.some(
        (className, index) => className !== tabClassnamesArray[index]
      )
    );
  }, [tabData, tabLabelsArray, tabIconsArray, tabClassnamesArray]);

  useEffect(() => {
    if (hasDataChanged) {
      setAttributes({
        tabLabelsArray: tabData.tabLabels,
        tabIconsArray: tabData.tabIcons,
        tabClassnamesArray: tabData.tabClassnames,
      });
    }
  }, [hasDataChanged, tabData, setAttributes]);

  // Ensure first tab is active on editor load/refresh
  useEffect(() => {
    if (tabsContentRef.current && childBlocks.length > 0) {
      const tabsContent = tabsContentRef.current;

      // Remove active class from all tabs
      document.querySelectorAll('.eb-user-account-v2__tab').forEach((tab) => {
        tab.classList.remove('active');
      });

      // Hide all tab content
      tabsContent
        .querySelectorAll(
          '.eb-user-account-v2__tab-panel, .eb-user-account-v2__tab-content'
        )
        .forEach((panel) => {
          panel.style.display = 'none';
        });

      // Activate first tab
      const firstTab = document.querySelector(
        '.eb-user-account-v2__tab[data-tab-index="0"]'
      );
      const firstTabContent = tabsContent.querySelector(
        '.eb-user-account-v2__tab-panel[data-tab-index="0"]'
      );

      if (firstTab) {
        firstTab.classList.add('active');
      }
      if (firstTabContent) {
        firstTabContent.style.display = 'block';
      }
    }
  }, [childBlocks.length]);

  // Tab management: Handle tab add/delete
  useEffect(() => {
    if (!tabsContentRef.current) return;

    const currentTabCount = childBlocks.length;
    const tabsContent = tabsContentRef.current;

    // Cleanup previous observer and timeout
    const cleanup = () => {
      if (observerRef.current) {
        observerRef.current.disconnect();
        observerRef.current = null;
      }
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
        timeoutRef.current = null;
      }
    };

    const activateTab = (tabIndex) => {
      const findAndActivateTab = () => {
        const allTabs = document.querySelectorAll('.eb-user-account-v2__tab');
        const allTabContent = tabsContent.querySelectorAll(
          '.eb-user-account-v2__tab-panel, .eb-user-account-v2__tab-content'
        );

        allTabs.forEach((tab) => {
          tab.classList.remove('active');
        });

        allTabContent.forEach((tab) => {
          tab.style.display = 'none';
        });

        const targetTabContent = tabsContent.querySelector(
          `[data-tab-index="${tabIndex}"]`
        );
        const targetTab = document.querySelector(
          `.eb-user-account-v2__tab[data-tab-index="${tabIndex}"]`
        );

        if (targetTabContent) {
          targetTabContent.style.display = 'block';
        }
        if (targetTab) {
          targetTab.classList.add('active');
          return true;
        }
        return false;
      };

      // Try immediately first
      if (findAndActivateTab()) {
        return;
      }

      cleanup();

      // If not found, use MutationObserver to wait for DOM updates
      const observer = new MutationObserver((mutations) => {
        if (findAndActivateTab()) {
          cleanup();
        }
      });

      observerRef.current = observer;

      const tabsContainer = document.querySelector(
        '.eb-user-account-v2__tabs-list'
      );
      if (tabsContainer) {
        observer.observe(tabsContainer, {
          childList: true,
          subtree: true,
          attributes: true,
          attributeFilter: ['data-tab-index'],
        });

        // Fallback timeout
        timeoutRef.current = setTimeout(() => {
          cleanup();
        }, 1000);
      }
    };

    // Only process if tab count actually changed
    if (currentTabCount !== previousTabCount.current) {
      // If tab was added - make new tab active
      if (currentTabCount > previousTabCount.current) {
        activateTab(currentTabCount - 1);
      }
      // If tab was deleted - clean up orphaned panels and make first tab active
      else if (currentTabCount < previousTabCount.current) {
        // Remove orphaned tab panels from DOM
        const allTabPanels = tabsContent.querySelectorAll(
          '.eb-user-account-v2__tab-panel, .eb-user-account-v2__tab-content'
        );
        allTabPanels.forEach((panel) => {
          const tabIndex = parseInt(panel.getAttribute('data-tab-index'));
          if (tabIndex >= currentTabCount) {
            panel.remove();
          }
        });

        // Activate first tab if any tabs remain
        if (currentTabCount > 0) {
          activateTab(0);
        }
      }

      previousTabCount.current = currentTabCount;
    }

    // Cleanup on unmount
    return cleanup;
  }, [childBlocks.length]);

  return (
    <div {...useBlockProps()}>
      <div className="eb-user-account-v2__wrapper" style={{ display: 'flex' }}>
        <div className="eb-user-account-v2__tabs">
          <div className="eb-user-account-v2__tabs-title-wrapper">
            {!hidePageTitle && (
              <h3 className="eb-user-account-v2__tabs-title">{pageTitle}</h3>
            )}
            <button
              className="eb-user-account-v2__tabs-toggle"
              data-active={isTabsVisible}
              onClick={() => setIsTabsVisible(!isTabsVisible)}
            >
              {isTabsVisible ? (
                <span className="tabs-toggle__close">
                  <Icons.close />
                </span>
              ) : (
                <span className="tabs-toggle__menu">
                  <Icons.menu />
                </span>
              )}
            </button>
          </div>
          <div
            className="eb-user-account-v2__tabs-list"
            data-visible={isTabsVisible}
          >
            <InnerBlocks
              allowedBlocks={['edwiser-bridge/user-account-v2-tab']}
              template={[
                [
                  'edwiser-bridge/user-account-v2-tab',
                  { tabLabel: 'Dashboard', tabIcon: 'layout-dashboard' },
                  [['edwiser-bridge/dashboard']],
                ],
                [
                  'edwiser-bridge/user-account-v2-tab',
                  { tabLabel: 'Profile', tabIcon: 'user' },
                  [['edwiser-bridge/profile']],
                ],
                [
                  'edwiser-bridge/user-account-v2-tab',
                  { tabLabel: 'Orders', tabIcon: 'package' },
                  [['edwiser-bridge/orders']],
                ],
                [
                  'edwiser-bridge/user-account-v2-tab',
                  { tabLabel: 'My Courses', tabIcon: 'graduation-cap' },
                  [['edwiser-bridge/my-courses']],
                ],
              ]}
              renderAppender={InnerBlocks.ButtonBlockAppender}
            />
          </div>
        </div>
        <div
          className="eb-user-account-v2__tabs-content"
          ref={tabsContentRef}
        ></div>
      </div>
      <InspectorControls>
        <PanelBody
          title={__('User Account Settings', 'edwiser-bridge')}
          initialOpen={true}
        >
          <ToggleControl
            label={__('Hide Page Title', 'edwiser-bridge')}
            checked={hidePageTitle}
            onChange={(value) => setAttributes({ hidePageTitle: value })}
          />
          <fieldset style={{ marginTop: '8px' }}>
            <TextControl
              label={__('Page Title', 'edwiser-bridge')}
              type="text"
              value={pageTitle}
              onChange={(value) => setAttributes({ pageTitle: value })}
            />
          </fieldset>{' '}
          <ToggleControl
            label={__('Hide Tab Icons', 'edwiser-bridge')}
            checked={hideTabIcons}
            onChange={(value) => setAttributes({ hideTabIcons: value })}
          />
        </PanelBody>
      </InspectorControls>
    </div>
  );
}
