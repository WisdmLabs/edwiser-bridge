package setUp;

import java.util.ArrayList;
import java.util.concurrent.TimeUnit;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
// import org.openqa.selenium.phantomjs.PhantomJSDriver;
// import org.openqa.selenium.phantomjs.PhantomJSDriverService;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.testng.annotations.AfterSuite;
import org.testng.annotations.BeforeSuite;
import org.testng.annotations.Parameters;

/**
 * Prerequisites : 1.The plugin is installed and active 2.Store URL should be
 * set properly and the key should be of same store URL 4.Number of products to
 * be listed on product listing page should be more enough to display all the
 * products so that we can compare them directly 5.The correct course
 * names(which are synchronized) to be supplied from testng.xml file
 */
public class projectSetUp {

	public static WebDriver driver;
	static ArrayList<String> cliArgsCap = new ArrayList<String>();

	/*
	 * This method is used for first setup of the project here we will login to
	 * the system, create some products, etc.
	 */
	@Parameters({ "browser" })
	@BeforeSuite
	public void driverSetUp(String browser) {

		// Now initiate the webdriver
		if (browser.equals("Firefox")) {
			// Set the gecko property so that it will not give security warning
			System.setProperty("webdriver.gecko.driver", "/home/yogesh/workspace/geckodriver");
			driver = new FirefoxDriver();
		} else if (browser.equals("Chrome")) {
			// Set the chrome driver system property
			// System.setProperty("webdriver.chrome.driver", "/home/yogesh/eclipse-workspace/Drivers/chromedriver");
			driver = new ChromeDriver();
// 		} else if (browser.equals("PhantomJS")) {
// 			DesiredCapabilities capabilities = DesiredCapabilities.phantomjs();
// 			cliArgsCap.add("--web-security=false");
// 			cliArgsCap.add("--ssl-protocol=any");
// 			cliArgsCap.add("--ignore-ssl-errors=true");
// 			capabilities.setCapability("takesScreenshot", false);
// 			capabilities.setJavascriptEnabled(true);
// 			 // capabilities.setCapability(PhantomJSDriverService.PHANTOMJS_EXECUTABLE_PATH_PROPERTY,"/usr/local/bin/phantomjs");
// 			capabilities.setCapability(PhantomJSDriverService.PHANTOMJS_CLI_ARGS, cliArgsCap);
// 			capabilities.setCapability(PhantomJSDriverService.PHANTOMJS_GHOSTDRIVER_CLI_ARGS,
// 					new String[] { "--logLevel=2" });
// 			driver = new PhantomJSDriver(capabilities);
// //			driver.manage().window().setSize(new Dimension(1024,768));
			
		}

		// Set the implicit wait time now so that system will run smoothly
		driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);

		// maximize the window
//		driver.manage().window().maximize();

	}

	/**
	 * Close the Driver
	 */
	@AfterSuite
	public void AfterClass() {
		driver.quit();

	}
}