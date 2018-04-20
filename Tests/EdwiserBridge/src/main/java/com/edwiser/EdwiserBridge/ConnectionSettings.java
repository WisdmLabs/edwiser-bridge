package com.edwiser.EdwiserBridge;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.EdwiserSettings;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class ConnectionSettings {

	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserSettings edwiserSettingObj;
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
	public void connectionSettingsSetUp(String siteURL, String username, String password) throws Exception {
		driver = projectSetUp.driver;
		// Initializing ProjectSetUpOperations Object
		projectOperationObject = new ProjectSetUpOperations();

		// Initializing GeneralisedProjectOperations Object
		generalisedOps = new GeneralisedProjectOperations();

		// Initializing EdwiserSettings Object
		edwiserSettingObj = new EdwiserSettings(driver);

		// Setting Admin Details
		baseURL = siteURL;
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);
	}

	/**
	 * Test with Empty Moodle URL and Empty Access Token
	 * 
	 * @throws Exception
	 */
	@Test(priority = 1)
	public void emptyURLandToken() throws Exception {
		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token
		response = edwiserSettingObj.testConnectionSettings("", "");

		// Check response
		Assert.assertEquals(response, "A valid URL was not provided.",
				"Error Message not matched on empty Moodle URL and Token");
	}

	/**
	 * Test with invalid Moodle URL and invalid Access Token
	 * 
	 * @throws Exception
	 */
	@Test(priority = 2)
	public void invalidMoodleURL() throws Exception {
		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token
		response = edwiserSettingObj.testConnectionSettings("Invalid URL", "Invalid Token");

		// Check response
		Assert.assertEquals(response, "A valid URL was not provided.",
				"Error Message not matched on empty Moodle URL and Token");
	}

	/**
	 * Test with Non Moodle Site URL and invalid Access Token
	 * 
	 * @throws Exception
	 */
	@Test(priority = 3)
	public void nonMoodleSiteURL() throws Exception {
		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token
		response = edwiserSettingObj.testConnectionSettings(baseURL, "Invalid Token");

		// Check response
		Assert.assertEquals(response, "Please check Moodle URL !",
				"Error Message not matched on empty Moodle URL and Token");
	}

	/**
	 * Test with Valid Moodle URL and Invalid Access Token
	 * 
	 * @param moodleURL
	 * @throws Exception
	 */
	@Test(priority = 4)
	@Parameters({ "moodleURL" })
	public void invalidAccessToken(String moodleURL) throws Exception {
		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token
		response = edwiserSettingObj.testConnectionSettings(moodleURL, "Invalid Token");

		// Check response
		Assert.assertEquals(response, "Invalid token - token not found",
				"Error Message not matched on empty Moodle URL and Token");
	}

	/**
	 * Test with Valid Moodle URL and Valid Access Token
	 * 
	 * @param moodleURL
	 * @param Token
	 * @throws Exception
	 */
	@Test(priority = 5)
	@Parameters({ "moodleURL", "moodleToken" })
	public void validDetails(String moodleURL, String Token) throws Exception {
		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token
		response = edwiserSettingObj.testConnectionSettings(moodleURL, Token);

		// Check response
		Assert.assertEquals(response, "Connection successful, Please save your connection details.",
				"Error Message not matched on empty Moodle URL and Token");
		// Save Settings
		edwiserSettingObj.saveChanges.click();

		wait = new WebDriverWait(driver, 10);
		// wait for Settings Saved
		wait.until(ExpectedConditions.visibilityOf(edwiserSettingObj.adminMessage));

		Assert.assertEquals(edwiserSettingObj.adminMessage.getText(), "Your settings have been saved.",
				"Settings Not saved");
	}
}
