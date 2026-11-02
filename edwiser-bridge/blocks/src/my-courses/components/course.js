import { __ } from '@wordpress/i18n';
import React from 'react';
import { Skeleton } from '@mantine/core';
import { Icons } from './icons';
import { decodeHTMLEntities } from '../utils';

function Course({ course, showCourseProgress }) {
  const getActionButton = () => {
    const progressPercentage = course.progress.percentage;

    if (course?.suspended) {
      return (
        <div className="course-suspended">
          <span>{__('Suspended', 'edwiser-bridge')}</span>
          <Icons.alert />
        </div>
      );
    } else if (progressPercentage >= 100) {
      return (
        <div className="course-completed">
          <span>{__('Completed', 'edwiser-bridge')}</span>
          <Icons.check />
        </div>
      );
    } else if (progressPercentage > 0) {
      return (
        <a href={course.progress.course_url} className="btn btn-resume">
          {__('Resume', 'edwiser-bridge')} <Icons.chevronRight />
        </a>
      );
    } else {
      return (
        <a href={course.progress.course_url} className="btn btn-start">
          {__('Start', 'edwiser-bridge')} <Icons.chevronRight />
        </a>
      );
    }
  };

  return (
    <a
      href={course.progress.course_url}
      target="_blank"
      className="eb-my-courses__course-anchor"
    >
      <div className="eb-my-courses__course-card">
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
                    {__(decodeHTMLEntities(category?.name), 'edwiser-bridge')}
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

            {showCourseProgress && (
              <div className="course__progress">
                {/* Activity Completion Status */}
                {/* <div className="activity-completion">
                  <span className="activity-text">
                    {__('Activity completed', 'edwiser-bridge')} :{' '}
                    <strong style={{ color: '#283b3c' }}>
                      {course.progress?.activitiesCompleted || 0}
                    </strong>
                    /{course.progress?.totalActivities || 0}
                  </span>
                </div> */}

                {/* Progress Bar */}
                <div className="progress-container">
                  <div className="progress-bar">
                    <div
                      className="progress-fill"
                      style={{ width: `${course.progress.percentage}%` }}
                    ></div>
                  </div>
                </div>

                {/* Course Completion Percentage */}
                <div className="course-completion">
                  <span className="completion-text">
                    {__('Course completed', 'edwiser-bridge')} :
                  </span>
                  <span className="completion-value">
                    {course.progress.percentage}%
                  </span>
                </div>
              </div>
            )}
          </div>

          {showCourseProgress && (
            <div className="course-details">{getActionButton()}</div>
          )}
        </div>
      </div>
    </a>
  );
}

export default Course;

export function CourseSkeleton() {
  return (
    <div className="eb-my-courses__course-card">
      <div className="course-thumbnail-container">
        <Skeleton height={140} style={{ borderRadius: 0 }} />
      </div>
      <div className="course-meta">
        <div className="course-content">
          <Skeleton height={20} width="90%" />
          <div className="course__progress">
            <Skeleton
              height={8}
              width="100%"
              mt={8}
              style={{ borderRadius: '12px' }}
            />
            <div className="course-completion" style={{ marginTop: 8 }}>
              <Skeleton height={18} width={120} />
              <Skeleton height={28} width={28} />
            </div>
          </div>
        </div>
        <div className="course-details">
          <Skeleton height={24} width={100} />
        </div>
      </div>
    </div>
  );
}
