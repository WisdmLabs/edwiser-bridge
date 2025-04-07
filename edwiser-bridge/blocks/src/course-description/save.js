import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  return (
    <div {...useBlockProps.save()}>
      <div
        id="eb-course-description"
        data-course-id={attributes.courseId}
        data-show-recommended-courses={attributes.showRecommendedCourses}
      ></div>
    </div>
  );
}
