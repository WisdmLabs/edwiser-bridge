package pageClasses;

import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import setUp.GeneralisedProjectOperations;

public class CoursesFrontEnd {
	WebDriver driver;

	GeneralisedProjectOperations generalisedOps = new GeneralisedProjectOperations();

	WebDriverWait wait;

	// Constructor
	public CoursesFrontEnd(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	// Take this Course Or Access Course Button on Course View Page
	@FindBy(xpath = "(//*[@class='wdm-btn'])[1]")
	public WebElement takeThisCourseButton;

	// Course Validity
	@FindBy(xpath = "//*[@class='eb-validity-wrapper']/span")
	public WebElement courseValidity;

	// Course View Page Course Price
	public String getCoursePrice() {
		return generalisedOps.findAndReturnWebElement(driver, "xpath", "//*[@class='wdm-pricepaid']").getText()
				.substring(1);
	}

	// Courses page Course Link
	public WebElement courseLink(String courseName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath",
				"//*[@class='wdm-caption']/h4[text()='" + courseName + "']");
	}

	/**
	 * Add Days in Date
	 * 
	 * @param date
	 * @param days
	 * @return
	 */
	public static Date addDays(Date date, int days) {
		GregorianCalendar cal = new GregorianCalendar();
		cal.setTime(date);
		cal.add(Calendar.DATE, days);

		return cal.getTime();
	}

	/**
	 * Make Order For Course
	 * 
	 * @param baseURL
	 * @param courseName
	 * @param userAccountPageObj
	 * @param testUser1
	 * @param testuser1password
	 * @param testUser1Fname
	 * @param testUser1Lname
	 * @param testUser1Email
	 * @return is User is new to Site for setting password
	 * @throws Exception
	 */
	public boolean makeOrderforCourse(String baseURL, String courseName, UserAccountPage userAccountPageObj,
			String testUser1, String testuser1password, String testUser1Fname, String testUser1Lname,
			String testUser1Email) throws Exception {
		JavascriptExecutor executor = (JavascriptExecutor) driver;
		wait = new WebDriverWait(driver, 15);
		boolean isNewUser = false;

		// Visit Courses Page
		driver.get(baseURL + "courses");

		// Visit Course
		wait.until(ExpectedConditions.visibilityOf(courseLink(courseName)));
		executor.executeScript("arguments[0].click();", courseLink(courseName));
		// courseLink(courseName).click();

		// Click Take This Course Button
		executor.executeScript("arguments[0].click();", takeThisCourseButton);

		// Waiting for registration link (Login Page)
		wait.until(ExpectedConditions.visibilityOf(userAccountPageObj.registrationLink));

		// User Login
		userAccountPageObj.loginFromUserAccountPage(testUser1, testuser1password);

		// Register User if Login Fail
		if (userAccountPageObj.isLoginError()) {
			userAccountPageObj.registerNewUser(testUser1Fname, testUser1Lname, testUser1Email);
			isNewUser = true;
		}
		return isNewUser;
	}
}
