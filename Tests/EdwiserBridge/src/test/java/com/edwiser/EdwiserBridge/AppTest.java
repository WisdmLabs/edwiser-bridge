package com.edwiser.EdwiserBridge;

import java.util.List;
import java.util.concurrent.TimeUnit;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.interactions.Actions;

public class AppTest {
	WebDriver driver;
	String straccesstoken;
	String servname = "MyExternalService1";
	String MoodleUrl= "http://localhost/moodle34";

//	@Parameters({ "moodlesiteurl", "moodleusername", "moodlepassword"})
	@BeforeClass 
	public void driverinit( )
			throws InterruptedException {
		System.setProperty("webdriver.chrome.driver", "/home/yogesh/workspace/chromedriver");
		driver = new ChromeDriver();
		// Set the implicit wait time now so that system will run smoothly
				driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);

				// maximize the window
//				driver.manage().window().maximize();
		driver.get(MoodleUrl+"/login/index.php");
		driver.findElement(By.id("username")).sendKeys("admin");
		driver.findElement(By.id("password")).sendKeys("Something@58259");
		driver.findElement(By.id("loginbtn")).click();
		Thread.sleep(3000);
	}


//	@Test(priority = 0)
//	public void tc1() throws InterruptedException {
//
//		driver.findElement(By.xpath("//*[contains(text(),'Site administration')]")).click();
//		driver.findElement(By.xpath("//*[@class = 'nav-link' and text()='Plugins']")).click();
//		driver.findElement(By.xpath("//*[text()='External services']")).click();
//		driver.findElement(By.xpath("//*[text()='Add']")).click();
//		driver.findElement(By.id("id_name")).sendKeys(servname);
//		driver.findElement(By.id("id_enabled")).click();
//		driver.findElement(By.id("id_restrictedusers")).click();
//		driver.findElement(By.id("id_submitbutton")).click();
//		driver.findElement(By.xpath("//*[text()='Add functions']")).click();
//		Thread.sleep(3000);
//	}
//
//	// driver.findElement(By.className("form-control")).sendKeys("core_user_create_users");
//	// driver.findElement(By.xpath("//li[contains(@id,form_autocomplete_suggestions)
//	// and contains(text(),'core_user_create_users')]")).click();
//
//	@Test(priority = 1)
//	public void tc2() throws InterruptedException {
//		String[] s1 = { "core_user_create_users", "core_user_get_users_by_field", "core_user_update_users",
//				"core_course_get_courses", "core_course_get_categories", "enrol_manual_enrol_users",
//				"enrol_manual_unenrol_users", "core_enrol_get_users_courses" };
//
//		int size = s1.length;
//		for (int i = 0; i < size; i++) {
//			driver.findElement(By.className("form-control")).clear();
//			driver.findElement(By.className("form-control")).sendKeys(s1[i]);
//			Thread.sleep(500);
//			driver.findElement(By.xpath("//li[contains(text(),'" + s1[i] + "')]")).click();
//		}
//		Thread.sleep(3000);
//		driver.findElement(By.xpath("//*[@name='submitbutton']")).click();
//	}

	@Test(priority = 2)
	public void tc3() throws InterruptedException {
		driver.get(MoodleUrl+"/admin/settings.php?section=externalservices");
		Thread.sleep(3000);
		driver.findElement(By.xpath("//*[text()='MyExternalService1']/../..//a[text()='Authorised users']")).click();
		driver.findElement(By.xpath("//*[text()='Admin User (yogesh.deore@wisdmlabs.com)']")).click();
		driver.findElement(By.id("add")).click();
	}

	@Test(priority = 3)
	public void tc4() throws InterruptedException {
		driver.get(MoodleUrl+"/admin/category.php?category=webservicesettings");

		if (driver
				.findElements(By.xpath("//*[text()='REST protocol']/../..//img[@class='iconsmall' and @alt='Enable']"))
				.size() != 0) {

			driver.findElement(By.xpath("//*[text()='REST protocol']/../..//img[@class='iconsmall' and @alt='Enable']"))
					.click();
		}

	}

	@Test(priority = 4)
	public void tc5() throws InterruptedException {
		driver.get(MoodleUrl+"/admin/category.php?category=webservicesettings");
		driver.findElement(By.xpath("//*[@id='webservicetokens']/../..//*[text()='Add']")).click();
		String s2 = "Admin User";
		driver.findElement(By.xpath("//input[@role='combobox']")).sendKeys(s2);
		Thread.sleep(500);
		driver.findElement(By.xpath("//*[@class='form-autocomplete-suggestions']/li[text() ='" + s2 + "']")).click();
		Thread.sleep(3000);
		Select selectobj = new Select(driver.findElement(By.id("id_service")));
		selectobj.selectByVisibleText("MyExternalService1");
		driver.findElement(By.id("id_submitbutton")).click();
		driver.get(MoodleUrl+"/admin/settings.php?section=webservicetokens");
		straccesstoken = driver.findElement(By.xpath("//*[@class='lastrow']/*[@class='leftalign cell c0']")).getText();
	}

	@Test(priority = 5)
	public void tc6() throws InterruptedException {
		driver.get(MoodleUrl+"/admin/search.php");
		driver.findElement(By.xpath("//a[text()='Advanced features']")).click();
		boolean b1 = driver.findElement(By.id("id_s__enablewebservices")).isSelected();
		if (b1 == false) {
			driver.findElement(By.id("id_s__enablewebservices")).click();
		}
		driver.findElement(By.xpath("//*[text()='Save changes']")).click();
	}

	@Test(priority = 6)
	public void tc7() throws InterruptedException {
		driver.get(MoodleUrl+"/admin/search.php");
		driver.findElement(By.xpath("(//ul[@class='list-unstyled']/li/a[text()='Site policies'])[1]")).click();
		Thread.sleep(3000);
		boolean b2 = driver.findElement(By.id("id_s__passwordpolicy")).isSelected();
		WebDriverWait wait = new WebDriverWait(driver, 10);
		WebElement element = wait.until(ExpectedConditions.elementToBeClickable(By.id("id_s__passwordpolicy")));

		if (b2 == true) {
			driver.findElement(By.id("id_s__passwordpolicy")).click();
		}
		driver.findElement(By.xpath("//*[text()='Save changes']")).click();
		driver.findElement(By.className("dropdown-toggle")).click();
		driver.findElement(By.xpath("//a[@class='dropdown-item menu-action']/span[text()='Log out']")).click();

	}
	
	@Parameters({ "wordpresssiteurl", "wordpressusername", "wordpresspassword"})
	@Test(priority = 7)
	public void tc8(String wordpresssiteurl, String wordpressusername, String wordpresspassword ) 
		throws InterruptedException {
		driver.get(wordpresssiteurl);
		driver.findElement(By.id("user_login")).sendKeys(wordpressusername);
		driver.findElement(By.id("user_pass")).sendKeys(wordpresspassword);
		driver.findElement(By.id("wp-submit")).click();
	}

	@Test(priority = 8)
	public void tc9() throws InterruptedException {
		Actions builder = new Actions(driver);
		WebElement web_Element_To_Be_Hovered = driver.findElement(By.linkText("Edwiser Bridge"));
		builder.moveToElement(web_Element_To_Be_Hovered).build().perform();
		Thread.sleep(3000);
		WebElement web_Element_To_Be_Clicked = driver
				.findElement(By.xpath("html/body/div[1]/div[1]/div[2]/ul/li[9]/ul/li[5]/a"));
		builder.moveToElement(web_Element_To_Be_Clicked).click().build().perform();

		boolean b3 = driver.findElement(By.id("eb_enable_registration")).isSelected();
		if (b3 == false) {
			driver.findElement(By.id("eb_enable_registration")).click();
		}
		Select sel = new Select(driver.findElement(By.id("eb_useraccount_page_id")));
		sel.selectByVisibleText("User Account");
		Thread.sleep(3000);
		driver.findElement(By.className("button-primary")).click();

		driver.findElement(By.xpath("//*[text()='Connection Settings']")).click();
		driver.findElement(By.id("eb_url")).clear();
		driver.findElement(By.id("eb_url")).sendKeys("MoodleUrl+");
		driver.findElement(By.id("eb_access_token")).clear();
		driver.findElement(By.id("eb_access_token")).sendKeys(straccesstoken);
		driver.findElement(By.id("eb_test_connection_button")).click();
		String expectedMessage = "Connection successful, Please save your connection details.";
		String message = driver.findElement(By.xpath("//*[@class='alert alert-success']")).getText();
		Assert.assertTrue(message.contains(expectedMessage));
		driver.findElement(By.className("button-primary")).click();

		driver.findElement(By.xpath("//a[text()='PayPal Settings']")).click();
		driver.findElement(By.id("eb_paypal_email")).clear();
		driver.findElement(By.id("eb_paypal_email")).sendKeys("yog.deore@wisdmlabs.com");
		Select sel1 = new Select(driver.findElement(By.id("eb_paypal_currency")));
		sel1.selectByVisibleText("U.S. Dollar (USD)");
		driver.findElement(By.id("eb_paypal_country_code")).clear();
		driver.findElement(By.id("eb_paypal_country_code")).sendKeys("U.S.");
		driver.findElement(By.id("eb_paypal_cancel_url")).clear();
		driver.findElement(By.id("eb_paypal_cancel_url")).sendKeys("http://manasi.wp/wordpress");
		driver.findElement(By.id("eb_paypal_return_url")).clear();
		driver.findElement(By.id("eb_paypal_return_url"))
				.sendKeys("http://manasi.wp/wordpress/thank-you-for-purchase/");
		Boolean b4 = driver.findElement(By.id("eb_paypal_sandbox")).isSelected();
		if (b4 == false) {
			driver.findElement(By.id("eb_paypal_sandbox")).click();
		}
		driver.findElement(By.xpath("//*[@type='submit']")).click();
	}

	@Test(priority = 9)
	public void tc10() throws InterruptedException {
		driver.findElement(By.xpath("//*[text()='Synchronization']")).click();

		boolean b5 = driver.findElement(By.id("eb_synchronize_categories")).isSelected();
		if(b5==false) {
			driver.findElement(By.id("eb_synchronize_categories")).click();
		}

		boolean b6 = driver.findElement(By.id("eb_synchronize_previous")).isSelected();
		if(b6==false) {
			driver.findElement(By.id("eb_synchronize_previous")).click();
		}
		
		boolean b7 = driver.findElement(By.id("eb_synchronize_draft")).isSelected();
		if(b7==false) {
			driver.findElement(By.id("eb_synchronize_draft")).click();
		}
		driver.findElement(By.id("eb_synchronize_courses_button")).click();
	}
	
	
	
	@Test(priority = 10)
	public void tc11() throws InterruptedException {
		driver.findElement(By.xpath("//*[@class='form-content']/../..//*[text()='Users']")).click();
		if(!driver.findElement(By.id("eb_synchronize_user_courses")).isSelected()) {
			driver.findElement(By.id("eb_synchronize_user_courses")).click();
		}
		
		driver.findElement(By.id("eb_synchronize_users_button")).click();
		String expectedMessage = "User data synced successfully.";
		String message = driver.findElement(By.xpath("//*[@class='alert alert-success']/p[text()='User data synced successfully.']")).getText();
		Assert.assertTrue(message.contains(expectedMessage));
		
}
}