import { Select, TextInput } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Icons } from './icons';

function CourseControls({
  searchTerm,
  setSearchTerm,
  sortOrder,
  setSortOrder,
  selectedCategory,
  setSelectedCategory,
  categories,
}) {
  return (
    <div className="eb-courses__controls">
      <div className="eb-controls__sort-filter">
        {/* Sort Order Select */}
        <Select
          value={sortOrder}
          onChange={setSortOrder}
          defaultValue={'latest'}
          data={[
            { value: 'latest', label: __('Latest', 'edwiser-bridge') },
            { value: 'oldest', label: __('Oldest', 'edwiser-bridge') },
            { value: 'a-z', label: __('A-Z', 'edwiser-bridge') },
            { value: 'z-a', label: __('Z-A', 'edwiser-bridge') },
          ]}
          allowDeselect={false}
          checkIconPosition="right"
          comboboxProps={{ withinPortal: false }}
          leftSection={<Icons.arrowUpDown />}
          rightSection={<Icons.chevronDown />}
        />
        {/* Category Select */}
        <Select
          value={selectedCategory}
          onChange={setSelectedCategory}
          defaultValue={'all'}
          data={[
            { value: 'all', label: __('All', 'edwiser-bridge') },
            ...categories.map((category) => ({
              value: category.name.toLowerCase(),
              label: __(category.name, 'edwiser-bridge'),
            })),
          ]}
          allowDeselect={false}
          checkIconPosition="right"
          comboboxProps={{ withinPortal: false }}
          leftSection={<Icons.funnel />}
          rightSection={<Icons.chevronDown />}
        />
      </div>

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

export default CourseControls;
