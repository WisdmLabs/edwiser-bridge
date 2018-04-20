package com.edwiser.EdwiserBridge;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import pageClasses.CoursesFrontEnd;
import pageClasses.EdwiserCourseEditPage;
import setUp.GeneralisedProjectOperations;
import setUp.ProjectSetUpOperations;
import setUp.projectSetUp;

public class CourseTypeSettings {

	WebDriver driver;
	ProjectSetUpOperations projectOperationObject;
	GeneralisedProjectOperations generalisedOps;
	EdwiserCourseEditPage courseEditObj;
	CoursesFrontEnd coursesFrontEndObj;
	WebDriverWait wait;
	String baseURL;

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

		// Initializing Edwiser Course Edit Page Object
		courseEditObj = new EdwiserCourseEditPage(driver);

		// Initializing Course Front End Page Object
		coursesFrontEndObj = new CoursesFrontEnd(driver);

		// Setting Admin Details
		baseURL = siteURL;
		projectOperationObject.loginToAdminDashboard(driver, baseURL, username, password);
	}

	/**
	 * Change Course Type as Paid , Add Price and Check Form Course view Page
	 * 
	 * @param username
	 * @param password
	 * @throws Exception
	 */
	@Test(priority = 1)
	@Parameters({ "course2" })
	public void makeCoursePaidAndAddPrice(String courseName) throws Exception {
		String coursePrice = "10";
		JavascriptExecutor executor = (JavascriptExecutor) driver;

		// Visit Courses Listing Page
		driver.get(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Visit Course
		courseEditObj.userNameLink(courseName).click();

		// Select Course Type
		courseEditObj.courseTypeSelect().selectByValue("paid");

		// Add Course Price
		courseEditObj.coursePrice.clear();
		courseEditObj.coursePrice.sendKeys(coursePrice);

		// Save Course
		executor.executeScript("arguments[0].click();", courseEditObj.publishButton);

		// Check Course is Updated
		Assert.assertEquals(courseEditObj.adminMessage.getText(), "Moodle Course updated. View moodle course",
				"Course price not Updated");

		// Visit Course From Front End
		courseEditObj.courseViewLink.click();

		// Check Price on Course View Page
		Assert.assertEquals(coursesFrontEndObj.getCoursePrice(), coursePrice, "Price not shown from Front End");
	}

	/**
	 * Change Course Type as Closed , Add Redirection URL and Check Form Course
	 * view Page
	 * 
	 * @param username
	 * @param password
	 * @throws Exception
	 */
	@Test(priority = 2)
	@Parameters({ "course3" })
	public void makeCourseClosedAndRedirectionURL(String courseName) throws Exception {
		String optionalURL = baseURL + "courses";

		JavascriptExecutor executor = (JavascriptExecutor) driver;

		// Visit Courses Listing Page
		driver.get(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Visit Course
		courseEditObj.userNameLink(courseName).click();

		// Select Course Type
		courseEditObj.courseTypeSelect().selectByValue("closed");

		// Add Course Price
		courseEditObj.courseOptionalUrl.clear();
		courseEditObj.courseOptionalUrl.sendKeys(optionalURL);

		// Save Course
		executor.executeScript("arguments[0].click();", courseEditObj.publishButton);

		// Check Course is Updated
		Assert.assertEquals(courseEditObj.adminMessage.getText(), "Moodle Course updated. View moodle course",
				"Course price not Updated");

		// Visit Course From Front End
		courseEditObj.courseViewLink.click();

		// Check Price on Course View Page
		Assert.assertEquals(coursesFrontEndObj.takeThisCourseButton.getAttribute("href"), optionalURL,
				"Optional URL not Matched");
	}

	/**
	 * Set Course Expire Access Days and Check Form Course view Page
	 * 
	 * @param username
	 * @param password
	 * @throws Exception
	 */
	@Test(priority = 3)
	@Parameters({ "course4", "noOfDaysForExpireAccess" })
	public void setCourseExpireAccessDays(String courseName, String daysForExpireAccess) throws Exception {

		JavascriptExecutor executor = (JavascriptExecutor) driver;

		// Visit Courses Listing Page
		driver.get(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Visit Course
		courseEditObj.userNameLink(courseName).click();

		// Enable Course Expire Access Option
		if (!courseEditObj.courseExpiry.isSelected()) {
			courseEditObj.courseExpiry.click();
		}

		// Add Course Price
		courseEditObj.numOfDaysForExpiry.clear();
		courseEditObj.numOfDaysForExpiry.sendKeys(daysForExpireAccess);

		// Save Course
		executor.executeScript("arguments[0].click();", courseEditObj.publishButton);

		// Check Course is Updated
		Assert.assertEquals(courseEditObj.adminMessage.getText(), "Moodle Course updated. View moodle course",
				"Course price not Updated");

		// Visit Course From Front End
		courseEditObj.courseViewLink.click();

		// Check Price on Course View Page
		Assert.assertEquals(coursesFrontEndObj.courseValidity.getText(),
				"Includes " + daysForExpireAccess + " days access", "Expire Access days not match");
	}
}
