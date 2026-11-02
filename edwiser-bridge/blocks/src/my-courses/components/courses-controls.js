import { __ } from '@wordpress/i18n';
import React from 'react';
import { Select, TextInput, Skeleton } from '@mantine/core';
import { Icons } from './icons';

function CoursesControls({
  sortOrder,
  setSortOrder,
  searchTerm,
  setSearchTerm,
}) {
  return (
    <div className="eb-my-courses__controls">
      {/* Sort Order Select */}
      <Select
        value={sortOrder}
        onChange={setSortOrder}
        defaultValue={'a-z'}
        data={[
          { value: 'a-z', label: __('A-Z', 'edwiser-bridge') },
          { value: 'z-a', label: __('Z-A', 'edwiser-bridge') },
          {
            value: 'progress-high',
            label: __('Progress: High to Low', 'edwiser-bridge'),
          },
          {
            value: 'progress-low',
            label: __('Progress: Low to High', 'edwiser-bridge'),
          },
        ]}
        allowDeselect={false}
        checkIconPosition="right"
        comboboxProps={{ withinPortal: false }}
        leftSection={<Icons.arrowUpDown />}
        rightSection={<Icons.chevronDown />}
      />

      {/* Search Input */}
      <TextInput
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.currentTarget.value)}
        leftSectionPointerEvents="none"
        leftSection={<Icons.search />}
        placeholder={__('Search courses', 'edwiser-bridge')}
      />
    </div>
  );
}

export default CoursesControls;

export const CoursesControlsSkeleton = () => {
  return (
    <div className="eb-my-courses__controls">
      <Skeleton width={300} height={36} />
      <Skeleton width={300} height={36} />
    </div>
  );
};
