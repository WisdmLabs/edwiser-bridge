import { Checkbox, Select, Textarea, TextInput } from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Icons } from './icons';

function CustomFields({
  customFields,
  customFieldsData,
  updateCustomFieldsData,
  validationErrors,
  title = __('Additional information', 'edwiser-bridge'),
  className = 'eb-profile__additional-fields',
}) {
  const renderField = (fieldData) => {
    const {
      type,
      label,
      placeholder = '',
      required,
      class: fieldClassName,
      id,
      name,
    } = fieldData;

    const value =
      customFieldsData[name] !== undefined
        ? customFieldsData[name]
        : fieldData.value || '';

    const commonProps = {
      label: (
        <>
          {label}{' '}
          {!required && type !== 'checkbox' && (
            <span className="optional-label">(optional)</span>
          )}
        </>
      ),
      placeholder,
      value,
      className: fieldClassName,
      name,
      error: validationErrors?.[name],
      onChange: (e) => {
        const newValue =
          type === 'checkbox'
            ? e.currentTarget.checked
              ? 'on'
              : 0
            : e.currentTarget
            ? e.currentTarget.value
            : e;
        updateCustomFieldsData(name, newValue);
      },
      required,
    };

    const fieldWrapper = (field) => {
      return <div className="eb-profile__row">{field}</div>;
    };

    switch (type) {
      case 'text':
        return fieldWrapper(<TextInput key={id} {...commonProps} />);

      case 'textarea':
        return fieldWrapper(
          <Textarea key={id} {...commonProps} minRows={4} maxRows={8} />
        );

      case 'number':
        return fieldWrapper(
          <TextInput key={id} type="number" {...commonProps} />
        );

      case 'date':
        return fieldWrapper(
          <TextInput key={id} type="date" {...commonProps} />
        );

      case 'select':
        return fieldWrapper(
          <Select
            key={id}
            {...commonProps}
            data={fieldData.options}
            checkIconPosition="right"
            comboboxProps={{
              withinPortal: false,
            }}
            maxDropdownHeight={400}
            rightSection={<Icons.chevronDown />}
            searchable
          />
        );

      case 'checkbox':
        return fieldWrapper(
          <Checkbox
            key={id}
            {...commonProps}
            checked={!!customFieldsData[name]}
          />
        );

      default:
        return null;
    }
  };

  return (
    <div className={className}>
      <h4 className="eb-profile__additional-fields-title">{title}</h4>
      {customFields.map((fieldData) => (
        <React.Fragment key={fieldData.id}>
          {renderField(fieldData)}
        </React.Fragment>
      ))}
    </div>
  );
}

export default CustomFields;
