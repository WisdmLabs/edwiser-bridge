import React from 'react';
import { __ } from '@wordpress/i18n';
import { Icons } from '../icons';
import { useDashboard } from '../../hooks/use-dashboard';
import { Skeleton } from '@mantine/core';

function Dashboard() {
  const { user, isLoading } = useDashboard(window.location.href);

  if (isLoading) {
    return <DashboardSkeleton />;
  }

  return (
    <div className="eb-user-account__dashboard">
      <h3 className="eb-dashboard__title">
        {__('Dashboard', 'edwiser-bridge')}
      </h3>
      <div className="eb-dashboard__profle">
        <div className="eb-profile">
          <div className="eb-profile__avatar">
            <img src={user.avatar} alt={`${user.display_name}'s avatar`} />
          </div>
          <div className="eb-profile__name">
            {__('Welcome', 'edwiser-bridge')} {user.display_name}!
          </div>
        </div>
        <a href={user.logout_url} className="eb-profile__logout">
          {__('Sign out', 'edwiser-bridge')}
        </a>
      </div>
      <div className="eb-dashboard__course-summary">
        <div className="eb-course-summary eb-enrolled-courses">
          <div className="eb-course-summary__icon">
            <Icons.book />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              {user.course_statistics.total_enrolled}
            </span>
            <span className="eb-course-summary__status">
              {__('Enrolled courses', 'edwiser-bridge')}
            </span>
          </div>
        </div>
        <div className="eb-course-summary eb-completed-courses">
          <div className="eb-course-summary__icon">
            <Icons.squareCheck />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              {user.course_statistics.completed}
            </span>
            <span className="eb-course-summary__status">
              {__('Completed', 'edwiser-bridge')}
            </span>
          </div>
        </div>
        <div className="eb-course-summary eb-inprogress-courses">
          <div className="eb-course-summary__icon">
            <Icons.circleDashed />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              {user.course_statistics.in_progress}
            </span>
            <span className="eb-course-summary__status">
              {__('In progress', 'edwiser-bridge')}
            </span>
          </div>
        </div>
        <div className="eb-course-summary eb-notstarted-courses">
          <div className="eb-course-summary__icon">
            <Icons.circleMinus />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              {user.course_statistics.not_started}
            </span>
            <span className="eb-course-summary__status">
              {__('Not started', 'edwiser-bridge')}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;

export const DashboardSkeleton = () => {
  return (
    <div className="eb-user-account__dashboard">
      <h3 className="eb-dashboard__title">
        {__('Dashboard', 'edwiser-bridge')}
      </h3>
      <div className="eb-dashboard__profle">
        <div className="eb-profile">
          <div className="eb-profile__avatar">
            <Skeleton width={42} height={42} />
          </div>
          <Skeleton width={120} height={24} />
        </div>
        <Skeleton width={80} height={32} />
      </div>
      <div className="eb-dashboard__course-summary">
        <div className="eb-course-summary eb-enrolled-courses">
          <div className="eb-course-summary__icon">
            <Icons.book />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              <Skeleton width={32} height={24} />
            </span>
            <span className="eb-course-summary__status">
              {__('Enrolled courses', 'edwiser-bridge')}
            </span>
          </div>
        </div>
        <div className="eb-course-summary eb-completed-courses">
          <div className="eb-course-summary__icon">
            <Icons.squareCheck />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              <Skeleton width={32} height={24} />
            </span>
            <span className="eb-course-summary__status">
              {__('Completed', 'edwiser-bridge')}
            </span>
          </div>
        </div>
        <div className="eb-course-summary eb-inprogress-courses">
          <div className="eb-course-summary__icon">
            <Icons.circleDashed />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              <Skeleton width={32} height={24} />
            </span>
            <span className="eb-course-summary__status">
              {__('In progress', 'edwiser-bridge')}
            </span>
          </div>
        </div>
        <div className="eb-course-summary eb-notstarted-courses">
          <div className="eb-course-summary__icon">
            <Icons.circleMinus />
          </div>
          <div className="eb-course-summary__content">
            <span className="eb-course-summary__course-count">
              <Skeleton width={32} height={24} />
            </span>
            <span className="eb-course-summary__status">
              {__('Not started', 'edwiser-bridge')}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};
