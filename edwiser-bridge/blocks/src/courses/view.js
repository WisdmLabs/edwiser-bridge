import React from 'react';
import ReactDOM from 'react-dom/client'; // Correct import for React 18+
import CourseListing from './course-listing';

document.addEventListener('DOMContentLoaded', function () {
  const elem = document.getElementById('eb-courses');

  if (elem) {
    const attributes = {
      pageTitle: elem.dataset.pageTitle || '',
      hideTitle: elem.dataset.hideTitle === 'true',
      hideFilters: elem.dataset.hideFilters === 'true',
      coursesPerPage: parseInt(elem.dataset.coursesPerPage, 10) || 9,
      categories: elem.dataset.categories || '',
      groupByCategory: elem.dataset.groupByCategory === 'true',
      categoryPerPage: parseInt(elem.dataset.categoryPerPage, 10) || 3,
      horizontalScroll: elem.dataset.horizontalScroll === 'true',
    };

    const root = ReactDOM.createRoot(elem);
    root.render(<CourseListing {...attributes} />);
  }
});
