package pageClasses;

import java.util.List;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

import setUp.GeneralisedProjectOperations;

public class EdwiserCourseEditPage {

	WebDriver driver;

	GeneralisedProjectOperations generalisedOps = new GeneralisedProjectOperations();
	WebDriverWait wait;

	// Constructor
	public EdwiserCourseEditPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	// Admin Message
	@FindBy(xpath = "//*[@id='message']//p")
	public WebElement adminMessage;

	// Course Title
	@FindBy(id = "title")
	public WebElement courseTitle;

	// Course ParmaLink
	@FindBy(xpath = "//*[@id='sample-permalink']/a")
	public WebElement courseViewLink;

	// Course Description
	@FindBy(id = "tinymce")
	public WebElement courseDiscription;

	// Course Publish Button
	@FindBy(id = "publish")
	public WebElement publishButton;

	// Moodle Course Id
	@FindBy(xpath = "//*[@id='moodle_course_id']/b")
	public WebElement moodleCourseId;

	// Course Price Type
	@FindBy(id = "course_price_type")
	public WebElement coursePriceType;

	// Course Price
	@FindBy(id = "course_price")
	public WebElement coursePrice;

	// Course Optional URL
	@FindBy(id = "course_closed_url")
	public WebElement courseOptionalUrl;

	// Course Expire CheckBox
	@FindBy(id = "course_expirey")
	public WebElement courseExpiry;

	// Expire Access After (days) Field
	@FindBy(id = "num_days_course_access")
	public WebElement numOfDaysForExpiry;

	// Course Short Description
	@FindBy(id = "course_short_description")
	public WebElement courseShortDescription;

	// Select All From Listing Page
	@FindBy(id = "cb-select-all-1")
	public WebElement selectAllFromListing;

	// Bulk Actions Select
	@FindBy(id = "bulk-action-selector-top")
	public WebElement bulkActions;

	// Bulk Actions Apply
	@FindBy(id = "doaction")
	public WebElement bulkActionsApply;

	// Trash Link
	@FindBy(xpath = "//a[contains(text(),'Trash')]")
	public WebElement trashLink;

	// Empty Trash
	@FindBy(id = "delete_all")
	public WebElement emptyTrash;

	// List of Course Links on Product Listing Page
	@FindBy(xpath = "//tbody[@id='the-list']//strong/a")
	public List<WebElement> allCoursessLinksListingPage;

	// All Categories Rows
	@FindBy(xpath = "//tr[contains(@id,'tag')]")
	public List<WebElement> allCategoriesRows;

	// Bulk Action Select
	public Select bulkActionsSelect() {
		return new Select(bulkActions);
	}

	// Course Type Select
	public Select courseTypeSelect() {
		return new Select(coursePriceType);
	}

	// Course listing Page course Link
	public WebElement userNameLink(String courseName) {
		return generalisedOps.findAndReturnWebElement(driver, "xpath", "//a[text()='" + courseName + "']");
	}

	/**
	 * Trash All Courses
	 */
	public void trashCourses() {
		// Select All Course
		selectAllFromListing.click();
		// Select Trash From Bulk Actions
		bulkActionsSelect().selectByValue("trash");
		// Apply
		bulkActionsApply.click();
	}

	/**
	 * Delete
	 */
	public void deleteCourses(String baseURL) {
		// Visit Courses Listing Page
		generalisedOps.visitURL(baseURL + "wp-admin/edit.php?post_type=eb_course");

		if (allCoursessLinksListingPage.size() > 0) {
			// Trash All
			trashCourses();

			// Visit Trash
			trashLink.click();
			// Click Empty Trash
			emptyTrash.click();
		}
	}

	/**
	 * Return Course Count
	 * 
	 * @param status
	 * @return
	 */
	public String coursesCountwithStatus(String baseURL, String status) {
		// Visit Courses Listing Page
		generalisedOps.visitURL(baseURL + "wp-admin/edit.php?post_type=eb_course");

		// Getting Count
		String count = generalisedOps
				.findAndReturnWebElement(driver, "xpath", "//div[@class='wrap']//a[contains(text(),'" + status + "')]")
				.getText().substring(status.length());
		return count;
	}

	/**
	 * Delete All Course Categories
	 */
	public void deleteAllCategories(String baseURL) {
		// Visit Categories Listing Page
		generalisedOps.visitURL(baseURL + "wp-admin/edit-tags.php?taxonomy=eb_course_cat&post_type=eb_course");
		if (allCategoriesRows.size() > 0) {
			// Select All Course
			selectAllFromListing.click();
			// Select Trash From Bulk Actions
			bulkActionsSelect().selectByValue("delete");
			// Apply
			bulkActionsApply.click();
		}
	}
}
