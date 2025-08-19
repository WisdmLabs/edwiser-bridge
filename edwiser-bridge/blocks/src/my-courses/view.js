import React from 'react';
import ReactDOM from 'react-dom/client';
import MyCourses from './my-courses';

document.addEventListener('DOMContentLoaded', function () {
  const elem = document.getElementById('eb-my-courses');

  if (elem) {
    const attributes = {
      pageTitle: elem.dataset.pageTitle || '',
      recommendedCoursesTitle: elem.dataset.recommendedCoursesTitle || '',
      recommendedCoursesCount:
        parseInt(elem.dataset.recommendedCoursesCount, 10) || 3,
      showCourseProgress: elem.dataset.showCourseProgress === 'true',
      showRecommendedCourses: elem.dataset.showRecommendedCourses === 'true',
      hidePageTitle: elem.dataset.hidePageTitle === 'true',
    };

    const root = ReactDOM.createRoot(elem);
    root.render(<MyCourses {...attributes} />);
  }
});
