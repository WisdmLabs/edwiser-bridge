package com.edwiser.EdwiserBridge;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.EdwiserSettings;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class Extras {
	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserSettings edwiserSettingObj;
	WebDriverWait wait;
	String baseURL;
	String response;

	/**
	 * Before Class: This Method does Admin Login
	 * 
	 * @param siteURL
	 * @param username
	 *            Admin UserName
	 * @param password
	 *            Admin User Password
	 * @throws Exception
	 */
	@Parameters({ "siteURL", "username", "password" })
	@BeforeClass
	public void beforeClassObjectSetUp(String siteURL, String username, String password) throws Exception {
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
	 * PayPal Settings
	 */
	@Test(priority = 1)
	public void PayPal() throws Exception {
		// Visit Edwiser Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Visit PayPal Tab
		edwiserSettingObj.paypalSettings.click();

		// Add PayPal Email Address
		edwiserSettingObj.paypalEmail.clear();
		edwiserSettingObj.paypalEmail.sendKeys("yogesh.deore@wisdmlabs.com");

		// Select Currency
		Select currency = new Select(edwiserSettingObj.paypalCurrency);
		currency.selectByValue("USD");

		// Set PayPal Country Code
		edwiserSettingObj.paypalCountryCode.clear();
		edwiserSettingObj.paypalCountryCode.sendKeys("US");

		// Enabling PayPal SandBox
		if (!edwiserSettingObj.paypalSandbox.isSelected()) {
			edwiserSettingObj.paypalSandbox.click();
		}

		// Save Settings
		edwiserSettingObj.saveChanges.click();

		// Wait for Settings Saved
		wait = new WebDriverWait(driver, 10);
		wait.until(ExpectedConditions.visibilityOf(edwiserSettingObj.adminMessage));

		// Asserting Settings are saved
		Assert.assertEquals(edwiserSettingObj.adminMessage.getText(), "Your settings have been saved.",
				"Paypal Settings not saved ");

	}

	/**
	 * License tab of Edwiser Bridge
	 */
	@Test(priority = 2)
	public void licenseTabExtensionsLink() throws Exception {
		// Visit Edwiser Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Visit PayPal Tab
		edwiserSettingObj.licenseSettings.click();

		// Check Extensions Link on License Page
		Assert.assertEquals(edwiserSettingObj.extensionLink.getAttribute("href"),
				"https://edwiser.org/bridge/extensions/", "Extenssion URL on match.");
	}

	/**
	 * Help tab of Edwiser Bridge
	 */
	@Test(priority = 3)
	public void help() throws Exception {
		// Visit Edwiser Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Check Help Link
		Assert.assertEquals(edwiserSettingObj.helpMenu.getAttribute("href"),
				"https://edwiser.org/bridge/documentation/", "Help URL not matched");
	}

	/**
	 * Check Extensions Page Links
	 */
	@Test(priority = 4)
	public void CheckExtensionsPageLinks() throws Exception {
		// Visit Edwiser Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Click Extensions Menu
		edwiserSettingObj.extensionsMenu.click();

		// Check All Extensions Link
		Assert.assertEquals(edwiserSettingObj.browseAllExtensions.getAttribute("href"),
				"https://edwiser.org/bridge/extensions/", "Browse all Extenssion link has different URL");

		// Check Number of Extensions Listed on Page
		Assert.assertTrue(edwiserSettingObj.allExtensionsList.size() == 5,
				"All Extenssipns are not getting displayed on Extenssions page");

		// Check All Our Extensions Link
		Assert.assertEquals(edwiserSettingObj.browseAllOurExtensions.getAttribute("href"),
				"https://edwiser.org/bridge/extensions/", "Edwiser Bridge Extenssion link has different URL");

	}
}
