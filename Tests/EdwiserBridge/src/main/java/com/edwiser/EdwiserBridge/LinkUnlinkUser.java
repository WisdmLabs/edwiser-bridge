package com.edwiser.EdwiserBridge;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.UserListingAndEditPage;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class LinkUnlinkUser {
	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	UserListingAndEditPage userListingObj;
	WebDriverWait wait;
	String baseURL;

	/**
	 * Before Class: This Method does Admin Login
	 * 
	 * @param siteURL
	 * @param username
	 * @param password
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
		userListingObj = new UserListingAndEditPage(driver);

		// Setting Admin Details
		baseURL = siteURL;
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);
	}

	/**
	 * Link User with Moodle Site from User Listing Page Bulk Actions
	 * 
	 * @param UserName
	 * @param UserEmail
	 * @param FirstName
	 * @param LastName
	 * @throws Exception
	 */
	@Parameters({ "testUser", "testUserEmail", "testUserFname", "testUserLname" })
	@Test(priority = 1)
	public void linkUserFromBulkAction(String UserName, String UserEmail, String FirstName, String LastName)
			throws Exception {
		wait = new WebDriverWait(driver, 10);

		// Create User
		userListingObj.createUser(baseURL, UserName, FirstName, LastName, UserEmail);

		// Visit Users Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Check User is Linked Or Not if Linked then Unlink
		if (userListingObj.checkUserLinkStatus(UserName)) {
			userListingObj.unlinkUser(UserName).click();
			// wait for Moodle LinkUnlink User Notice
			wait.until(ExpectedConditions.visibilityOf(userListingObj.moodleLinkUnlinkUserNotices));
			Assert.assertEquals(userListingObj.moodleLinkUnlinkUserNotices.getText(),
					UserName + "'s account has been unlinked successfully.",
					"User not Unlinked with moodle site from User Listing Page link ");
		}

		// Select User
		userListingObj.userCheckbox(UserName).click();

		// Select Bulk Action to Link User
		userListingObj.bulkActionsSelect().selectByValue("link_moodle");

		// Click Apply Button
		userListingObj.bulkActionsApply.click();

		// Check Admin Message
		Assert.assertEquals(userListingObj.userLinkUnlinkMessage.getText(), "1 User Linked.",
				"User not linked with moodle site from User Listing Page Bulk Action");

	}

	/**
	 * Unlink User with Moodle Site from User Listing Page Bulk Actions
	 * 
	 * @param UserName
	 * @param UserEmail
	 * @param FirstName
	 * @param LastName
	 * @throws Exception
	 */
	@Parameters({ "testUser", "testUserEmail", "testUserFname", "testUserLname" })
	@Test(priority = 2)
	public void unlinkUserFromBulkAction(String UserName, String UserEmail, String FirstName, String LastName)
			throws Exception {
		wait = new WebDriverWait(driver, 10);

		// Create User
		userListingObj.createUser(baseURL, UserName, FirstName, LastName, UserEmail);

		// Visit Users Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Check User is Linked Or Not if not Linked then Link
		if (!userListingObj.checkUserLinkStatus(UserName)) {
			userListingObj.linkUser(UserName).click();
			// wait for Moodle LinkUnlink User Notice
			wait.until(ExpectedConditions.visibilityOf(userListingObj.moodleLinkUnlinkUserNotices));
			Assert.assertEquals(userListingObj.moodleLinkUnlinkUserNotices.getText(),
					UserName + "'s account has been linked successfully.",
					"User not linked with moodle site from User Listing Page link ");
		}

		// Select User
		userListingObj.userCheckbox(UserName).click();

		// Select Bulk Action to Link User
		userListingObj.bulkActionsSelect().selectByValue("unlink_moodle");

		// Click Apply Button
		userListingObj.bulkActionsApply.click();

		// Check Admin Message
		Assert.assertEquals(userListingObj.userLinkUnlinkMessage.getText(), "1 User Unlinked.",
				"User not unlinked with moodle site from User Listing Page Bulk Action");

	}

	/**
	 * Link User with Moodle Site from User Listing Page User Link/Unlink Link
	 * 
	 * @param UserName
	 * @param UserEmail
	 * @param FirstName
	 * @param LastName
	 * @throws Exception
	 */
	@Parameters({ "testUser", "testUserEmail", "testUserFname", "testUserLname" })
	@Test(priority = 3)
	public void linkUserFromLinkUnlinkLink(String UserName, String UserEmail, String FirstName, String LastName)
			throws Exception {
		wait = new WebDriverWait(driver, 10);

		// Create User
		userListingObj.createUser(baseURL, UserName, FirstName, LastName, UserEmail);

		// Visit Users Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Check User is Linked Or Not if Linked then Unlink
		if (userListingObj.checkUserLinkStatus(UserName)) {
			userListingObj.unlinkUser(UserName).click();
			// wait for Moodle LinkUnlink User Notice
			wait.until(ExpectedConditions.visibilityOf(userListingObj.moodleLinkUnlinkUserNotices));
			Assert.assertEquals(userListingObj.moodleLinkUnlinkUserNotices.getText(),
					UserName + "'s account has been unlinked successfully.",
					"User not Unlinked with moodle site from User Listing Page link ");

		}

		// Click on Link User Link
		userListingObj.linkUser(UserName).click();

		// wait for Moodle LinkUnlink User Notice
		wait.until(ExpectedConditions.visibilityOf(userListingObj.moodleLinkUnlinkUserNotices));
		// Check Admin Message
		Assert.assertEquals(userListingObj.moodleLinkUnlinkUserNotices.getText(),
				UserName + "'s account has been linked successfully.",
				"User not linked with moodle site from User Listing Page link ");

	}

	/**
	 * Unlink User with Moodle Site from User Listing Page User Link/Unlink Link
	 * 
	 * @param UserName
	 * @param UserEmail
	 * @param FirstName
	 * @param LastName
	 * @throws Exception
	 */
	@Parameters({ "testUser", "testUserEmail", "testUserFname", "testUserLname" })
	@Test(priority = 4)
	public void unlinkUserFromLinkUnlinkLink(String UserName, String UserEmail, String FirstName, String LastName)
			throws Exception {
		wait = new WebDriverWait(driver, 10);

		// Create User
		userListingObj.createUser(baseURL, UserName, FirstName, LastName, UserEmail);

		// Visit Users Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Check User is Linked Or Not if not Linked then Link
		if (!userListingObj.checkUserLinkStatus(UserName)) {
			userListingObj.linkUser(UserName).click();
			// wait for Moodle LinkUnlink User Notice
			wait.until(ExpectedConditions.visibilityOf(userListingObj.moodleLinkUnlinkUserNotices));
			Assert.assertEquals(userListingObj.moodleLinkUnlinkUserNotices.getText(),
					UserName + "'s account has been linked successfully.",
					"User not linked with moodle site from User Listing Page link ");

		}

		// Check Admin Message
		userListingObj.unlinkUser(UserName).click();

		// wait for Moodle LinkUnlink User Notice
		wait.until(ExpectedConditions.visibilityOf(userListingObj.moodleLinkUnlinkUserNotices));

		// Check Admin Message
		Assert.assertEquals(userListingObj.moodleLinkUnlinkUserNotices.getText(),
				UserName + "'s account has been unlinked successfully.",
				"User not Unlinked with moodle site from User Listing Page link ");

	}

}
