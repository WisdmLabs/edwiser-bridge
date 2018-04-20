package setUp;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

public class ProjectSetUpOperations {

	GeneralisedProjectOperations generalOperationsObject = new GeneralisedProjectOperations();

	public void loginToAdminDashboard(WebDriver driver, String siteURL, String username, String password)
			throws Exception {
		// Get the admin login page first
		driver.get(siteURL + "/wp-login.php");
		// Wait for web element to appear
		// We will wait for user login text box, if that gets loaded properly,
		// the web page is ready
		WebDriverWait wait = new WebDriverWait(driver, 15);
		wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("user_login")));

		// Clear and fill the username and password fields and click on submit
		// button
		Thread.sleep(500);
		generalOperationsObject.findAndClearTextWebElement(driver, "ID", "user_login");
		generalOperationsObject.findAndSendKeysToTextWebElement(driver, "ID", "user_login", username);
		Thread.sleep(200);
		generalOperationsObject.findAndClearTextWebElement(driver, "ID", "user_pass");
		generalOperationsObject.findAndSendKeysToTextWebElement(driver, "ID", "user_pass", password);
		generalOperationsObject.findAndClickOnWebElement(driver, "ID", "wp-submit");
	}

	public void logOut(WebDriver driver, String baseUrl) {
		JavascriptExecutor executor = (JavascriptExecutor) driver;

		// Get the My Account Page First
		driver.get(baseUrl + "user-account");

		// Wait for web element to appear
		WebDriverWait wait = new WebDriverWait(driver, 15);

		// Click LogOut link if it is Present

		if (generalOperationsObject.findAndReturnWebElements(driver, "xpath", "//a[text()='Sign out']").size() != 0) {
//			wait.until(ExpectedConditions.elementToBeClickable(By.xpath("//a[text()='Sign out']")));
			WebElement logOut = generalOperationsObject.findAndReturnWebElement(driver, "xpath",
					"//a[text()='Sign out']");
			
			wait.until(ExpectedConditions.elementToBeClickable(logOut));
			// Click Sign Out
			executor.executeScript("arguments[0].click();", logOut);
//			logOut.click();		
			}
	}

}
