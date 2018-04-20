package setUp;

import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import setUp.projectSetUp;

/**
 * In this class we are running all the setup methods like to create the
 * products and doing some initial settings in the project
 * 
 */
public class GeneralisedProjectOperations {

	WebDriver driver;

	/**
	 * This is to return the webelement
	 * 
	 * @param driver
	 * @param findType
	 * @param findValue
	 * @return
	 */
	public WebElement findAndReturnWebElement(WebDriver driver, String findType, String findValue) {
		WebElement foundElement = null;
		if (findType.equalsIgnoreCase("ID")) {
			foundElement = driver.findElement(By.id(findValue));
		} else if (findType.equalsIgnoreCase("NAME")) {
			foundElement = driver.findElement(By.name(findValue));
		} else if (findType.equalsIgnoreCase("CLASS")) {
			foundElement = driver.findElement(By.className(findValue));
		} else if (findType.equalsIgnoreCase("XPATH")) {
			foundElement = driver.findElement(By.xpath(findValue));
		} else if (findType.equalsIgnoreCase("CSS")) {
			foundElement = driver.findElement(By.cssSelector(findValue));
		}
		return foundElement;
	}

	/**
	 * This is to return the set of webelements
	 * 
	 * @param driver
	 * @param findType
	 * @param findValue
	 * @return
	 */
	public List<WebElement> findAndReturnWebElements(WebDriver driver, String findType, String findValue) {
		List<WebElement> foundElements = null;
		if (findType.equalsIgnoreCase("ID")) {
			foundElements = driver.findElements(By.id(findValue));
		} else if (findType.equalsIgnoreCase("NAME")) {
			foundElements = driver.findElements(By.name(findValue));
		} else if (findType.equalsIgnoreCase("CLASS")) {
			foundElements = driver.findElements(By.className(findValue));
		} else if (findType.equalsIgnoreCase("XPATH")) {
			foundElements = driver.findElements(By.xpath(findValue));
		} else if (findType.equalsIgnoreCase("CSS")) {
			foundElements = driver.findElements(By.cssSelector(findValue));
		}
		return foundElements;
	}

	/**
	 * This is to find the webelement and after finding it click on that found
	 * element
	 * 
	 * @param driver
	 * @param findType
	 * @param findValue
	 * @param webElement
	 */
	public void findAndClickOnWebElement(WebDriver driver, String findType, String findValue) {
		WebElement findElement = findAndReturnWebElement(driver, findType, findValue);
		findElement.click();
	}

	/**
	 * This method is for clearing the text element
	 * 
	 * @param driver
	 * @param findType
	 * @param findValue
	 */
	public void findAndClearTextWebElement(WebDriver driver, String findType, String findValue) {
		WebElement findElement = findAndReturnWebElement(driver, findType, findValue);
		findElement.clear();
	}

	/**
	 * This is for entering the text in the text field
	 * 
	 * @param driver
	 * @param findType
	 * @param findValue
	 * @param sendValue
	 */
	public void findAndSendKeysToTextWebElement(WebDriver driver, String findType, String findValue, String sendValue) {
		WebElement findElement = findAndReturnWebElement(driver, findType, findValue);
		findElement.sendKeys(sendValue);
	}

	/**
	 * To send the keys to the known element
	 * 
	 * @param driver
	 * @param webElement
	 * @param sendValue
	 */
	public void sendKeysToTextWebElement(WebDriver driver, WebElement webElement, String sendValue) {
		webElement.sendKeys(sendValue);
	}

	public WebElement waitForWebElement(WebDriver driver, WebElement webElement, int timeOutInSeconds) {
		WebDriverWait wait = new WebDriverWait(driver, timeOutInSeconds);

		webElement = wait.until(ExpectedConditions.elementToBeClickable(webElement));
		return webElement;
	}

	public void acceptAlert(WebDriver driver) {
		driver.switchTo().alert().accept();
	}

	/**
	 * This is for visiting the URL provided to this method
	 */
	public void visitURL(String visitingURL) {
		WebDriver driver = projectSetUp.driver;
		driver.get(visitingURL);
	}

	public void clearTextWebElement(WebElement providedTextBox) {
		providedTextBox.clear();

	}

}
