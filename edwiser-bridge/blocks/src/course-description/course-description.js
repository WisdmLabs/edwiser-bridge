import { MantineProvider, Skeleton } from '@mantine/core';
import apiFetch from '@wordpress/api-fetch';
import { __, _n, sprintf } from '@wordpress/i18n';
import React, { useEffect, useState } from 'react';
import CourseContent, {
  CourseContentSkeleton,
} from './components/course-content';
import CourseMeta, { CourseMetaSkeleton } from './components/course-meta';
import RecommendedCourses, {
  RecommendedCoursesSkeleton,
} from './components/recommended-courses';
import { decodeHTMLEntities } from './utils';
import EmptyState from './components/empty-state';

function CourseDescription({ courseId, showRecommendedCourses }) {
  const [course, setCourse] = useState();
  const [isLoading, setIsLoading] = useState(true);

  // Fetch course data
  useEffect(() => {
    const fetchCourseById = async () => {
      setIsLoading(true);
      try {
        const course = await apiFetch({
          path: `/eb/api/v1/courses/${courseId}`,
        });

        setCourse(course);
      } catch (error) {
        console.error('Error fetching course:', error);
      } finally {
        setIsLoading(false);
        setTimeout(() => {
          const event = new CustomEvent('eb_course_btn_loaded');
          window.dispatchEvent(event);
        }, 1000);
      }
    };

    fetchCourseById();
  }, [courseId]);

  if (!isLoading && !course) {
    return (
      <div className="eb-course-desc__wrapper">
        <EmptyState />
      </div>
    );
  }

  return (
    <MantineProvider>
      <div className="eb-course-desc__wrapper">
        {isLoading ? (
          <Skeleton width={320} height={32} />
        ) : (
          <h1 className="eb-title">
            {__(decodeHTMLEntities(course?.title), 'edwiser-bridge')}
          </h1>
        )}
        <div className="eb-course-desc">
          <div className="eb-course-desc__details">
            <div className="eb-course-desc__course-image">
              {isLoading ? (
                <Skeleton width="100%" height="100%" />
              ) : (
                <img src={course?.thumbnail} alt={course?.title} />
              )}
            </div>
            {isLoading ? (
              <CourseMetaSkeleton />
            ) : (
              <CourseMeta
                courseCategory={course?.categories}
                courseAccess={
                  course?.course_expiry
                    ? course?.status === 'enrolled'
                      ? sprintf(
                          /* translators: %d is number of days left */
                          _n(
                            '%d Day Left',
                            '%d Days Left',
                            course.course_expires_after_days,
                            'edwiser-bridge'
                          ),
                          course.course_expires_after_days
                        )
                      : sprintf(
                          /* translators: %d is number of days */
                          _n(
                            '%d Day',
                            '%d Days',
                            course.course_expires_after_days,
                            'edwiser-bridge'
                          ),
                          course.course_expires_after_days
                        )
                    : __('Lifetime', 'edwiser-bridge')
                }
                courseStatus={course?.status}
                coursePrice={course?.price}
                courseCta={course?.course_cta}
              />
            )}
          </div>
          {isLoading ? (
            <CourseContentSkeleton />
          ) : (
            course?.content && <CourseContent content={course?.content} />
          )}
        </div>
        {isLoading ? (
          <RecommendedCoursesSkeleton />
        ) : (
          showRecommendedCourses &&
          course?.show_recommended_courses && (
            <RecommendedCourses courses={course?.recommended_courses} />
          )
        )}
      </div>
    </MantineProvider>
  );
}

export default CourseDescription;
