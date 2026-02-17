import { Pagination, Skeleton } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React, { useMemo, useState } from 'react';
import CourseGrid, { CourseGridSkeleton } from './course-grid';
import CoursesControls, { CoursesControlsSkeleton } from './courses-controls';
import RecommendedCourses, {
  RecommendedCoursesSkeleton,
} from './recommended-courses';
import useMyCourses from '../../../hooks/use-my-courses';

export default function MyCourses({
  showCourseProgress,
  showRecommendedCourses,
  recommendedCoursesCount,
}) {
  const { enrolledCourses, recommendedCourses, coursesPageUrl, isLoading } =
    useMyCourses(recommendedCoursesCount);

  const [currentPage, setCurrentPage] = useState(1);
  const [sortOrder, setSortOrder] = useState('a-z');
  const [searchTerm, setSearchTerm] = useState('');

  // Filter and sort courses based on search term and sort order
  const filteredAndSortedCourses = useMemo(() => {
    let filtered = enrolledCourses;

    // Apply search filter
    if (searchTerm.trim()) {
      const searchLower = searchTerm.toLowerCase();
      filtered = enrolledCourses.filter(
        (course) =>
          (course.title || '').toLowerCase().includes(searchLower) ||
          (course.categories || []).some((cat) =>
            (cat.name || '').toLowerCase().includes(searchLower)
          )
      );
    }

    // Apply sorting
    const sorted = [...filtered].sort((a, b) => {
      switch (sortOrder) {
        case 'a-z':
          return a.title.localeCompare(b.title);
        case 'z-a':
          return b.title.localeCompare(a.title);
        case 'progress-high':
          return b.progress.percentage - a.progress.percentage;
        case 'progress-low':
          return a.progress.percentage - b.progress.percentage;
        default:
          return new Date(b.createdAt) - new Date(a.createdAt);
      }
    });

    return sorted;
  }, [enrolledCourses, searchTerm, sortOrder]);

  // Calculate pagination based on filtered results
  const itemsPerPage = 9;
  const totalPages = Math.ceil(filteredAndSortedCourses.length / itemsPerPage);

  // Reset to first page when search or sort changes
  React.useEffect(() => {
    setCurrentPage(1);
  }, [searchTerm, sortOrder]);

  // Calculate current page courses
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentCourses = filteredAndSortedCourses.slice(startIndex, endIndex);

  // Handle empty state
  const hasEnrolledCourses = enrolledCourses && enrolledCourses.length > 0;
  const hasRecommendedCourses =
    recommendedCourses && recommendedCourses.length > 0;

  // Loading state handling
  if (isLoading) {
    return (
      <MyCoursesSkeleton showRecommendedCourses={showRecommendedCourses} />
    );
  }

  return (
    <div className="eb-user-account__my-courses">
      <h2 className="eb-my-courses__title">
        {__('My Courses', 'edwiser-bridge')}
      </h2>
      {hasEnrolledCourses ? (
        <>
          <CoursesControls
            sortOrder={sortOrder}
            setSortOrder={setSortOrder}
            searchTerm={searchTerm}
            setSearchTerm={setSearchTerm}
          />
          <CourseGrid
            courses={currentCourses}
            searchTerm={searchTerm}
            showCourseProgress={showCourseProgress}
          />
          {filteredAndSortedCourses.length > 0 && (
            <div className="eb-my-courses__pagination">
              <span className="eb-my-courses__pagination-text">
                <>
                  {__('Showing', 'edwiser-bridge')} {startIndex + 1}{' '}
                  {__('to', 'edwiser-bridge')}{' '}
                  {Math.min(endIndex, filteredAndSortedCourses.length)}{' '}
                  {__('of', 'edwiser-bridge')} {filteredAndSortedCourses.length}{' '}
                  {__('entries', 'edwiser-bridge')}
                </>
              </span>
              <Pagination
                total={totalPages}
                value={currentPage}
                onChange={setCurrentPage}
              />
            </div>
          )}
        </>
      ) : (
        <div className="eb-my-courses__empty">
          <h4>{__('No courses found.', 'edwiser-bridge')}</h4>
          <p>
            {__('You are not enrolled in any courses yet.', 'edwiser-bridge')}
          </p>
        </div>
      )}
      {showRecommendedCourses && hasRecommendedCourses && (
        <RecommendedCourses
          courses={recommendedCourses}
          title={__('Recommended Courses', 'edwiser-bridge')}
          coursesPageUrl={coursesPageUrl}
        />
      )}
    </div>
  );
}

export const MyCoursesSkeleton = ({ showRecommendedCourses }) => {
  return (
    <div className="eb-user-account__my-courses">
      <h2 className="eb-my-courses__title">
        {__('My Courses', 'edwiser-bridge')}
      </h2>
      <CoursesControlsSkeleton />
      <CourseGridSkeleton />
      <div className="eb-my-courses__pagination">
        <Skeleton width={160} height={20} />
        <Skeleton width={240} height={32} />
      </div>
      {showRecommendedCourses && <RecommendedCoursesSkeleton />}
    </div>
  );
};
