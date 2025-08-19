import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  __experimentalNumberControl as NumberControl,
  PanelBody,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './editor.scss';
import UserAccount from './user-account';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody
          title={__('User Account Settings', 'edwiser-bridge')}
          initialOpen={true}
        >
          <ToggleControl
            label={__('Hide Page Title', 'edwiser-bridge')}
            checked={attributes.hidePageTitle}
            onChange={(value) => setAttributes({ hidePageTitle: value })}
          />
          <fieldset style={{ marginTop: '8px' }}>
            <TextControl
              label={__('Page Title', 'edwiser-bridge')}
              type="text"
              value={attributes.pageTitle}
              onChange={(value) => setAttributes({ pageTitle: value })}
            />
          </fieldset>{' '}
        </PanelBody>
        <PanelBody
          title={__('My Courses Tab Settings', 'edwiser-bridge')}
          initialOpen={true}
        >
          <ToggleControl
            label={__('Show Course Progress', 'edwiser-bridge')}
            checked={attributes.showCourseProgress}
            onChange={(value) => setAttributes({ showCourseProgress: value })}
          />
          <ToggleControl
            label={__('Show Recommended Courses', 'edwiser-bridge')}
            checked={attributes.showRecommendedCourses}
            onChange={(value) =>
              setAttributes({ showRecommendedCourses: value })
            }
          />
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
      <UserAccount {...attributes} />
    </div>
  );
}
