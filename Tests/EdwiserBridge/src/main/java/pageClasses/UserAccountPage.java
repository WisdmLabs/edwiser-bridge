package pageClasses;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import setUp.GeneralisedProjectOperations;

public class UserAccountPage {
	WebDriver driver;
	GeneralisedProjectOperations generalisedOps = new GeneralisedProjectOperations();
	WebDriverWait wait;

	// Constructor
	public UserAccountPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	// Registration Link
	@FindBy(xpath = "//*[@class='register-link']/a")
	public WebElement registrationLink;

	// User Name field
	@FindBy(id = "wdm_username")
	public WebElement userNameField;

	// Password field
	@FindBy(id = "wdm_password")
	public WebElement userPasswordField;

	// Login Button
	@FindBy(name = "wdm_login")
	public WebElement loginButton;

	// Sign Out Link
	@FindBy(xpath = "//div[@class='eb-user-data']//a")
	public WebElement signOutLink;

	// Edit Profile Link
	@FindBy(xpath = "//*[@class='eb-edit-profile']/a")
	public WebElement editProfileLink;

	// Edit UserName Field
	@FindBy(id = "username")
	public WebElement editUserName;

	// Edit First Name Field
	@FindBy(id = "first_name")
	public WebElement editFirstName;

	// Edit Last Name Field
	@FindBy(id = "last_name")
	public WebElement editLastName;

	// Edit Nick Name Field
	@FindBy(id = "nickname")
	public WebElement editNickName;

	// Edit Email Field
	@FindBy(id = "email")
	public WebElement editEmail;

	// Edit Password Field
	@FindBy(id = "pass_1")
	public WebElement editPassword;

	// Edit Biographical Information Field
	@FindBy(id = "description")
	public WebElement editBiographicalInformation;

	// Edit City Field
	@FindBy(id = "city")
	public WebElement editCity;

	// Select Country Field
	@FindBy(id = "country")
	public WebElement editCountry;

	// Update User Button
	@FindBy(id = "updateuser")
	public WebElement updateUserButton;

	// User Update Message
	@FindBy(xpath = "//section[contains(@class,'eb-user-info')]/p[2]")
	public WebElement userUpdateMessage;

	// User Registration First Name
	@FindBy(id = "reg_firstname")
	public WebElement regFirstname;

	// User Registration Last Name
	@FindBy(id = "reg_lastname")
	public WebElement regLastname;

	// User Registration Email Name
	@FindBy(id = "reg_email")
	public WebElement regEmail;

	// User Registration Register Button Name
	@FindBy(name = "register")
	public WebElement registerButton;

	// Check User is Enrolled in Specific Course from User Account Page
	public boolean userIsEnrolledInSpecificCourse(String course) {
		if (generalisedOps.findAndReturnWebElements(driver, "xpath",
				"//*[@class='eb-course-data']/div/div/a[text()='" + course + "']").size() > 0)
			return true;
		else
			return false;
	}

	// Check Login Error
	public boolean isLoginError() {
		if (generalisedOps.findAndReturnWebElements(driver, "xpath", "//*[@class='wdm-flash-error']/span").size() > 0)
			return true;
		else
			return false;
	}

	/**
	 * WordPress Register user from User-Account Page
	 * 
	 * @param Fname
	 * @param Lname
	 * @param UserEmail
	 * @throws Exception
	 */
	public void registerNewUser(String Fname, String Lname, String UserEmail) {
		JavascriptExecutor executor = (JavascriptExecutor) driver;

		// Click Registration Link
		executor.executeScript("arguments[0].click();", registrationLink);
		// registrationLink.click();

		// Adding User Details
		regFirstname.clear();
		regFirstname.sendKeys(Fname);
		regLastname.clear();
		regLastname.sendKeys(Lname);

		regEmail.clear();
		regEmail.sendKeys(UserEmail);

		// Click Register Button
		registerButton.click();
	}

	/**
	 * User Login From User Account Page
	 * 
	 * @param userName
	 * @param password
	 */
	public void loginFromUserAccountPage(String userName, String password) {
		JavascriptExecutor executor = (JavascriptExecutor) driver;
		wait = new WebDriverWait(driver, 10);

		// Add User Name
		wait.until(ExpectedConditions.visibilityOf(userNameField));
		userNameField.clear();
		userNameField.sendKeys(userName);

		// Add User Password
		userPasswordField.clear();
		userPasswordField.sendKeys(password);

		// Click Login Button

		wait.until(ExpectedConditions.elementToBeClickable(loginButton));
		executor.executeScript("arguments[0].click();", loginButton);
		// loginButton.click();
	}

}
