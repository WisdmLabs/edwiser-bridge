import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  __experimentalNumberControl as NumberControl,
  PanelBody,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import CourseListing from './course-listing';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody
          title={__('Courses Settings', 'edwiser-bridge')}
          initialOpen={true}
        >
          <fieldset>
            <TextControl
              label={__('Page Title', 'edwiser-bridge')}
              type="text"
              value={attributes.pageTitle}
              onChange={(value) => setAttributes({ pageTitle: value })}
            />
          </fieldset>

          <fieldset style={{ marginTop: '8px' }}>
            <ToggleControl
              label={__('Hide title', 'edwiser-bridge')}
              checked={attributes.hideTitle}
              onChange={(value) => setAttributes({ hideTitle: value })}
            />
          </fieldset>

          <fieldset style={{ marginTop: '8px' }}>
            <ToggleControl
              label={__('Hide filters', 'edwiser-bridge')}
              checked={attributes.hideFilters}
              onChange={(value) => setAttributes({ hideFilters: value })}
            />
          </fieldset>

          {!attributes.groupByCategory && (
            <fieldset style={{ marginTop: '8px' }}>
              <NumberControl
                label={__('Courses per page', 'edwiser-bridge')}
                value={attributes.coursesPerPage}
                onChange={(value) =>
                  setAttributes({ coursesPerPage: parseInt(value, 10) || 0 })
                }
                min={1}
              />
            </fieldset>
          )}

          <fieldset style={{ marginTop: '16px' }}>
            <TextControl
              label={__(
                'Categories (Add comma separated category slugs)',
                'edwiser-bridge'
              )}
              type="text"
              value={attributes.categories}
              onChange={(value) => setAttributes({ categories: value })}
            />
          </fieldset>

          <fieldset style={{ marginTop: '8px' }}>
            <ToggleControl
              label={__('Group courses by category', 'edwiser-bridge')}
              checked={attributes.groupByCategory}
              onChange={(value) => setAttributes({ groupByCategory: value })}
            />
          </fieldset>

          {attributes.groupByCategory && (
            <fieldset style={{ marginTop: '8px' }}>
              <NumberControl
                label={__('Grouped categories per page', 'edwiser-bridge')}
                value={attributes.categoryPerPage}
                onChange={(value) =>
                  setAttributes({ categoryPerPage: parseInt(value, 10) || 0 })
                }
                min={1}
              />
            </fieldset>
          )}

          <fieldset style={{ marginTop: '8px' }}>
            <ToggleControl
              label={__('Enable horizontal slider', 'edwiser-bridge')}
              checked={attributes.horizontalScroll}
              onChange={(value) => setAttributes({ horizontalScroll: value })}
            />
          </fieldset>
        </PanelBody>
      </InspectorControls>
      <CourseListing {...attributes} />
    </div>
  );
}
