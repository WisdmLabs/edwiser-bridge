import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  return (
    <div {...useBlockProps.save()}>
      <div
        id="eb-courses"
        data-page-title={attributes.pageTitle || ''}
        data-hide-title={attributes.hideTitle || false}
        data-hide-filters={attributes.hideFilters || false}
        data-courses-per-page={attributes.coursesPerPage || 9}
        data-categories={attributes.categories || ''}
        data-group-by-category={attributes.groupByCategory || false}
        data-category-per-page={attributes.categoryPerPage || 3}
        data-horizontal-scroll={attributes.horizontalScroll || false}
      ></div>
    </div>
  );
}
