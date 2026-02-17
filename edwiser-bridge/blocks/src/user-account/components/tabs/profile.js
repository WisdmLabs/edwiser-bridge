import {
  PasswordInput,
  Select,
  Skeleton,
  TextInput,
  Textarea,
} from '@mantine/core';
import { __ } from '@wordpress/i18n';
import React, { useEffect, useState } from 'react';
import { Icons } from '../icons';
import { useProfile } from '../../hooks/use-profile';
import CustomFields from '../custom-fields';

function Profile() {
  const { user, customFields, countries, isLoading, updateProfile } =
    useProfile();
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const [isSaving, setIsSaving] = useState(false);

  const [customFieldsData, setCustomFieldsData] = useState({});
  const [originalCustomFieldsData, setOriginalCustomFieldsData] = useState({});
  const [validationErrors, setValidationErrors] = useState({});

  const updateCustomFieldsData = (name, value) => {
    if (value === undefined) {
      const updatedData = { ...customFieldsData };
      delete updatedData[name];
      setCustomFieldsData(updatedData);
    } else {
      setCustomFieldsData({
        ...customFieldsData,
        [name]: value,
      });
    }

    // Clear field-specific validation error when user provides value
    if (value === true || (typeof value === 'string' && value.trim())) {
      setValidationErrors((prev) => ({
        ...prev,
        [name]: '',
      }));
    }

    // Clear API message when user starts typing
    clearApiMessageOnInput();
  };

  // Profile form state
  const [profileData, setProfileData] = useState({
    first_name: '',
    last_name: '',
    display_name: '',
    email: '',
    country: '',
    city: '',
    bio: '',
  });

  // Password form state
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: '',
  });

  // Original data for change detection
  const [originalProfileData, setOriginalProfileData] = useState({
    first_name: '',
    last_name: '',
    display_name: '',
    email: '',
    country: '',
    city: '',
    bio: '',
  });

  // Form errors
  const [profileErrors, setProfileErrors] = useState({});
  const [passwordErrors, setPasswordErrors] = useState({});
  const [apiMessage, setApiMessage] = useState({ type: '', text: '' });
  const [messageTimer, setMessageTimer] = useState(null);

  // Load user data when available
  useEffect(() => {
    if (user) {
      const userData = {
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        display_name: user.nickname || '',
        email: user.email || '',
        country: user.country && user.country !== '0' ? user.country : '',
        city: user.city || '',
        bio: user.description || '',
      };
      setProfileData(userData);
      setOriginalProfileData(userData);
    }
  }, [user]);

  // Initialize custom fields data when available
  useEffect(() => {
    if (customFields && customFields.length > 0) {
      const initialCustomFieldsData = {};
      customFields.forEach((field) => {
        if (field.type === 'checkbox') {
          if (
            field.value === true ||
            field.value === 'on' ||
            field.value === '1' ||
            field.value === 1
          ) {
            initialCustomFieldsData[field.name] = 'on';
          }
        } else {
          initialCustomFieldsData[field.name] = field.value;
        }
      });
      setCustomFieldsData(initialCustomFieldsData);
      setOriginalCustomFieldsData(initialCustomFieldsData);
    }
  }, [customFields]);

  // Handle profile input changes
  const handleProfileChange = (field, value) => {
    setProfileData((prev) => ({
      ...prev,
      [field]: value,
    }));

    // Clear error when user starts typing
    if (profileErrors[field]) {
      setProfileErrors((prev) => ({
        ...prev,
        [field]: '',
      }));
    }

    // Clear API message when user starts typing
    clearApiMessageOnInput();
  };

  // Handle password input changes
  const handlePasswordChange = (field, value) => {
    setPasswordData((prev) => ({
      ...prev,
      [field]: value,
    }));

    // Clear error when user starts typing
    if (passwordErrors[field]) {
      setPasswordErrors((prev) => ({
        ...prev,
        [field]: '',
      }));
    }

    // Clear API message when user starts typing
    clearApiMessageOnInput();
  };

  // Validate profile form
  const validateProfile = () => {
    const errors = {};

    if (!profileData.first_name) {
      errors.first_name = __('First name is required', 'edwiser-bridge');
    }

    if (!profileData.last_name) {
      errors.last_name = __('Last name is required', 'edwiser-bridge');
    }

    if (!profileData.display_name) {
      errors.display_name = __('Nickname is required', 'edwiser-bridge');
    }

    if (!profileData.email || !/^\S+@\S+$/.test(profileData.email)) {
      errors.email = __('Please enter a valid email address', 'edwiser-bridge');
    }

    setProfileErrors(errors);
    return Object.keys(errors).length === 0;
  };

  // Validate password form
  const validatePassword = () => {
    const errors = {};

    if (!passwordData.new_password) {
      errors.new_password = __('New password is required', 'edwiser-bridge');
    }

    if (!passwordData.confirm_password) {
      errors.confirm_password = __(
        'Confirm password is required',
        'edwiser-bridge'
      );
    }

    if (passwordData.new_password !== passwordData.confirm_password) {
      errors.confirm_password = __(
        'Confirm password does not match',
        'edwiser-bridge'
      );
    }

    setPasswordErrors(errors);
    return Object.keys(errors).length === 0;
  };

  // Validate custom fields
  const validateCustomFields = () => {
    const errors = {};

    customFields.forEach((field) => {
      if (field.required) {
        const value = customFieldsData[field.name];

        if (field.type === 'checkbox') {
          // For checkbox, check if it's checked
          if (!value) {
            errors[field.name] = __(
              `${field.label} is required`,
              'edwiser-bridge'
            );
          }
        } else {
          // For other field types, check if value exists and is not empty
          if (!value || (typeof value === 'string' && value.trim() === '')) {
            errors[field.name] = __(
              `${field.label} is required`,
              'edwiser-bridge'
            );
          }
        }
      }
    });

    // Update validation errors for custom fields
    setValidationErrors((prev) => ({
      ...prev,
      ...errors,
    }));

    return Object.keys(errors).length === 0;
  };

  // Map API errors to form errors
  const mapApiErrorsToForm = (error) => {
    const newProfileErrors = {};
    const newPasswordErrors = {};

    if (error.code) {
      switch (error.code) {
        case 'email_required':
          newProfileErrors.email = __(
            'Email address is required',
            'edwiser-bridge'
          );
          break;
        case 'invalid_email':
          newProfileErrors.email = __(
            'Invalid email address',
            'edwiser-bridge'
          );
          break;
        case 'email_exists':
          newProfileErrors.email = __('Email already exists', 'edwiser-bridge');
          break;
        case 'password_mismatch':
          newPasswordErrors.confirm_password = __(
            'New password and confirm password do not match',
            'edwiser-bridge'
          );
          break;
        case 'invalid_current_password':
          newPasswordErrors.current_password = __(
            'Invalid current password',
            'edwiser-bridge'
          );
          break;
        case 'wp_update_failed':
        case 'moodle_update_failed':
          setApiMessageWithTimer({
            type: 'error',
            text:
              error.message || __('Profile update failed', 'edwiser-bridge'),
          });
          break;
        default:
          setApiMessageWithTimer({
            type: 'error',
            text:
              error.message ||
              __('An error occurred while updating profile', 'edwiser-bridge'),
          });
      }
    }

    setProfileErrors((prev) => ({ ...prev, ...newProfileErrors }));
    setPasswordErrors((prev) => ({ ...prev, ...newPasswordErrors }));

    return (
      Object.keys(newProfileErrors).length > 0 ||
      Object.keys(newPasswordErrors).length > 0
    );
  };

  // Set API message with auto-clear timer
  const setApiMessageWithTimer = (message) => {
    // Clear any existing timer
    if (messageTimer) {
      clearTimeout(messageTimer);
    }

    setApiMessage(message);

    // Set new timer to clear message after 5 seconds
    if (message.text) {
      const timer = setTimeout(() => {
        setApiMessage({ type: '', text: '' });
        setMessageTimer(null);
      }, 5000);
      setMessageTimer(timer);
    }
  };

  // Clear API message manually
  const clearApiMessage = () => {
    if (messageTimer) {
      clearTimeout(messageTimer);
      setMessageTimer(null);
    }
    setApiMessage({ type: '', text: '' });
  };

  // Clear API message when user starts typing
  const clearApiMessageOnInput = () => {
    if (apiMessage.text) {
      clearApiMessage();
    }
  };

  // Check if there are changes in the form
  const hasChanges = () => {
    // Check profile data changes
    const profileChanged = Object.keys(originalProfileData).some(
      (key) => originalProfileData[key] !== profileData[key]
    );

    // Check if password fields have any values (indicating intent to change password)
    const passwordChanged =
      passwordData.current_password.trim() !== '' ||
      passwordData.new_password.trim() !== '' ||
      passwordData.confirm_password.trim() !== '';

    // Check custom fields changes
    const customFieldsChanged =
      Object.keys(originalCustomFieldsData).some(
        (key) => originalCustomFieldsData[key] !== customFieldsData[key]
      ) ||
      Object.keys(customFieldsData).some(
        (key) => originalCustomFieldsData[key] !== customFieldsData[key]
      );

    return profileChanged || passwordChanged || customFieldsChanged;
  };

  // Cleanup timer on unmount
  useEffect(() => {
    return () => {
      if (messageTimer) {
        clearTimeout(messageTimer);
      }
    };
  }, [messageTimer]);

  // Handle profile save
  const handleProfileSave = async (e) => {
    e.preventDefault();

    // Clear previous API message
    clearApiMessage();

    // Client-side validation first
    const isProfileValid = validateProfile();
    const isCustomFieldsValid = validateCustomFields();
    let isPasswordValid = true;

    // Only validate password if user is trying to change it
    if (
      passwordData.new_password ||
      passwordData.confirm_password ||
      passwordData.current_password
    ) {
      isPasswordValid = validatePassword();
    }

    if (!isProfileValid || !isPasswordValid || !isCustomFieldsValid) {
      return;
    }

    setIsSaving(true);
    try {
      const result = await updateProfile({
        ...profileData,
        ...passwordData,
        custom_fields: customFieldsData,
      });

      if (result.success) {
        setApiMessageWithTimer({
          type: 'success',
          text:
            result.data.message ||
            __('Profile updated successfully', 'edwiser-bridge'),
        });

        // Clear password fields after successful update
        setPasswordData({
          current_password: '',
          new_password: '',
          confirm_password: '',
        });
        setIsPasswordVisible(false);

        // Update original data to current data after successful save
        setOriginalProfileData({ ...profileData });
        setOriginalCustomFieldsData({ ...customFieldsData });
      } else {
        // Handle API validation errors
        const hasFormErrors = mapApiErrorsToForm(result.error);

        if (!hasFormErrors) {
          setApiMessageWithTimer({
            type: 'error',
            text:
              result.error.message ||
              __('Profile update failed', 'edwiser-bridge'),
          });
        }
      }
    } catch (error) {
      console.error('Error saving profile:', error);
      setApiMessageWithTimer({
        type: 'error',
        text: __('An unexpected error occurred', 'edwiser-bridge'),
      });
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) {
    return <ProfileSkeleton />;
  }

  return (
    <div className="eb-user-account__profile">
      <h3 className="eb-profile__title">{__('Profile', 'edwiser-bridge')}</h3>
      <div className="eb-profile__content">
        {/* Personal Information Section */}
        <div className="eb-profile__section">
          <div className="eb-profile__row">
            <TextInput
              label={__('First name', 'edwiser-bridge')}
              placeholder={__('e.g., John', 'edwiser-bridge')}
              value={profileData.first_name}
              onChange={(e) =>
                handleProfileChange('first_name', e.target.value)
              }
              error={profileErrors.first_name}
              required
            />

            <TextInput
              label={__('Last name', 'edwiser-bridge')}
              placeholder={__('e.g., Smith', 'edwiser-bridge')}
              value={profileData.last_name}
              onChange={(e) => handleProfileChange('last_name', e.target.value)}
              error={profileErrors.last_name}
              required
            />
          </div>
          <div className="eb-profile__row">
            <div className="eb-profile__row-field">
              <TextInput
                label={__('Nickname', 'edwiser-bridge')}
                placeholder={__('e.g., John Smith or JSmith', 'edwiser-bridge')}
                value={profileData.display_name}
                onChange={(e) =>
                  handleProfileChange('display_name', e.target.value)
                }
                error={profileErrors.display_name}
                required
              />
            </div>

            <TextInput
              label={__('Email', 'edwiser-bridge')}
              placeholder={__('e.g., john.smith@example.com', 'edwiser-bridge')}
              value={profileData.email}
              onChange={(e) => handleProfileChange('email', e.target.value)}
              error={profileErrors.email}
              required
            />
          </div>
        </div>

        {/* Password Management Section */}
        <div
          className={
            isPasswordVisible
              ? 'eb-profile__password-section eb-profile__password-section--visible'
              : 'eb-profile__password-section'
          }
        >
          <div className="eb-profile__password-header">
            <h4 className="eb-profile__password-title">
              {__('Password', 'edwiser-bridge')}
            </h4>
            {isPasswordVisible && (
              <button
                className="eb-profile__password-close"
                onClick={() => setIsPasswordVisible(false)}
              >
                <Icons.close />
              </button>
            )}
          </div>

          {isPasswordVisible ? (
            <div className="eb-profile__password-fields">
              <div className="eb-profile__password-row">
                <PasswordInput
                  label={__('Current password', 'edwiser-bridge')}
                  placeholder={__(
                    'Enter your current password',
                    'edwiser-bridge'
                  )}
                  value={passwordData.current_password}
                  onChange={(e) =>
                    handlePasswordChange('current_password', e.target.value)
                  }
                  error={passwordErrors.current_password}
                  required
                />
              </div>

              <div className="eb-profile__password-row">
                <PasswordInput
                  label={__('New password', 'edwiser-bridge')}
                  placeholder={__('Enter your new password', 'edwiser-bridge')}
                  value={passwordData.new_password}
                  onChange={(e) =>
                    handlePasswordChange('new_password', e.target.value)
                  }
                  error={passwordErrors.new_password}
                  required
                />
                <PasswordInput
                  label={__('Confirm password', 'edwiser-bridge')}
                  placeholder={__(
                    'Re-enter your new password',
                    'edwiser-bridge'
                  )}
                  value={passwordData.confirm_password}
                  onChange={(e) =>
                    handlePasswordChange('confirm_password', e.target.value)
                  }
                  error={passwordErrors.confirm_password}
                  required
                />
              </div>
            </div>
          ) : (
            <div className="eb-profile__password-toggle">
              <button
                type="button"
                className="eb-profile__btn eb-profile__btn--secondary"
                onClick={() => setIsPasswordVisible(!isPasswordVisible)}
              >
                <Icons.edit />
                {__('Change password', 'edwiser-bridge')}
              </button>
            </div>
          )}
        </div>

        {/* Location and Bio Section */}
        <div className="eb-profile__section">
          <div className="eb-profile__row">
            <Select
              label={__('Country', 'edwiser-bridge')}
              placeholder={__('Select your country', 'edwiser-bridge')}
              data={[
                {
                  value: '',
                  label: __('- Select a country -', 'edwiser-bridge'),
                },
                ...countries,
              ]}
              value={profileData.country}
              onChange={(value) => handleProfileChange('country', value)}
              rightSection={<Icons.chevronDown />}
              comboboxProps={{
                withinPortal: false,
              }}
              searchable
              checkIconPosition="right"
            />

            <TextInput
              label={__('City', 'edwiser-bridge')}
              placeholder={__(
                'e.g., New York, London, Tokyo',
                'edwiser-bridge'
              )}
              value={profileData.city}
              onChange={(e) => handleProfileChange('city', e.target.value)}
            />
          </div>
          <Textarea
            label={__('Bio', 'edwiser-bridge')}
            placeholder={__(
              'Tell us about yourself, your interests, or professional background...',
              'edwiser-bridge'
            )}
            minRows={4}
            maxRows={8}
            value={profileData.bio}
            onChange={(e) => handleProfileChange('bio', e.target.value)}
          />
        </div>

        <CustomFields
          customFields={customFields}
          customFieldsData={customFieldsData}
          updateCustomFieldsData={updateCustomFieldsData}
          validationErrors={validationErrors}
          title={__('Additional fields', 'edwiser-bridge')}
        />
      </div>

      <div className="eb-profile__footer">
        <div className="eb-profile__footer-content">
          {apiMessage.text && (
            <div
              className={`eb-profile__message eb-profile__message--${apiMessage.type} eb-profile__message--animated`}
            >
              <span className="eb-profile__message-text">
                {apiMessage.text}
              </span>
              <button
                type="button"
                className="eb-profile__message-close"
                onClick={clearApiMessage}
                aria-label={__('Close message', 'edwiser-bridge')}
              >
                <Icons.close />
              </button>
            </div>
          )}

          <p className="eb-profile__footer-note">
            <strong>{__('Note: ', 'edwiser-bridge')}</strong>
            {__(
              'All fields will be updated on Moodle as well as on WordPress site.',
              'edwiser-bridge'
            )}
          </p>

          <button
            type="submit"
            className="eb-profile__btn eb-profile__btn--primary eb-profile__btn--save"
            disabled={isSaving || !hasChanges()}
            onClick={handleProfileSave}
          >
            {isSaving && <Icons.loader />}
            {__('Save settings', 'edwiser-bridge')}
          </button>
        </div>
      </div>
    </div>
  );
}

export default Profile;

export const ProfileSkeleton = () => {
  return (
    <div className="eb-user-account__profile">
      <h3 className="eb-profile__title">{__('Profile', 'edwiser-bridge')}</h3>
      <div className="eb-profile__content">
        <div className="eb-profile__section">
          <div className="eb-profile__row">
            <Skeleton width="100%" height={36} />
            <Skeleton width="100%" height={36} />
          </div>
          <div className="eb-profile__row">
            <Skeleton width="100%" height={36} />
            <Skeleton width="100%" height={36} />
          </div>
        </div>

        <Skeleton width={150} height={40} />

        <div className="eb-profile__section">
          <div className="eb-profile__row">
            <Skeleton width="100%" height={36} />
            <Skeleton width="100%" height={36} />
          </div>
          <Skeleton width="100%" height={80} />
        </div>
      </div>

      <div className="eb-profile__footer">
        <div className="eb-profile__footer-content">
          <Skeleton width={400} height={20} />
          <Skeleton width={120} height={38} />
        </div>
      </div>
    </div>
  );
};
