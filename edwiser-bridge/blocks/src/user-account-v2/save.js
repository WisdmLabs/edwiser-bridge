import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { Icons, getIconById } from './components/icons';

export default function save({ attributes }) {
  const {
    tabLabelsArray,
    tabIconsArray,
    tabClassnamesArray,
    hidePageTitle,
    pageTitle,
    hideTabIcons,
  } = attributes;
  const renderTabLabels = () => {
    return tabLabelsArray.map((label, i) => {
      const iconId = tabIconsArray[i];
      const tabIcon = getIconById(iconId);
      const tabClassName = tabClassnamesArray[i];
      const tabName = label.trim().toLowerCase().replace(/\s+/g, '-');

      return (
        <div
          key={i}
          className={
            i === 0
              ? `eb-user-account-v2__tab active ${tabName} ${tabClassName}`
              : `eb-user-account-v2__tab ${tabName} ${tabClassName}`
          }
          role="tab"
          aria-selected={i === 0 ? 'true' : 'false'}
          aria-controls={tabName}
          data-tab-index={i}
        >
          {tabIcon?.icon && !hideTabIcons && (
            <span className="eb-user-account-v2__tab-icon">
              <tabIcon.icon />
            </span>
          )}
          <span className="eb-user-account-v2__tab-label">{label}</span>
        </div>
      );
    });
  };

  return (
    <div {...useBlockProps.save()}>
      <div className="eb-user-account-v2__wrapper">
        <div className="eb-user-account-v2__tabs">
          <div className="eb-user-account-v2__tabs-title-wrapper">
            {!hidePageTitle && (
              <h3 className="eb-user-account-v2__tabs-title">{pageTitle}</h3>
            )}
            <button
              className="eb-user-account-v2__tabs-toggle"
              data-active={false}
            >
              <span className="tabs-toggle__menu">
                <Icons.menu />
              </span>
              <span className="tabs-toggle__close">
                <Icons.close />
              </span>
            </button>
          </div>
          <div className="eb-user-account-v2__tabs-list" data-visible={false}>
            {renderTabLabels()}
          </div>
        </div>
        <div className="eb-user-account-v2__tabs-content">
          <InnerBlocks.Content />
        </div>
      </div>
    </div>
  );
}
