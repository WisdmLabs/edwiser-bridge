import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
  PanelBody,
  ToggleControl,
  TextControl,
  Select,
  SelectControl,
} from '@wordpress/components';
import './editor.scss';
import Orders from './orders';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody
          title={__('Orders Settings', 'edwiser-bridge')}
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
          <fieldset style={{ marginTop: '16px' }}>
            <legend
              style={{
                fontSize: '13px',
                fontWeight: 'bold',
                marginBottom: '8px',
                color: '#1e1e1e',
              }}
            >
              {__('Order Sources', 'edwiser-bridge')}
            </legend>
            <ToggleControl
              label={__('Enable Edwiser Bridge Orders', 'edwiser-bridge')}
              checked={attributes.enableEdwiserOrders}
              onChange={(value) =>
                setAttributes({ enableEdwiserOrders: value })
              }
            />
            <ToggleControl
              label={__('Enable WooCommerce Orders', 'edwiser-bridge')}
              checked={attributes.enableWooCommerceOrders}
              onChange={(value) =>
                setAttributes({ enableWooCommerceOrders: value })
              }
            />
          </fieldset>
          {attributes.enableEdwiserOrders &&
            attributes.enableWooCommerceOrders && (
              <fieldset style={{ marginTop: '16px' }}>
                <SelectControl
                  label={__('Default Tab', 'edwiser-bridge')}
                  value={attributes.defaultTab}
                  onChange={(value) => setAttributes({ defaultTab: value })}
                  options={[
                    {
                      value: 'eb-orders',
                      label: __('Edwiser Orders', 'edwiser-bridge'),
                    },
                    {
                      value: 'wc-orders',
                      label: __('WooCommerce Orders', 'edwiser-bridge'),
                    },
                  ]}
                />
              </fieldset>
            )}
        </PanelBody>
      </InspectorControls>

      <Orders {...attributes} />
    </div>
  );
}
