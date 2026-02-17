import { __ } from '@wordpress/i18n';
import React from 'react';
import Course, { CourseSkeleton } from './course';

function CourseGrid({ courses }) {
  return (
    <div className="eb-courses__grid">
      {courses.length > 0 ? (
        courses.map((course) => <Course key={course.ID} course={course} />)
      ) : (
        <p>{__('No courses found', 'edwiser-bridge')}</p>
      )}
    </div>
  );
}

export default CourseGrid;

export function CourseGridSkeleton({ coursesPerPage }) {
  return (
    <div className="eb-courses__grid">
      {Array.from({ length: coursesPerPage }).map((_, index) => (
        <CourseSkeleton key={index} />
      ))}
    </div>
  );
}
