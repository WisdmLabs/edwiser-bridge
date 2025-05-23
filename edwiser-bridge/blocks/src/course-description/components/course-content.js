import { Skeleton } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React from 'react';

function CourseContent({ content }) {
  return (
    <div className="eb-course-desc__content">
      <h2 className="eb-course-desc__content-title">
        {__('Description', 'edwiser-bridge')}
      </h2>
      <div
        className="eb-course-desc__content-body"
        dangerouslySetInnerHTML={{ __html: content }}
      ></div>
    </div>
  );
}

export default CourseContent;

export function CourseContentSkeleton() {
  return (
    <div className="eb-course-desc__content">
      <Skeleton width={130} height={32} />
      <div>
        <Skeleton width={'100%'} height={16} />
        <Skeleton width={'100%'} height={16} mt={4} />
        <Skeleton width={'30%'} height={16} mt={4} />
      </div>
      <div>
        <Skeleton width={'100%'} height={16} />
        <Skeleton width={'100%'} height={16} mt={4} />
        <Skeleton width={'70%'} height={16} mt={4} />
      </div>
      <div>
        <Skeleton width={'100%'} height={16} />
        <Skeleton width={'100%'} height={16} mt={4} />
        <Skeleton width={'30%'} height={16} mt={4} />
      </div>
    </div>
  );
}
