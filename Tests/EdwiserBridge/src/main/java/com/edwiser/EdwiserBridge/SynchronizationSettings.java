package com.edwiser.EdwiserBridge;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.EdwiserCourseEditPage;
import pageClasses.EdwiserSettings;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class SynchronizationSettings {

	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserSettings edwiserSettingObj;
	EdwiserCourseEditPage edwiserCourseEditObj;
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
	public void synchronizationSettingsSetUp(String siteURL, String username, String password) throws Exception {
		driver = projectSetUp.driver;
		// Initializing ProjectSetUpOperations Object
		projectOperationObject = new ProjectSetUpOperations();

		// Initializing GeneralisedProjectOperations Object
		generalisedOps = new GeneralisedProjectOperations();

		// Initializing EdwiserSettings Object
		edwiserSettingObj = new EdwiserSettings(driver);

		// Initializing EdwiserCourseEditPage Object
		edwiserCourseEditObj = new EdwiserCourseEditPage(driver);

		// Setting Admin Details
		baseURL = siteURL;
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);

	}

	/**
	 * Synchronize Course without connection with Moodle
	 * 
	 * @param moodleURL
	 * @param Token
	 * @throws Exception
	 */
	@Test(priority = 1)
	@Parameters({ "moodleURL", "moodleToken" })
	public void syncWithOutConnection(String moodleURL, String Token) throws Exception {
		wait = new WebDriverWait(driver, 10);
		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token for Connection
		edwiserSettingObj.testConnectionSettings(moodleURL, "123");

		// Save Settings
		edwiserSettingObj.saveChanges.click();

		// wait for Settings Saved
		wait.until(ExpectedConditions.visibilityOf(edwiserSettingObj.adminMessage));

		Assert.assertEquals(edwiserSettingObj.adminMessage.getText(), "Your settings have been saved.",
				"Settings Not saved");

		// Synchronization Setting
		response = edwiserSettingObj.courseSynchronizationSettings(true, true, false);

		// Check Response
		Assert.assertEquals(response, "There is a problem while connecting to moodle server.",
				"No error without connection synchronization");

		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Send Empty URL and Token for Connection
		edwiserSettingObj.testConnectionSettings(moodleURL, Token);

		// Save Settings
		edwiserSettingObj.saveChanges.click();

		// wait for Settings Saved
		wait.until(ExpectedConditions.visibilityOf(edwiserSettingObj.adminMessage));

		Assert.assertEquals(edwiserSettingObj.adminMessage.getText(), "Your settings have been saved.",
				"Settings Not saved");
	}

	/**
	 * Synchronize and Draft the Courses
	 * 
	 * @param moodleURL
	 * @param Token
	 * @throws Exception
	 */
	@Test(priority = 2)
	@Parameters({ "moodleURL", "moodleToken" })
	public void syncCourseAsDraft(String moodleURL, String Token) throws Exception {

		// Delete All Courses
		edwiserCourseEditObj.deleteCourses(baseURL);

		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Synchronization Setting
		response = edwiserSettingObj.courseSynchronizationSettings(false, false, true);

		// Check Response
		Assert.assertEquals(response, "Courses synchronized successfully.", "Courses not synchronized as draft");

		// Visit Courses
		driver.get(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Getting All Courses Count
		String allCourse = edwiserCourseEditObj.coursesCountwithStatus(baseURL, "All");

		// Getting Draft Courses Count
		String draftsCourse = edwiserCourseEditObj.coursesCountwithStatus(baseURL, "Drafts");

		// Checking All Courses are in draft or Not
		Assert.assertEquals(allCourse, draftsCourse, "All Courses are not in draft");
	}

	/**
	 * Synchronize and Publish the Courses
	 * 
	 * @param moodleURL
	 * @param Token
	 * @throws Exception
	 */
	@Test(priority = 3)
	@Parameters({ "moodleURL", "moodleToken" })
	public void syncCourseAsPublish(String moodleURL, String Token) throws Exception {

		// Delete All Courses
		edwiserCourseEditObj.deleteCourses(baseURL);

		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Synchronization Setting
		response = edwiserSettingObj.courseSynchronizationSettings(false, false, false);

		// Check Response
		Assert.assertEquals(response, "Courses synchronized successfully.", "Courses  synchronized as Publish Courses");

		// Visit Courses
		driver.get(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Getting All Courses Count
		String allCourse = edwiserCourseEditObj.coursesCountwithStatus(baseURL, "All");

		// Getting Published Courses Count
		String publishedCourse = edwiserCourseEditObj.coursesCountwithStatus(baseURL, "Published");

		// Checking All Courses are in Published Course or Not
		Assert.assertEquals(allCourse, publishedCourse, "All Courses are not in Published Course");
	}

	/**
	 * Synchronize course Categories
	 * 
	 * @throws Exception
	 */
	@Test(priority = 4)
	public void syncCourseCategories() throws Exception {

		// Delete All Course CategoriesbaseURL
		edwiserCourseEditObj.deleteAllCategories(baseURL);

		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Synchronization Categories
		response = edwiserSettingObj.courseSynchronizationSettings(true, false, false);

		// Visit Categories Page
		generalisedOps.visitURL(baseURL + "wp-admin/edit-tags.php?taxonomy=eb_course_cat&post_type=eb_course");

		// Check Categories are added or Not
		Assert.assertTrue(edwiserCourseEditObj.allCategoriesRows.size() != 0,
				"All Categories are not Syncronized. . .");
	}

	/**
	 * Update Courses Previously Synchronized courses
	 * 
	 * @throws Exception
	 */
	@Test(priority = 5)
	public void syncUpdateCourses() throws Exception {

		// Visit Courses Listing Page
		generalisedOps.visitURL(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// First Course In Listing for Updation
		String firstCourseName = edwiserCourseEditObj.allCoursessLinksListingPage.get(1).getText();

		// Visit Course
		edwiserCourseEditObj.allCoursessLinksListingPage.get(1).click();

		// Change Title
		edwiserCourseEditObj.courseTitle.sendKeys("Updated");

		// Save Course
		edwiserCourseEditObj.publishButton.click();

		// Visit Settings
		edwiserSettingObj.visitGeneralSettings(baseURL);

		// Synchronization Categories
		response = edwiserSettingObj.courseSynchronizationSettings(false, true, false);

		// Visit Courses Listing Page
		generalisedOps.visitURL(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Check Categories are added or Not
		Assert.assertEquals(edwiserCourseEditObj.allCoursessLinksListingPage.get(1).getText(), firstCourseName,
				"Course not updated after Syncronization. . .");
	}

	/**
	 * Synchronize User data
	 */
	// @Test(priority = 6)
	// public void syncUserData(String UserName, String Course) throws Exception
	// {
	//
	// }
}
