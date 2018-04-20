package com.edwiser.EdwiserBridge;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.CoursesFrontEnd;
import pageClasses.EdwiserOrders;
import pageClasses.UserAccountPage;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class FrontEndCoursePurchase {
	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserOrders edwiserOrdersObj;
	CoursesFrontEnd coursesFrontEndObj;
	UserAccountPage userAccountPageObj;
	WebDriverWait wait;
	String baseURL;
	String adminUser;
	String adminPassword;

	/**
	 * Before Class: This Method does Admin Login and Object Initialization
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

		// Initializing Edwiser Order Edit Page Object
		edwiserOrdersObj = new EdwiserOrders(driver);

		// Initializing Course Front End Page Object
		coursesFrontEndObj = new CoursesFrontEnd(driver);

		// Initializing User Account Page Object
		userAccountPageObj = new UserAccountPage(driver);

		// Setting Admin Details
		baseURL = siteURL;
		adminUser = username;
		adminPassword = password;

		// projectOperationObject.loginToAdminDashboard(driver, baseURL,
		// username, password);
	}

	/**
	 * Purchase Free Course With New User
	 * 
	 * Course Name : @param courseName
	 * 
	 * User Details : @param testUser1
	 * 
	 * @param testUser1Email
	 * @param testUser1Fname
	 * @param testUser1Lname
	 * @param testuser1password
	 * @throws Exception
	 */
	@Test(priority = 1)
	@Parameters({ "course1", "testUser1", "testUser1Email", "testUser1Fname", "testUser1Lname", "testuser1password" })
	public void purchaseFreeCourseForNewUser(String courseName, String testUser1, String testUser1Email,
			String testUser1Fname, String testUser1Lname, String testuser1password) throws Exception {
		JavascriptExecutor executor = (JavascriptExecutor) driver;
		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		wait = new WebDriverWait(driver, 10);

		// Make Order For User
		boolean isNewUser = coursesFrontEndObj.makeOrderforCourse(baseURL, courseName, userAccountPageObj, testUser1,
				testuser1password, testUser1Fname, testUser1Lname, testUser1Email);

		// Checking Access course button visibility
		wait.until(ExpectedConditions.visibilityOf(coursesFrontEndObj.takeThisCourseButton));
		Assert.assertEquals(coursesFrontEndObj.takeThisCourseButton.getText(), "ACCESS COURSE",
				"User is not getting Enrolled in Free Course");

		// Change Password if user is new
		if (isNewUser) {
			driver.get(baseURL + "user-account");
			userAccountPageObj.editProfileLink.click();
			wait.until(ExpectedConditions.visibilityOf(userAccountPageObj.editPassword));
			userAccountPageObj.editPassword.clear();
			// Set New Password
			userAccountPageObj.editPassword.sendKeys(testuser1password);

			// Update User
			executor.executeScript("arguments[0].click();", userAccountPageObj.updateUserButton);
			// userAccountPageObj.updateUserButton.click();
		}

	}

	/**
	 * Purchase Expire Access Enabled Course
	 * 
	 * @param courseName
	 * @param testUser1
	 * @param testUser1Email
	 * @param testUser1Fname
	 * @param testUser1Lname
	 * @param testuser1password
	 * @throws Exception
	 */

	@Test(priority = 2)
	@Parameters({ "course4", "testUser1", "testUser1Email", "testUser1Fname", "testUser1Lname", "testuser1password",
			"noOfDaysForExpireAccess" })
	public void purchaseExpireAccessEnabledCourse(String courseName, String testUser1, String testUser1Email,
			String testUser1Fname, String testUser1Lname, String testuser1password, String daysForExpireAccess)
			throws Exception {
		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		wait = new WebDriverWait(driver, 10);

		// Make Order For User
		boolean isNewUser = coursesFrontEndObj.makeOrderforCourse(baseURL, courseName, userAccountPageObj, testUser1,
				testuser1password, testUser1Fname, testUser1Lname, testUser1Email);

		// Checking Access course button visibility
		wait.until(ExpectedConditions.visibilityOf(coursesFrontEndObj.takeThisCourseButton));
		Assert.assertEquals(coursesFrontEndObj.takeThisCourseButton.getText(), "ACCESS COURSE",
				"User is not getting Enrolled in Free Course");

		// int remainingDays = Integer.parseInt(daysForExpireAccess)-1;
		// Check Course Expire Days
		Assert.assertEquals(coursesFrontEndObj.courseValidity.getText(), daysForExpireAccess + " days access remaining",
				"Expire Access days not match");

		// Change Password if user is new
		if (isNewUser) {
			driver.get(baseURL + "user-account");
			userAccountPageObj.editProfileLink.click();
			wait.until(ExpectedConditions.visibilityOf(userAccountPageObj.editPassword));
			userAccountPageObj.editPassword.clear();
			// Set New Password
			userAccountPageObj.editPassword.sendKeys(testuser1password);
		}

	}

	@Test(priority = 3)
	@Parameters({ "course2", "testUser1", "testUser1Email", "testUser1Fname", "testUser1Lname", "testuser1password",
			"noOfDaysForExpireAccess" })
	public void purchasePaidCourse(String courseName, String testUser1, String testUser1Email, String testUser1Fname,
			String testUser1Lname, String testuser1password, String daysForExpireAccess) throws Exception {
		JavascriptExecutor executor = (JavascriptExecutor) driver;
		wait = new WebDriverWait(driver, 10);

		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		// Make Order For User
		boolean isNewUser = coursesFrontEndObj.makeOrderforCourse(baseURL, courseName, userAccountPageObj, testUser1,
				testuser1password, testUser1Fname, testUser1Lname, testUser1Email);

		Thread.sleep(2000);
		String payPalURL = driver.getCurrentUrl();
		// Checking paypal URL
		Assert.assertTrue(payPalURL.contains("https://www.sandbox.paypal.com"), "Not redirected to Paypal");

		// Change Password if user is new
		if (isNewUser) {
			driver.get(baseURL + "user-account");
			userAccountPageObj.editProfileLink.click();
			wait.until(ExpectedConditions.visibilityOf(userAccountPageObj.editPassword));
			userAccountPageObj.editPassword.clear();
			// Set New Password
			userAccountPageObj.editPassword.sendKeys(testuser1password);
		}

		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		// Login as Admin
		projectOperationObject.loginToAdminDashboard(driver, baseURL, adminUser, adminPassword);

		// Complete Order
		edwiserOrdersObj.completeOrder(baseURL, testUser1, courseName);

		// Log Out from Admin Login
		projectOperationObject.logOut(driver, baseURL);

		// Login with test User
		driver.get(baseURL + "user-account");
		userAccountPageObj.loginFromUserAccountPage(testUser1, testuser1password);

		// Visit Courses Page
		driver.get(baseURL + "courses");

		// Visit Course
		executor.executeScript("arguments[0].click();", coursesFrontEndObj.courseLink(courseName));
		// coursesFrontEndObj.courseLink(courseName).click();

		// Checking Access course button visibility
		wait.until(ExpectedConditions.visibilityOf(coursesFrontEndObj.takeThisCourseButton));
		Assert.assertEquals(coursesFrontEndObj.takeThisCourseButton.getText(), "ACCESS COURSE",
				"User is not getting Enrolled in Free Course");

	}
}
