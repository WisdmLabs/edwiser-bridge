import { MantineProvider, Skeleton } from '@mantine/core';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import React, { useEffect, useState } from 'react';
import CourseContent, {
  CourseContentSkeleton,
} from './components/course-content';
import CourseMeta, { CourseMetaSkeleton } from './components/course-meta';
import RecommendedCourses, {
  RecommendedCoursesSkeleton,
} from './components/recommended-courses';

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
      }
    };

    fetchCourseById();
  }, [courseId]);

  return (
    <MantineProvider>
      <div className="eb-course-desc__wrapper">
        {isLoading ? (
          <Skeleton width={320} height={32} />
        ) : (
          <h1 className="eb-title">{__(course?.title)}</h1>
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
                courseCategory={course?.category}
                courseAccess={
                  course?.course_expiry
                    ? course?.course_expires_after_days + ' Days'
                    : 'Lifetime'
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
            <CourseContent content={course?.content} />
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
