import React from 'react';
import ReactDOM from 'react-dom/client';
import Dashboard from './dashboard';

document.addEventListener('DOMContentLoaded', function () {
  const elem = document.getElementById('eb-dashboard');

  if (elem) {
    const attributes = {
      pageTitle: elem.dataset.pageTitle || '',
      hidePageTitle: elem.dataset.hidePageTitle === 'true',
    };

    const root = ReactDOM.createRoot(elem);
    root.render(<Dashboard {...attributes} />);
  }
});
