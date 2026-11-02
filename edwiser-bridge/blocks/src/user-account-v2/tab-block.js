import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useCallback, useEffect, useMemo } from 'react';
import IconOptions from './components/icon-options';
import { ICONS, getIconById } from './components/icons';

registerBlockType('edwiser-bridge/user-account-v2-tab', {
  title: 'Tab',
  icon: 'welcome-add-page',
  parent: ['edwiser-bridge/user-account-v2'],
  category: 'edwiser',
  attributes: {
    tabLabel: {
      type: 'string',
      default: '',
    },
    tabIcon: {
      type: 'string',
      default: 'layout-dashboard',
    },
    tabIndex: {
      type: 'number',
      default: 0,
    },
    tabClassName: {
      type: 'string',
      default: '',
    },
  },

  edit: ({ attributes, setAttributes, clientId }) => {
    const { tabLabel, tabIcon, tabIndex, tabClassName } = attributes;

    // Get parent block ID
    const parentBlockID = useSelect(
      (select) =>
        select('core/block-editor').getBlockParentsByBlockName(clientId, [
          'edwiser-bridge/user-account-v2',
        ])[0],
      [clientId]
    );

    const parentAttributes = useSelect(
      (select) => {
        if (!parentBlockID) return {};
        return select('core/block-editor').getBlockAttributes(parentBlockID);
      },
      [parentBlockID]
    );

    // Get current block index
    const currentBlockIndex = useSelect(
      (select) => {
        if (!parentBlockID) return 0;
        return select('core/block-editor')
          .getBlockOrder(parentBlockID)
          .indexOf(clientId);
      },
      [parentBlockID, clientId]
    );

    // Update block index when it changes
    useEffect(() => {
      if (currentBlockIndex !== tabIndex) {
        setAttributes({ tabIndex: currentBlockIndex });
      }
    }, [currentBlockIndex, tabIndex, setAttributes]);

    // Move tab content to content area
    useEffect(() => {
      const contentArea = document.querySelector(
        '.eb-user-account-v2__tabs-content'
      );
      if (!contentArea) return;

      const tabContent = document.querySelector(
        `.eb-user-account-v2__tab-panel[data-tab-index="${tabIndex}"]`
      );

      if (tabContent) {
        contentArea.appendChild(tabContent);
      }
    }, [tabIndex, clientId]);

    // Memoized icon component
    const tabIconComponent = useMemo(() => {
      const iconData = getIconById(tabIcon);
      const IconComponent = iconData?.icon;
      return IconComponent ? (
        <span className="eb-user-account-v2__tab-icon">
          <IconComponent />
        </span>
      ) : null;
    }, [tabIcon]);

    // Memoized handlers
    const handleTabLabelChange = useCallback(
      (newTabLabel) => {
        setAttributes({ tabLabel: newTabLabel });
      },
      [setAttributes]
    );

    const handleIconSelect = useCallback(
      (iconId) => {
        setAttributes({ tabIcon: iconId });
      },
      [setAttributes]
    );

    const handleTabClassNameChange = useCallback(
      (value) => {
        setAttributes({ tabClassName: value });
      },
      [setAttributes]
    );

    const tabName = attributes.tabLabel
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '-');

    return (
      <div
        className={
          tabIndex === 0
            ? `eb-user-account-v2__tab active ${tabName} ${attributes.tabClassName}`
            : `eb-user-account-v2__tab ${tabName} ${attributes.tabClassName}`
        }
        aria-selected={tabIndex === 0 ? 'true' : 'false'}
        aria-controls={tabName}
        data-tab-index={tabIndex}
      >
        {!parentAttributes.hideTabIcons && tabIconComponent}
        <TextControl
          className="eb-user-account-v2__tab-label"
          value={tabLabel}
          onChange={handleTabLabelChange}
          placeholder="Add Tab Label"
          type="text"
        />
        <div
          className={`eb-user-account-v2__tab-panel ${tabName} ${attributes.tabClassName}`}
          data-tab-index={attributes.tabIndex}
        >
          <InnerBlocks />
        </div>
        <InspectorControls>
          <PanelBody title="Icon Settings">
            <IconOptions
              onSelectTabIcon={handleIconSelect}
              icons={ICONS}
              tabIcon={tabIcon}
            />
          </PanelBody>
          <PanelBody title="Classnames for tab">
            <TextControl
              value={tabClassName}
              onChange={handleTabClassNameChange}
            />
          </PanelBody>
        </InspectorControls>
      </div>
    );
  },
  save: ({ attributes }) => {
    const tabName = attributes.tabLabel
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '-');

    return (
      <div
        className={`eb-user-account-v2__tab-panel ${tabName} ${attributes.tabClassName}`}
        data-tab-index={attributes.tabIndex}
        data-tab-name={tabName}
      >
        <InnerBlocks.Content />
      </div>
    );
  },
});

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function (event) {
  document.addEventListener('click', function (e) {
    if (e.target.closest('.eb-user-account-v2__tab')) {
      const clickedTab = e.target.closest('.eb-user-account-v2__tab');

      // Remove active class from all tabs
      document.querySelectorAll('.eb-user-account-v2__tab').forEach((tab) => {
        tab.classList.remove('active');
      });

      clickedTab.classList.add('active');

      const tabindex = clickedTab.getAttribute('data-tab-index');

      // Hide all tab content
      document
        .querySelectorAll('.eb-user-account-v2__tabs-content > div')
        .forEach((panel) => {
          panel.style.display = 'none';
        });

      // Show selected tab content
      const targetPanel = document.querySelector(
        '.eb-user-account-v2__tabs-content .eb-user-account-v2__tab-panel[data-tab-index="' +
          tabindex +
          '"]'
      );

      if (targetPanel) {
        targetPanel.style.display = 'block';
      }
    }
  });
});
