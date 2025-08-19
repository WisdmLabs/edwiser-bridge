import React from 'react';
import ReactDOM from 'react-dom/client';
import UserAccount from './user-account';

document.addEventListener('DOMContentLoaded', function () {
  const elem = document.getElementById('eb-user-account');

  if (elem) {
    const attributes = {
      pageTitle: elem.dataset.pageTitle || '',
      hidePageTitle: elem.dataset.hidePageTitle === 'true',
      showCourseProgress: elem.dataset.showCourseProgress === 'true',
      showRecommendedCourses: elem.dataset.showRecommendedCourses === 'true',
      recommendedCoursesCount:
        parseInt(elem.dataset.recommendedCoursesCount, 10) || 3,
    };

    const root = ReactDOM.createRoot(elem);
    root.render(<UserAccount {...attributes} />);
  }
});
