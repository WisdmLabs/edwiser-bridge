import React from 'react';
import { __ } from '@wordpress/i18n';

const EmptyState = () => {
  return (
    <div className="eb-course-desc__empty-state">
      <div className="eb-course-desc__empty-state-content">
        <h2>{__('Course Not Found', 'edwiser-bridge')}</h2>
        <p>
          {__(
            'Sorry, the course you are looking for is not available or may have been removed.',
            'edwiser-bridge'
          )}
        </p>
      </div>
    </div>
  );
};

export default EmptyState;
