import { Skeleton } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { decodeHTMLEntities } from '../utils';
import { CoursePrice } from './course';
import { Icons } from './icons';

function CourseMeta({
  courseCategory,
  coursePrice,
  courseAccess,
  courseStatus,
  courseCta,
}) {
  const statusLabels = {
    enrolled: __('Enrolled', 'edwiser-bridge'),
    suspended: __('Suspended', 'edwiser-bridge'),
  };

  return (
    <div className="eb-course-desc__course-meta">
      <div className="course-meta__header">
        <h3>{__('Details', 'edwiser-bridge')}</h3>
      </div>
      <div className="course-meta__content">
        <div className="course-meta__content-top">
          {courseCategory.length > 0 && (
            <div className="course-meta__category">
              <div className="label">
                <Icons.grid />
                <span>{__('Category', 'edwiser-bridge')}</span>
              </div>
              <span className="value">
                {courseCategory.map((category, index) => (
                  <React.Fragment key={category?.id}>
                    {__(decodeHTMLEntities(category?.name), 'edwiser-bridge')}
                    {index < courseCategory.length - 1 ? ', ' : ''}
                  </React.Fragment>
                ))}
              </span>
            </div>
          )}
          <div className="course-meta__course-access">
            <div className="label">
              <Icons.clock />
              <span>{__('Course access', 'edwiser-bridge')}</span>
            </div>
            <span className="value">{__(courseAccess, 'edwiser-bridge')}</span>
          </div>
          {(courseStatus === 'enrolled' || courseStatus === 'suspended') && (
            <div className="course-meta__course-status">
              <div className="label">
                <Icons.status />
                <span>{__('Status', 'edwiser-bridge')}</span>
              </div>
              <span className="value">
                <span className={courseStatus}>
                  {statusLabels[courseStatus] || courseStatus}
                </span>
              </span>
            </div>
          )}
          {courseStatus !== 'enrolled' && coursePrice?.type !== 'closed' && (
            <div className="course-meta__price">
              <span className="label">{__('Price', 'edwiser-bridge')}</span>
              <span className="value">
                <CoursePrice price={coursePrice} />
              </span>
            </div>
          )}
        </div>
        <div
          className="course-meta__content-bottom"
          dangerouslySetInnerHTML={{ __html: courseCta }}
        />
      </div>
    </div>
  );
}

export default CourseMeta;

export function CourseMetaSkeleton() {
  return (
    <div className="eb-course-desc__course-meta">
      <div className="course-meta__header">
        <Skeleton width={60} height={24} />
      </div>
      <div className="course-meta__content">
        <div className="course-meta__content-top">
          <div className="course-meta__category">
            <Skeleton width={90} height={24} />
            <Skeleton width={60} height={24} />
          </div>
          <div className="course-meta__course-access">
            <Skeleton width={90} height={24} />
            <Skeleton width={60} height={24} />
          </div>
          <div className="course-meta__price">
            <Skeleton width={35} height={24} />
            <Skeleton width={50} height={24} />
          </div>
        </div>
        <Skeleton width={'100%'} height={40} />
      </div>
    </div>
  );
}
