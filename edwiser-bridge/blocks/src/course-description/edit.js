import './editor.scss';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import CourseDescription from './course-description';
import {
  PanelBody,
  __experimentalNumberControl as NumberControl,
  ToggleControl,
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__('Course', 'edwiser-bridge')} initialOpen={true}>
          <fieldset>
            <NumberControl
              label={__('Course Id', 'edwiser-bridge')}
              value={attributes.courseId}
              onChange={(value) =>
                setAttributes({ courseId: parseInt(value, 10) })
              }
            />
          </fieldset>
          <fieldset style={{ marginTop: '16px' }}>
            <ToggleControl
              label={__('Show Recommended Courses', 'edwiser-bridge')}
              checked={attributes.showRecommendedCourses}
              onChange={(value) =>
                setAttributes({ showRecommendedCourses: value })
              }
            />
            <p style={{ fontSize: '13px', color: '#819596' }}>
              {__(
                'This option also requires Show Recommended Courses to be enabled in the individual course settings.',
                'edwiser-bridge'
              )}
            </p>
          </fieldset>
        </PanelBody>
      </InspectorControls>
      <CourseDescription {...attributes} />
    </div>
  );
}
