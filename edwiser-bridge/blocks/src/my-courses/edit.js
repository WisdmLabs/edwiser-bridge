import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  __experimentalNumberControl as NumberControl,
  PanelBody,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import MyCourses from './my-courses';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody
          title={__('My Courses Settings', 'edwiser-bridge')}
          initialOpen={true}
        >
          <fieldset>
            <ToggleControl
              label={__('Hide Page Title', 'edwiser-bridge')}
              checked={attributes.hidePageTitle}
              onChange={(value) => setAttributes({ hidePageTitle: value })}
            />
          </fieldset>
          <fieldset style={{ marginTop: '8px' }}>
            <TextControl
              label={__('Page Title', 'edwiser-bridge')}
              type="text"
              value={attributes.pageTitle}
              onChange={(value) => setAttributes({ pageTitle: value })}
            />
          </fieldset>
          <fieldset style={{ marginTop: '8px' }}>
            <ToggleControl
              label={__('Show Course Progress', 'edwiser-bridge')}
              checked={attributes.showCourseProgress}
              onChange={(value) => setAttributes({ showCourseProgress: value })}
            />
          </fieldset>
        </PanelBody>
        <PanelBody
          title={__('Recommended Courses Settings', 'edwiser-bridge')}
          initialOpen={true}
        >
          <fieldset style={{ marginTop: '8px' }}>
            <ToggleControl
              label={__('Show Recommended Courses', 'edwiser-bridge')}
              checked={attributes.showRecommendedCourses}
              onChange={(value) =>
                setAttributes({ showRecommendedCourses: value })
              }
            />
          </fieldset>
          <fieldset style={{ marginTop: '8px' }}>
            <TextControl
              label={__('Recommended Courses Title', 'edwiser-bridge')}
              type="text"
              value={attributes.recommendedCoursesTitle}
              onChange={(value) =>
                setAttributes({ recommendedCoursesTitle: value })
              }
            />
          </fieldset>
          <fieldset style={{ marginTop: '8px' }}>
            <NumberControl
              label={__('Recommended Courses Count', 'edwiser-bridge')}
              value={attributes.recommendedCoursesCount}
              onChange={(value) =>
                setAttributes({
                  recommendedCoursesCount: parseInt(value, 10) || 0,
                })
              }
              min={1}
            />
          </fieldset>
        </PanelBody>
      </InspectorControls>
      <MyCourses {...attributes} />
    </div>
  );
}
