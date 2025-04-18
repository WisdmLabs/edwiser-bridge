import { __ } from '@wordpress/i18n';
import React from 'react';
import { Skeleton } from '@mantine/core';
import { Icons } from './icons';

function Course({ course }) {
  return (
    <a href={course.link} target="_blank" className="eb-courses__course-anchor">
      <div className="eb-courses__course-card">
        <div className="course-thumbnail-container">
          <img
            src={course.thumbnail}
            alt={course.title}
            className="course-thumbnail"
          />
          <div className="course-category">
            <Icons.grid />
            <span>{__(course.category)}</span>
          </div>
        </div>
        <div className="course-meta">
          <div className="course-content">
            <h3 className="course-title">{__(course.title)}</h3>
            <p className="course-excerpt">{__(course.excerpt)}</p>
          </div>
          <div className="course-details">
            <div className="course-price">
              {course?.suspended ? (
                <span className="suspended">{__('Suspended')}</span>
              ) : (
                <CoursePrice price={course.price} />
              )}
            </div>
            <a href={course.link} className="btn">
              View Details
            </a>
          </div>
        </div>
      </div>
    </a>
  );
}

export default Course;

function CoursePrice({ price }) {
  // Price rendering logic
  if (price.enrolled) {
    return <span className="enrolled">{__('Enrolled')}</span>;
  }
  if (price.type === 'subscription') {
    return (
      <>
        <span className="price">{__('₹' + price.amount)}</span>
        <span className="recurring">{__('/month')}</span>
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
    return <span className="price">{__('Free')}</span>;
  }
  if (price.originalAmount !== null) {
    return (
      <>
        <span className="price">{__(price.currency + price.amount)}</span>
        <span className="original-price">
          {__(price.currency + price.originalAmount)}
        </span>
      </>
    );
  }

  return <span className="price">{__(price.currency + price.amount)}</span>;
}

export function CourseSkeleton() {
  return (
    <div className="eb-courses__course-card">
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
  );
}
