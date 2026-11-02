import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  return (
    <div {...useBlockProps.save()}>
      <div
        id="eb-dashboard"
        data-page-title={attributes.pageTitle || ''}
        data-hide-page-title={attributes.hidePageTitle}
      ></div>
    </div>
  );
}
