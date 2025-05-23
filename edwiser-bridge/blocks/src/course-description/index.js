import '@mantine/core/styles.css';
import './style.scss';
import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType(metadata.name, {
  edit: Edit,
  save,
  attributes: {
    courseId: {
      type: 'integer',
      source: 'meta',
      meta: 'courseId'
    },
    showRecommendedCourses: {
      type: 'boolean',
      source: 'meta',
      meta: 'showRecommendedCourses'
    }
  }
});
