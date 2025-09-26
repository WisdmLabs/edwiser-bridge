import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import './editor.scss';
import Dashboard from './dashboard';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody
          title={__('Dashboard Settings', 'edwiser-bridge')}
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
          </fieldset>
        </PanelBody>
      </InspectorControls>

      <Dashboard {...attributes} />
    </div>
  );
}
