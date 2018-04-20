package pageClasses;

import java.util.List;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import setUp.GeneralisedProjectOperations;

public class UserListingAndEditPage {
	WebDriver driver;
	GeneralisedProjectOperations generalisedOps = new GeneralisedProjectOperations();
	WebDriverWait wait;

	// Constructor
	public UserListingAndEditPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	// Bulk Actions Select
	@FindBy(id = "bulk-action-selector-top")
	public WebElement bulkActions;

	// Bulk Actions Apply
	@FindBy(id = "doaction")
	public WebElement bulkActionsApply;

	// Bulk Action Select
	public Select bulkActionsSelect() {
		return new Select(bulkActions);
	}

	// Add New User Button from
	@FindBy(xpath = "//div[@class='wrap']/a[text()='Add New']")
	public WebElement addNewUserLink;

	// Admin Message
	@FindBy(xpath = "//*[@id='message']//p")
	public WebElement adminMessage;

	// Add New User Page User Name Field
	@FindBy(id = "user_login")
	public WebElement userName;

	// Add New User Page User Email Field
	@FindBy(id = "email")
	public WebElement userEmail;

	// Add New User Page User First Name Field
	@FindBy(id = "first_name")
	public WebElement userFirstName;

	// Add New User Page User Last Name Field
	@FindBy(id = "last_name")
	public WebElement userLastName;

	// User Biographical Info Field
	@FindBy(id = "description")
	public WebElement biographicalInfo;

	// Add New User Page Show Password Button
	@FindBy(xpath = "//*[text()='Show password']")
	public WebElement showPassword;

	// Add New User Page User Password Field
	@FindBy(id = "pass1-text")
	public WebElement passwordText;

	// Add New User Page weak Password checkbox
	@FindBy(xpath = "//input[@class='pw-checkbox']")
	public WebElement weakPassword;

	// Add New User Page weak Password checkbox
	@FindBy(id = "createusersub")
	public WebElement createUserButton;

	@FindBy(id = "submit")
	public WebElement saveUser;

	// User Link Unlink Message
	@FindBy(xpath = "//div[@class='updated']/p")
	public WebElement userLinkUnlinkMessage;

	// User Link Unlink Message
	@FindBy(xpath = "//div[@id='moodleLinkUnlinkUserNotices']/p")
	public WebElement moodleLinkUnlinkUserNotices;

	// Enroll Courses DropDown
	@FindBy(name = "enroll_course")
	public WebElement enrollCourse;

	// Unenroll Courses DropDown
	@FindBy(name = "unenroll_course")
	public WebElement unenrollCourse;

	@FindBy(xpath = "//ul[@class='extensions']//li")
	public List<WebElement> allExtensionsList;

	// User Edit Link from User Listing Page
	public WebElement userEditLink(String userName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath", "//a[text()='" + userName + "']");
	}

	// Checkbox in front of User
	public WebElement userCheckbox(String UserName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath",
				"//a[text()='" + UserName + "']/../../..//input[@type ='checkbox']");
	}

	// Link User in front of User
	public WebElement linkUser(String UserName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath",
				"//a[text()='" + UserName + "']/../../..//*[@class='link-unlink' and text() ='Link User']");
	}

	// UnLink User in front of User
	public WebElement unlinkUser(String UserName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath",
				"//a[text()='" + UserName + "']/../../..//*[@class='link-unlink' and text() ='Unlink User']");
	}

	// Check User is Present Or Not
	public boolean findUser(String UserName) {
		if (generalisedOps.findAndReturnWebElements(driver, "xpath", "//a[text()='" + UserName + "']").size() > 0) {
			return true;
		} else {
			return false;
		}
	}

	// User listing Page UserName Link
	public WebElement userNameLink(String UserName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath", "//a[text()='" + UserName + "']");
	}

	// Check User is Linked Or Not
	public boolean checkUserLinkStatus(String UserName) {
		if (unlinkUser(UserName).isDisplayed()) {
			return true;
		} else {
			return false;
		}
	}

	// Enroll Course DropDown
	public Select enrollCourseSelect() {
		return new Select(enrollCourse);
	}

	// Unenroll Course DropDown
	public Select unenrollCourseSelect() {
		return new Select(unenrollCourse);
	}

	// Check User is Enrolled in Course
	public boolean checkUserEnrolledInCourse(String Course) {

		if (generalisedOps.findAndReturnWebElements(driver, "xpath",
				"//h3[text()='Enrolled Courses']/../..//a[contains(text(),'" + Course + "')]").size() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Enroll To Course
	 * 
	 * @param courseName
	 */
	public void enrollToCourse(String courseName) {
		// Check if already Enrolled
		if (checkUserEnrolledInCourse(courseName)) {
			unenrollfromCourse(courseName);
		}
		// Select Course
		enrollCourseSelect().selectByVisibleText(courseName);
		// Save User
		saveUser.click();

		// Check User is Updated Or Not
		Assert.assertTrue(adminMessage.getText().contains("User updated"), "User not Updated");

	}

	/**
	 * Unenroll From Course
	 * 
	 * @param courseName
	 */
	public void unenrollfromCourse(String courseName) {
		// Check if not Enrolled
		if (!checkUserEnrolledInCourse(courseName)) {
			enrollToCourse(courseName);
		}
		// Select Course
		unenrollCourseSelect().selectByVisibleText(courseName);
		// Save User
		saveUser.click();

		// Check User is Updated Or Not
		Assert.assertTrue(adminMessage.getText().contains("User updated"), "User not Updated");
	}

	/**
	 * Create User from DashBoard
	 * 
	 * @param UserName
	 * @param FName
	 * @param LName
	 * @param UserEmail
	 */
	public void createUser(String baseURL, String UserName, String FName, String LName, String UserEmail) {
		// Visit Users Page from DashBoard
		driver.get(baseURL + "wp-admin/users.php");
		// check if User Exists
		if (!findUser(UserName)) {
			// Click Add New User
			addNewUserLink.click();

			// Add User Name
			userName.sendKeys(UserName);

			// Add First Name
			userFirstName.sendKeys(FName);

			// Add Last Name
			userLastName.sendKeys(LName);

			// Add Email
			userEmail.sendKeys(UserEmail);

			// View Password
			showPassword.click();

			// Add Password
			passwordText.clear();
			passwordText.sendKeys(UserName);

			// Conform Weak Password
			weakPassword.click();

			// Click Save
			createUserButton.click();

			// Check User Saved Message
			Assert.assertTrue(adminMessage.getText().contains("New user created"), "User not created");
		}
	}

}
