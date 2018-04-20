package com.edwiser.EdwiserBridge;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.EdwiserSettings;
import pageClasses.UserAccountPage;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class GeneralSettings {
	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserSettings edwiserSettingObj;
	UserAccountPage userAccountObject;
	WebDriverWait wait;
	String baseURL;
	String response; // Server response o Connection

	/**
	 * Before Class: THis Method does Admin Login
	 * 
	 * @param siteURL
	 * @param username
	 * @param password
	 * @throws Exception
	 */
	@Parameters({ "siteURL", "username", "password" })
	@BeforeClass
	public void generalSettingsSetUp(String siteURL, String username, String password) throws Exception {
		driver = projectSetUp.driver;
		// Initializing ProjectSetUpOperations Object
		projectOperationObject = new ProjectSetUpOperations();

		// Initializing GeneralisedProjectOperations Object
		generalisedOps = new GeneralisedProjectOperations();

		// Initializing EdwiserSettings Object
		edwiserSettingObj = new EdwiserSettings(driver);

		// Initializing UserAccountPage Object
		userAccountObject = new UserAccountPage(driver);

		// Setting Admin Details
		baseURL = siteURL;
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);
	}

	/**
	 * Disable User Creation
	 * 
	 * @param username
	 * @param password
	 * @throws Exception
	 */
	@Test(priority = 1)
	@Parameters({ "username", "password" })
	public void disableUserCreation(String username, String password) throws Exception {
		// Admin Login
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);

		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Disable User Creation
		if (edwiserSettingObj.userRegistration.isSelected()) {
			edwiserSettingObj.userRegistration.click();
		}

		// Save Settings
		edwiserSettingObj.saveChanges.click();

		// wait for Settings Saved
		wait.until(ExpectedConditions.visibilityOf(edwiserSettingObj.adminMessage));

		Assert.assertEquals(edwiserSettingObj.adminMessage.getText(), "Your settings have been saved.",
				"Settings Not saved");
		// Logout
		projectOperationObject.logOut(driver, baseURL);

		generalisedOps.visitURL(baseURL + "user-account");

		// Check Registration Link
		Assert.assertFalse(userAccountObject.registrationLink.isDisplayed(), "Registration Link is Available");
	}

	/**
	 * Enable User Creation
	 * 
	 * @param username
	 * @param password
	 * @throws Exception
	 */
	@Test(priority = 2)
	@Parameters({ "username", "password" })
	public void enableUserCreation(String username, String password) throws Exception {
		// Admin Login
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);

		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Disable User Creation
		if (!edwiserSettingObj.userRegistration.isSelected()) {
			edwiserSettingObj.userRegistration.click();
		}

		// Save Settings
		edwiserSettingObj.saveChanges.click();

		// wait for Settings Saved
		wait.until(ExpectedConditions.visibilityOf(edwiserSettingObj.adminMessage));

		Assert.assertEquals(edwiserSettingObj.adminMessage.getText(), "Your settings have been saved.",
				"Settings Not saved");
		// Logout
		projectOperationObject.logOut(driver, baseURL);

		generalisedOps.visitURL(baseURL + "user-account");

		// Check Registration Link
		Assert.assertTrue(userAccountObject.registrationLink.isDisplayed(), "Registration Link is Not Available");
	}
}
