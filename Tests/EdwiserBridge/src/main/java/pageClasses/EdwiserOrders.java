package pageClasses;

import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;

import setUp.GeneralisedProjectOperations;

public class EdwiserOrders {

	WebDriver driver;
	GeneralisedProjectOperations generalisedOps = new GeneralisedProjectOperations();
	WebDriverWait wait;

	// Constructor
	public EdwiserOrders(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	// List of Orders Links on Order Listing Page
	@FindBy(xpath = "//tbody[@id='the-list']//strong/a")
	public List<WebElement> allOrdersLinksListingPage;

	// First Order From Orders Page from DashBoard
	@FindBy(xpath = "(//td[@data-colname='Order Title']/strong/a)[1]")
	public WebElement firstOrder;

	// Order Status Field
	@FindBy(id = "order_status")
	public WebElement orderStatus;

	// Order Status Select
	public Select orderStatusSelect() {
		return new Select(orderStatus);
	}

	// Course Publish Button
	@FindBy(id = "publish")
	public WebElement publishButton;

	// Order Course Name
	@FindBy(xpath = "//*[@class='eb-order-meta-details']/div[2]/a")
	public WebElement orderCourseName;

	// Order User Name
	@FindBy(xpath = "//*[@class='eb-order-meta-byer-details']/div[1]/label")
	public WebElement orderUserName;

	/**
	 * FUNCTION TO COMPLETE COURSE ORDER FROM DASHBOARD
	 */
	public void completeOrder(String baseURL, String userName, String courseName) throws Exception {
		wait = new WebDriverWait(driver, 8);

		// Visit Orders Page from DashBoard
		driver.get(baseURL + "wp-admin/edit.php?post_type=eb_order");

		// Visit First Order
		firstOrder.click();

		// Wait for Visibility of Order Status DropDown
		wait.until(ExpectedConditions.visibilityOf(orderStatus));

		// Check Course Name
		Assert.assertEquals(orderCourseName.getText(), courseName, "Course Not Matched in Order Edit Page");

		// Check UserName
		// System.out.println(orderUserName.getText());
		// Assert.assertEquals(orderUserName.getText(), userName, "Course Not
		// Matched in Order Edit Page");

		// Set order Status as Completed
		orderStatusSelect().selectByValue("completed");

		// Update Order
		publishButton.click();

		// Wait for Order Updation
		wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath("//*[@id='message']/p")));
	}

}
