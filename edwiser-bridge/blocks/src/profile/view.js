import React from 'react';
import ReactDOM from 'react-dom/client';
import Profile from './profile';

document.addEventListener('DOMContentLoaded', function () {
  const elem = document.getElementById('eb-profile');

  if (elem) {
    const attributes = {
      pageTitle: elem.dataset.pageTitle || '',
      hidePageTitle: elem.dataset.hidePageTitle === 'true',
    };

    const root = ReactDOM.createRoot(elem);
    root.render(<Profile {...attributes} />);
  }
});
