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

public class UserEditPageCourseEnrollment {

	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	UserListingAndEditPage userListingObj;
	WebDriverWait wait;
	String baseURL;

	/**
	 * Before Class: THis Method does Admin Login and Object Initialization
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
	 * Enroll user into course from User Edit Page
	 */
	@Parameters({ "testUser", "course1" })
	@Test(priority = 1)
	public void EnrollUserFromUsrEditPage(String UserName, String Course) throws Exception {
		wait = new WebDriverWait(driver, 10);
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

		// Visit User Edit Page
		userListingObj.userNameLink(UserName).click();
		;

		// Enroll To Course
		userListingObj.enrollToCourse(Course);

		// Check Enrolled Course
		Assert.assertTrue(userListingObj.checkUserEnrolledInCourse(Course),
				"User Is Not getting Enrolled To Course from User Edit Page");

	}

	/**
	 * Unenroll user into course from User Edit Page
	 */
	@Parameters({ "testUser", "course1" })
	@Test(priority = 2)
	public void unenrollUserFromUsrEditPage(String UserName, String Course) throws Exception {
		wait = new WebDriverWait(driver, 10);
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

		// Visit User Edit Page
		userListingObj.userNameLink(UserName).click();

		// Enroll To Course
		userListingObj.unenrollfromCourse(Course);

		// Check Enrolled Course
		Assert.assertFalse(userListingObj.checkUserEnrolledInCourse(Course),
				"User Is Not getting Unenrolled From Course from User Edit Page");

	}
}
