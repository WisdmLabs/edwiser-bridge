import React from 'react';
import ReactDOM from 'react-dom/client'; // Correct import for React 18+
import CourseDescription from './course-description';

document.addEventListener('DOMContentLoaded', function () {
  const elem = document.getElementById('eb-course-description');

  if (elem) {
    const attributes = {
      courseId: parseInt(elem.dataset.courseId, 10),
      showRecommendedCourses: elem.dataset.showRecommendedCourses === 'true',
    };

    const root = ReactDOM.createRoot(elem);
    root.render(<CourseDescription {...attributes} />);
  }
});
