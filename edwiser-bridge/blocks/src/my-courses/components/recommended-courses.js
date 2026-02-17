import { __ } from '@wordpress/i18n';
import React from 'react';
import { Icons } from './icons';
import { decodeHTMLEntities } from '../utils';
import { Skeleton } from '@mantine/core';

function RecommendedCourses({ courses, title, coursesPageUrl }) {
  if (!courses || courses.length === 0) {
    return null;
  }

  return (
    <div className="eb-my-courses__recommended-courses">
      <h3 className="eb-my-courses__recommended-courses-title">{title}</h3>
      <div className="eb-my-courses__recommended-courses-grid">
        {courses.map((course) => (
          <a
            href={course.link}
            target="_blank"
            className="eb-courses__course-anchor"
            key={course.ID}
          >
            <div className="eb-courses__course-card">
              <div className="course-thumbnail-container">
                <img
                  src={course.thumbnail}
                  alt={course.title}
                  className="course-thumbnail"
                />
                {course.categories.length > 0 && (
                  <div className="course-category">
                    <Icons.grid />
                    <span>
                      {course.categories.map((category, index) => (
                        <React.Fragment key={category?.id}>
                          {__(
                            decodeHTMLEntities(category?.name),
                            'edwiser-bridge'
                          )}
                          {index < course.categories.length - 1 ? ', ' : ''}
                        </React.Fragment>
                      ))}
                    </span>
                  </div>
                )}
              </div>
              <div className="course-meta">
                <div className="course-content">
                  <h3 className="course-title">
                    {__(course.title, 'edwiser-bridge')}
                  </h3>
                  <p className="course-excerpt">
                    {__(course.excerpt, 'edwiser-bridge')}
                  </p>
                </div>
                <div className="course-details">
                  <div className="course-price">
                    {course?.suspended ? (
                      <span className="suspended">
                        {__('Suspended', 'edwiser-bridge')}
                      </span>
                    ) : (
                      <CoursePrice price={course.price} />
                    )}
                  </div>
                  <a href={course.link} className="btn">
                    {__('View Details', 'edwiser-bridge')}
                  </a>
                </div>
              </div>
            </div>
          </a>
        ))}
      </div>
      <div className="eb-my-courses__recommended-courses-view-more">
        <a href={coursesPageUrl} className="eb-btn__view-more">
          {__('View more courses', 'edwiser-bridge')}
          <Icons.chevronRight />
        </a>
      </div>
    </div>
  );
}

export default RecommendedCourses;

function CoursePrice({ price }) {
  // Price rendering logic
  if (price.enrolled) {
    return <span className="enrolled">{__('Enrolled', 'edwiser-bridge')}</span>;
  }
  if (price.type === 'subscription') {
    return (
      <>
        <span className="price">
          {__('₹' + price.amount, 'edwiser-bridge')}
        </span>
        <span className="recurring">{__('/month', 'edwiser-bridge')}</span>
      </>
    );
  }
  if (price.type === 'closed') {
    return (
      <>
        <span></span>
      </>
    );
  }
  if (price.amount === 0) {
    return <span className="price">{__('Free', 'edwiser-bridge')}</span>;
  }
  if (price.originalAmount !== null) {
    return (
      <>
        <span className="price">
          {__(price.currency + price.amount, 'edwiser-bridge')}
        </span>
        <span className="original-price">
          {__(price.currency + price.originalAmount, 'edwiser-bridge')}
        </span>
      </>
    );
  }

  return (
    <span className="price">
      {__(price.currency + price.amount, 'edwiser-bridge')}
    </span>
  );
}

export const RecommendedCoursesSkeleton = () => {
  return (
    <div className="eb-my-courses__recommended-courses">
      <h3 className="eb-my-courses__recommended-courses-title">
        {__('Recommended Courses', 'edwiser-bridge')}
      </h3>
      <div className="eb-my-courses__recommended-courses-grid">
        {Array.from({ length: 3 }).map((_, index) => (
          <div className="eb-courses__course-card" key={index}>
            <div className="course-thumbnail-container">
              <Skeleton height={140} style={{ borderRadius: 0 }} />
            </div>
            <div className="course-meta">
              <div className="course-content">
                <Skeleton height={20} width="90%" />
                <div>
                  <Skeleton height={12} />
                  <Skeleton height={12} mt={6} width="70%" />
                </div>
              </div>
              <div className="course-details">
                <div className="course-price">
                  <Skeleton height={28} width={80} />
                </div>
                <Skeleton height={18} width={80} />
              </div>
            </div>
          </div>
        ))}
      </div>
      <div className="eb-my-courses__recommended-courses-view-more">
        <Skeleton width={130} height={20} />
      </div>
    </div>
  );
};
