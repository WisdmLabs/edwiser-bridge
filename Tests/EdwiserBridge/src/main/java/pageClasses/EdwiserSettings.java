package pageClasses;

import java.util.List;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import setUp.GeneralisedProjectOperations;

public class EdwiserSettings {

	WebDriver driver;
	GeneralisedProjectOperations generalisedOps = new GeneralisedProjectOperations();
	WebDriverWait wait;

	// Constructor
	public EdwiserSettings(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	// General Settings Tab
	@FindBy(xpath = "//a[text()='General']")
	public WebElement generalSettings;

	// Connection Settings Tab
	@FindBy(xpath = "// a[text()='Connection Settings']")
	public WebElement connectionSettings;

	// Synchronization Settings Tab
	@FindBy(xpath = "// a[text()='Synchronization']")
	public WebElement synchronizationSettings;

	// PayPal Settings Tab
	@FindBy(xpath = "// a[text()='PayPal Settings']")
	public WebElement paypalSettings;

	// License Tab
	@FindBy(xpath = "// a[text()='Licenses']")
	public WebElement licenseSettings;

	// Save Settings
	@FindBy(xpath = "//*[@value='Save changes']")
	public WebElement saveChanges;

	// Admin Message
	@FindBy(xpath = "//*[@id='message']/p/strong")
	public WebElement adminMessage;

	// Enable Redirect To My Courses
	@FindBy(id = "eb_enable_my_courses")
	public WebElement redirectToMyCourses;

	// My Courses Page Select
	@FindBy(id = "eb_my_courses_page_id")
	public WebElement myCoursesPage;

	// Enable User Registration
	@FindBy(id = "eb_enable_registration")
	public WebElement userRegistration;

	// UserAccount Page Select
	@FindBy(id = "eb_useraccount_page_id")
	public WebElement userAccountPage;

	// Edwiser Language Code
	@FindBy(id = "eb_language_code")
	public WebElement ebLanguageCode;

	// Courses Per Row
	@FindBy(id = "courses_per_row")
	public WebElement coursesPerRow;

	// Moodle URL
	@FindBy(id = "eb_url")
	public WebElement moodleUrl;

	// Moodle Access Token
	@FindBy(id = "eb_access_token")
	public WebElement moodleAccessToken;

	// Test Connection Button
	@FindBy(id = "eb_test_connection_button")
	public WebElement testConnection;

	// Test Connection Response Message
	@FindBy(xpath = "//*[@class='response-box']/div")
	public WebElement connectionResponse;

	// Courses Tab In Synchronization Setting
	@FindBy(xpath = "//*[@class='form-content']//a[text()='Courses']")
	public WebElement coursesTab;

	// Users Tab In Synchronization Setting
	@FindBy(xpath = "//*[@class='form-content']//a[text()='Users']")
	public WebElement usersTab;

	// Synchronize Categories CheckBox
	@FindBy(id = "eb_synchronize_categories")
	public WebElement synchronizeCategories;

	// Synchronize Previous Courses CheckBox
	@FindBy(id = "eb_synchronize_previous")
	public WebElement synchronizePrevious;

	// Synchronize Courses ad Draft CheckBox
	@FindBy(id = "eb_synchronize_draft")
	public WebElement synchronizeDraft;

	// Start Courses Synchronization Button
	@FindBy(id = "eb_synchronize_courses_button")
	public WebElement startCourseSynchronization;

	// Start User Synchronization Button
	@FindBy(id = "eb_synchronize_users_button")
	public WebElement startUserSynchronization;

	// Start User Synchronization Button
	@FindBy(id = "eb_synchronize_user_courses")
	public WebElement synchronizeUserCourses;

	// PayPal Email
	@FindBy(id = "eb_paypal_email")
	public WebElement paypalEmail;

	// PayPal SandBox CheckBox
	@FindBy(id = "eb_paypal_sandbox")
	public WebElement paypalSandbox;

	// PayPal Currency DropDown
	@FindBy(id = "eb_paypal_currency")
	public WebElement paypalCurrency;

	// PayPal Country Code
	@FindBy(id = "eb_paypal_country_code")
	public WebElement paypalCountryCode;

	// Extension Link
	@FindBy(xpath = "//div[@class='form-content']//a")
	public WebElement extensionLink;

	// Edwiser Courses Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Courses']")
	public WebElement coursesMenu;

	// Edwiser Course Categories Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Course Categories']")
	public WebElement courseCategoriesMenu;

	// Edwiser Orders Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Orders']")
	public WebElement ordersMenu;

	// Edwiser Settings Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Settings']")
	public WebElement settingsMenu;

	// Edwiser Manage Email Templates Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Manage Email Templates']")
	public WebElement manageEmailTemplatesMenu;

	// Edwiser Manage Enrollment Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Manage Enrollment']")
	public WebElement manageEnrollmentMenu;

	// Edwiser Extensions Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//a[text()='Extensions']")
	public WebElement extensionsMenu;

	// Edwiser Help Admin Menu
	@FindBy(xpath = "//*[@id='toplevel_page_edwiserbridge_lms']//div[text()='Help']/..")
	public WebElement helpMenu;

	// Extensions Page Browse all extensions Link
	@FindBy(xpath = "//a[contains(text(),'Browse all extensions')]")
	public WebElement browseAllExtensions;

	// Extensions Page Browse All Our extensions Link
	@FindBy(xpath = "//a[@class='browse-all']")
	public WebElement browseAllOurExtensions;

	// All Extensions List
	@FindBy(xpath = "//ul[@class='extensions']//li")
	public List<WebElement> allExtensionsList;

	/**
	 * Visit Edwiser Bridge General Settings
	 * 
	 * @param baseURL
	 */
	public void visitGeneralSettings(String baseURL) {
		driver.get(baseURL + "wp-admin/admin.php?page=eb-settings");
	}

	/**
	 * Test Connection Settings
	 * 
	 * @param baseURL
	 */
	public String testConnectionSettings(String moodleSiteURL, String moodleToken) {
		wait = new WebDriverWait(driver, 15);

		// Wait For Setting Visible
		wait.until(ExpectedConditions.elementToBeClickable(connectionSettings));

		// Visit Connection Settings
		connectionSettings.click();

		// Wait For Moodle URL Field Visible
		wait.until(ExpectedConditions.visibilityOf(moodleUrl));

		// Fill Moodle URL
		moodleUrl.clear();
		moodleUrl.sendKeys(moodleSiteURL);

		// Fill Moodle Token
		moodleAccessToken.clear();
		moodleAccessToken.sendKeys(moodleToken);

		// Click Test Connection Button
		testConnection.click();

		// Wait For Response
		wait.until(ExpectedConditions.visibilityOf(connectionResponse));

		// Return Response Message
		return connectionResponse.getText();

	}

	/**
	 * Test Connection Settings
	 * 
	 * @param baseURL
	 */
	public String courseSynchronizationSettings(boolean courseCategory, boolean update, boolean draft) {
		wait = new WebDriverWait(driver, 15);

		// Wait For Setting Visible
		wait.until(ExpectedConditions.elementToBeClickable(synchronizationSettings));

		// Visit Synchronization Settings
		synchronizationSettings.click();

		// Wait For Course tab Visibility
		wait.until(ExpectedConditions.visibilityOf(coursesTab));

		// Synchronize Categories
		if (courseCategory) {
			if (!synchronizeCategories.isSelected()) {
				synchronizeCategories.click();
			}
		} else if (synchronizeCategories.isSelected()) {
			synchronizeCategories.click();
		}

		// Update Synchronized Courses
		if (update) {
			if (!synchronizePrevious.isSelected()) {
				synchronizePrevious.click();
			}
		} else if (synchronizePrevious.isSelected()) {
			synchronizePrevious.click();
		}

		// Make Synchronized Courses as Draft
		if (draft) {
			if (!synchronizeDraft.isSelected()) {
				synchronizeDraft.click();
			}
		} else if (synchronizeDraft.isSelected()) {
			synchronizeDraft.click();
		}

		// Click Start Synchronization Button
		startCourseSynchronization.click();

		// Wait For Response
		wait.until(ExpectedConditions.visibilityOf(connectionResponse));

		// Return Response Message
		return connectionResponse.getText();
	}
}
