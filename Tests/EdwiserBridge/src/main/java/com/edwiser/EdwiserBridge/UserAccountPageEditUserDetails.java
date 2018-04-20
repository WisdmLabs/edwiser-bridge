package com.edwiser.EdwiserBridge;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

import pageClasses.CoursesFrontEnd;
import pageClasses.EdwiserOrders;
import pageClasses.UserAccountPage;
import pageClasses.UserListingAndEditPage;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class UserAccountPageEditUserDetails {

	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserOrders edwiserOrdersObj;
	CoursesFrontEnd coursesFrontEndObj;
	UserAccountPage userAccountPageObj;
	UserListingAndEditPage userListingAndEditPageObj;
	WebDriverWait wait;
	String baseURL;
	String adminUser;
	String adminPassword;
	SoftAssert soft_assert = new SoftAssert();

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

		userListingAndEditPageObj = new UserListingAndEditPage(driver);

		// Setting Admin Details
		baseURL = siteURL;
		adminUser = username;
		adminPassword = password;

		// projectOperationObject.loginToAdminDashboard(driver, baseURL,
		// username, password);
	}

	/**
	 * Update User data from User Account Page
	 */
	@Test(priority = 1)
	@Parameters({ "course3", "testUser1", "testUser1Email", "testUser1Fname", "testUser1Lname", "testuser1password" })
	public void editUserDetailsFromUserAccountPage(String courseName, String testUser1, String testUser1Email,
			String testUser1Fname, String testUser1Lname, String testuser1password) throws Exception {

		wait = new WebDriverWait(driver, 15);
		JavascriptExecutor executor = (JavascriptExecutor) driver;

		// Data to Update
		String NewPassWord = testuser1password;
		String City = "Pune";
		String CountryCode = "IN";
		String BiographicalInfo = "Share a little biographical information to fill out your profile. This may be shown publicly.";

		projectOperationObject.logOut(driver, baseURL);
		// Visit User Account Page
		driver.get(baseURL + "user-account");

		// Login By Test User
		userAccountPageObj.loginFromUserAccountPage(testUser1, testuser1password);

		// Visiting User Account Page
		driver.get(baseURL + "user-account/");

		// Click User Edit Profile Link
		// userAccountPageObj.editProfileLink.click();
		wait .until(ExpectedConditions.visibilityOf(userAccountPageObj.editProfileLink));
		executor.executeScript("arguments[0].click();", userAccountPageObj.editProfileLink);

		wait.until(ExpectedConditions.visibilityOf(userAccountPageObj.editUserName));

		// Change First Name to Last Name
		userAccountPageObj.editFirstName.clear();
		userAccountPageObj.editFirstName.sendKeys(testUser1Lname);

		// Change Last Name to First Name
		userAccountPageObj.editLastName.clear();
		userAccountPageObj.editLastName.sendKeys(testUser1Fname);

		// Set New Password
		userAccountPageObj.editPassword.clear();
		userAccountPageObj.editPassword.sendKeys(NewPassWord);

		// Select City
		userAccountPageObj.editCity.clear();
		userAccountPageObj.editCity.sendKeys(City);

		// Add Biographical Info
		userAccountPageObj.editBiographicalInformation.clear();
		userAccountPageObj.editBiographicalInformation.sendKeys(BiographicalInfo);

		// Select Country
		Select Countries = new Select(userAccountPageObj.editCountry);
		Countries.selectByValue(CountryCode);

		// Update User
		executor.executeScript("arguments[0].click();", userAccountPageObj.updateUserButton);
		// userAccountPageObj.updateUserButton.click();

		// Check User is Updated or Not
		Assert.assertTrue(userAccountPageObj.userUpdateMessage.getText().equals("Account details saved successfully."),
				"User Details are not Updated");

		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		// Login as Admin
		projectOperationObject.loginToAdminDashboard(driver, baseURL, adminUser, adminPassword);

		// Visit User Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Click on Edit User
		userListingAndEditPageObj.userEditLink(testUser1).click();

		// Checking First Name
		soft_assert.assertEquals(userListingAndEditPageObj.userFirstName.getAttribute("value"), testUser1Lname,
				"First Name Not Matched");
		// Checking Last Name
		soft_assert.assertEquals(userListingAndEditPageObj.userLastName.getAttribute("value"), testUser1Fname,
				"Last Name Not Matched");
		// Checking Email
		soft_assert.assertEquals(userListingAndEditPageObj.userEmail.getAttribute("value"), testUser1Email,
				"Email Not Matched");
		// Checking Biographical Info
		soft_assert.assertEquals(userListingAndEditPageObj.biographicalInfo.getText(), BiographicalInfo,
				"Biographical Info Not Matched");

		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		// Assert All
		soft_assert.assertAll();
	}

	/**
	 * Check Enrolled Courses on User Account Page and there Link
	 * 
	 * @param courseName
	 * @param testUsername
	 * @param testPassword
	 * @throws Exception
	 */
	@Test(priority = 2)
	@Parameters({ "course3", "testUser", "testuserpassword" })
	public void checkEnrolledCoursesOnUserAccountPage(String courseName, String testUsername, String testPassword)
			throws Exception {
		wait = new WebDriverWait(driver, 10);

		// Login as Admin
		projectOperationObject.loginToAdminDashboard(driver, baseURL, adminUser, adminPassword);

		// Visit User Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Click on Edit User
		userListingAndEditPageObj.userEditLink(testUsername).click();

		// Enroll To Course
		userListingAndEditPageObj.enrollToCourse(courseName);

		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		// Visit User Account Page
		driver.get(baseURL + "user-account");

		// Login By Test User
		userAccountPageObj.loginFromUserAccountPage(testUsername, testPassword);

		// Visiting User Account Page
		driver.get(baseURL + "user-account");

		// Check User Enrolled Course on User Account Page
		Assert.assertTrue(userAccountPageObj.userIsEnrolledInSpecificCourse(courseName),
				"Enrolled Courses Not Showing on User Account Page");

		// Log Out
		projectOperationObject.logOut(driver, baseURL);
	}

	/**
	 * Check Unenrolled Courses on User Account Page and there Link
	 * 
	 * @param courseName
	 * @param testUsername
	 * @param testPassword
	 * @throws Exception
	 */
	@Test(priority = 3)
	@Parameters({ "course3", "testUser", "testuserpassword" })
	public void checkUnenrolledCoursesOnUserAccountPage(String courseName, String testUsername, String testPassword)
			throws Exception {
		wait = new WebDriverWait(driver, 10);

		// Login as Admin
		projectOperationObject.loginToAdminDashboard(driver, baseURL, adminUser, adminPassword);

		// Visit User Listing Page
		driver.get(baseURL + "wp-admin/users.php");

		// Click on Edit User
		userListingAndEditPageObj.userEditLink(testUsername).click();

		// Enroll To Course
		userListingAndEditPageObj.unenrollfromCourse(courseName);

		// Log Out
		projectOperationObject.logOut(driver, baseURL);

		// Visit User Account Page
		driver.get(baseURL + "user-account");

		// Login By Test User
		userAccountPageObj.loginFromUserAccountPage(testUsername, testPassword);

		// Visiting User Account Page
		driver.get(baseURL + "user-account");

		// Check User Enrolled Course on User Account Page
		Assert.assertFalse(userAccountPageObj.userIsEnrolledInSpecificCourse(courseName),
				"Enrolled Courses Not Showing on User Account Page");

		// Log Out
		projectOperationObject.logOut(driver, baseURL);
	}

}
