import { __ } from '@wordpress/i18n';
import React from 'react';
import Course, { CourseSkeleton } from './course';

function CourseGrid({ courses, searchTerm = '', showCourseProgress }) {
  return (
    <div className="eb-my-courses__grid">
      {courses.length > 0 ? (
        courses.map((course) => (
          <Course
            key={course.id}
            course={course}
            showCourseProgress={showCourseProgress}
          />
        ))
      ) : (
        <div className="eb-my-courses__no-results">
          {searchTerm ? (
            <p>
              {__('No courses found matching', 'edwiser-bridge')}{' '}
              <strong>"{searchTerm}"</strong>
            </p>
          ) : (
            <p>{__('No courses found', 'edwiser-bridge')}</p>
          )}
        </div>
      )}
    </div>
  );
}

export default CourseGrid;

export function CourseGridSkeleton() {
  return (
    <div className="eb-my-courses__grid">
      {Array.from({ length: 9 }).map((_, index) => (
        <CourseSkeleton key={index} />
      ))}
    </div>
  );
}
