import { __ } from '@wordpress/i18n';
import React from 'react';
import Course, { CourseSkeleton } from './course';
import { Skeleton } from '@mantine/core';

function RecommendedCourses({ courses }) {
  if (courses.length === 0) {
    return null;
  }

  return (
    <div className="eb-course-desc__recom-courses-wrapper">
      <h2 className="recom-courses-title">
        {__('Recommended courses', 'edwiser-bridge')}
      </h2>
      <div className="recom-courses">
        {courses.map((course) => (
          <Course key={course.id} course={course} />
        ))}
      </div>
    </div>
  );
}

export default RecommendedCourses;

export function RecommendedCoursesSkeleton() {
  return (
    <div className="eb-course-desc__recom-courses-wrapper">
      <Skeleton width={250} height={32} />
      <div className="recom-courses">
        {Array.from({ length: 3 }).map((_, index) => (
          <CourseSkeleton key={index} />
        ))}
      </div>
    </div>
  );
}
