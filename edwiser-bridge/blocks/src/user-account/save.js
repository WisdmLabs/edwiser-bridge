import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  return (
    <div {...useBlockProps.save()}>
      <div
        id="eb-user-account"
        data-page-title={attributes.pageTitle || ''}
        data-hide-page-title={attributes.hidePageTitle}
        data-show-course-progress={attributes.showCourseProgress}
        data-show-recommended-courses={attributes.showRecommendedCourses}
        data-recommended-courses-count={attributes.recommendedCoursesCount || 3}
      ></div>
    </div>
  );
}
