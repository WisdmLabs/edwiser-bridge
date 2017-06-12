=== Edwiser Bridge - WordPress Moodle LMS Integration === 
Contributors: WisdmLabs
Tags: WordPress, Moodle, Courses, Users, Synchronization, Sell Courses, Learning Management System, LMS, LMS Integration, Moodle WordPress, WordPress Moodle, WP Moodle,
Requires at least: 4.0
Tested up to: 4.7
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


Edwiser Bridge integrates WordPress with Moodle LMS & provides an easy option to import and sell Moodle courses using WordPress.

 ==  Description == 

Edwiser Bridge integrates WordPress with the Moodle LMS. The plugin provides an easy option to import Moodle courses to WordPress and sell them using PayPal. The plugin also allows automatic registration of WordPress users on the Moodle website along with single login credentials for both the systems.

 = Import Courses as Drafts to WordPress = 
Courses can be imported from the Moodle learning management system to WordPress and can be saved as drafts.

 = Sell Moodle Courses from WordPress = 
Moodle courses can be sold from WordPress using the PayPal payment gateway.

 = Automatic Registration on the Moodle LMS = 
An account will be created on Moodle for all users registering on WordPress. 

 = Identical Login Credentials for Registered User = 
Login credentials for accounts created on WordPress and Moodle will be the same. 

 = Enable/ Disable Registration to Courses = 
Registration to all courses can be enabled or disabled at once from the settings page. 

 = Create Course Categories in WordPress = 
Course categories can be created in WordPress and can be assigned to courses imported from Moodle.

 = Synchronize Course Categories from WordPress = 
Course categories from Moodle can be imported to WordPress and can be assigned to courses that have been imported from Moodle.

 = Synchronize Enrolled Courses Data for Users = 
User's course enrollment status can be updated and synchronized between the two systems to display the same information. 

 = View Purchased Course Data in WordPress = 
Admin can view a history of purchased courses in the 'Orders' section at the backend of the WordPress website. 

 = Set Language Code as on the Moodle Site = 
The default language that the Moodle website needs to be displayed can be set in the settings page on the WordPress website.

 = Update Previously Synchronized Courses = 
For previously synchronized courses, changes in courses on the Moodle website can be reflected on the WordPress website by updating the courses. 

 = Enroll / Unenroll Students from WordPress = 
WordPress settings have been provided to enroll and unenroll users from courses. The revised enrollment status will be reflected on both WordPress as well as Moodle. 

 = Link and Unlink Registered Users = 
An account on WordPress is linked with an account on Moodle for synchronization purposes. These accounts can be linked or delinked using the bulk actions drop down on the Users page at the back end. 

 = Ready for Translation to any Language = 
The plugin can be displayed in a required language by adding the necessary .mo & .po files to the languages folder in the plugin. 

 = Hooks and Filters for Customization = 
Various hooks and filters have been provided in Edwiser Bridge easing the customization process for developers.

 = Email Template customization =
Edit the content in email templates sent out from the plugin to users who purchase the course.

= My Courses page =
A dedicated page which displays every user the list of courses they have purchased and based on their selection, recommends them other courses on offer.

= Set course access expiry period =
Set the course expiry date on course backend and upon course expire, users are automatically unenrolled from the course on WordPress and Moodle.

 = Premium Extensions = 
**WooCommerce Integration for Edwiser Bridge**

Want to use the power of WooCommerce to sell your Moodle courses from WordPress? Take a look at the WooCommerce Integration for Edwiser Bridge that takes you through the WooCommerce Moodle Integration seamlessly.

<a href = "https://edwiser.org/bridge/extensions/woocommerce-integration/">WooCommerce Integration for Edwiser Bridge</a>

**Single Sign On for Edwiser Bridge**

The Single Sign On extension for Edwiser Bridge facilitates simultaneous login to WordPress and Moodle by entering login credentials only once.

<a href = "https://edwiser.org/bridge/extensions/single-sign-on/">Single Sign On for Edwiser Bridge</a>

**Selective Synchronization for Edwiser Bridge**

Selectively synchronize Moodle courses or courses belonging to a particular category using the Selective Syncronization extension for Edwiser Bridge.

<a href = "https://edwiser.org/bridge/extensions/selective-synchronization/">Selective Synchronization for Edwiser Bridge</a>


 ==  Installation  == 

 = Minimum Requirements = 
* PHP version 5.6 or greater
* WordPress 4.7 or higher
* Moodle 2.9 or higher

 =  Automatic Installation  = 
* Go to the Plugins menu from the dashboard. 
* Click on the 'Add New' button on this page.
* Search for 'Edwiser Bridge' in the search bar provided. 
* Click on 'Install Now' once you have located the plugin.
* On successful installation click the 'Activate Plugin' link to activate the plugin. 

 =  Manual Installation  = 
* Download the Edwiser Bridge plugin from wordpress.org. 
* Now unzip and upload the folder using the FTP application of your choice.
* The plugin can then be activated by navigating to the Plugins menu in the admin dashboard. 

 = Moodle Configuration = 
Take a look at the link below and follow the steps provided to configure your Moodle website. 
<a href = "https://edwiser.org/bridge/documentation/#tab-b540a7a7-e59f-3">Moodle Website Configurations</a>


== Screenshots == 
1. General Settings for Edwiser Bridge
2. Connection Settings for Edwiser Bridge
3. Course Synchronization from Moodle to WordPress
4. User Enrollment Data Synchronization
5. Imported moodle courses in the WordPress Backend
6. Order Details of Courses Purchased from WordPress
7. User Profile Shortcode with List of Enrolled Courses
8. Courses page Shortcode.
9. My Courses page Shortcode.
10. Redirect the user to the My Courses page on login/registration from the User Account page.
11. Setting to manage 'Max number of courses in the row' for the Courses page template.
12. Single course page template.
13. A student can update their profile details from the frontend. 
14. Setting to set the course access period of the enrolled user.
15. Edit email notification template's content.
16. Manage user enrollment.

 ==  Frequently Asked Questions  == 

 = Do WordPress and Moodle need to be installed and running on the same server? = 
No, not required. Both the systems can be installed on the same server or different servers.

 = Which course details are imported on synchronizing courses from Moodle to WordPress? = 
When courses are imported from Moodle the course title, description and feature image are imported to WordPress.

 = Can one WordPress website be used with multiple Moodle websites? = 
No, this is not possible using Edwiser Bridge.

Take a look at the link below to see the full list of questions which will help you around the Edwiser Bridge plugin. 
<a href = "https://edwiser.org/bridge/faqs/">Frequently Asked Questions for Edwiser Bridge</a>

 ==  Changelog  == 

= 1.2.2 =
* Feature - New page for the admin to manage user enrollment.
* Feature - New email template for the moodle account creation.
* Feature - Functionality to disable email notifications.
* Feature - Functionality to place the order for the free course to maintain the purchase history.
* Tweak- Added the order and Buyer details in order page.
* Tweak- User gets un-enroll from the course on the order status marked from completed to pending or failed.
* Tweak- Updated the user order details on the user account page and added the order status column.
* Fix - User not getting created on moodle when username contains the uppercase characters.
* Fix - My courses shortcode showing only 10 courses not more than that.


= 1.2.1 =
* Feature - Functionality to display the users moodle account link unlink status on users list table for the admin user.
* Fix - Fix for the plugins default data update on plugin update.

=  1.2.0  =
* Feature - Functionality to set course access expiry days.
* Feature - Introduced functionality to email template customization for the mail sent to the user from the Edwiser Bridge plugin.
* Feature - Update Moodle as well as WordPress user profile from shortcode `eb_user_account`.
* Feature - Added the settings to enable login redirection to my courses page.
* Feature - Send the test email to check the modified email template.
* Feature - Shortcode `eb_courses` to list courses.
* Feature - Shortcode `eb_my_courses` to list specific user's courses.
* Feature - Shortcode `eb_course` to show single course. Argument `id` i.e Course ID decides which course to show.
* Tweak - Added page(My Courses) to display the user's enrolled courses.
* Tweak - Redirect user to my courses page on login.
* Tweak - Translation ready - fix missing strings.
* Tweak - Notification on un-enrollment from the course.
* Tweak - Archive course page layout improvement.
* Tweak - Single course page layout improvement.
* Tweak - Redirect non-logged in user to the `Access course`/`Checkout` page on click of the `Take this Course` after login.
* Tweak - Deprecated shortcode eb_user_profile. Use shortcode `eb_user_account`.
* Fix - Changed Credentials spelling.
* Fix - Undefined index: HTTP_REFFER.
* Fix - Permalink issue.
* Fix - Call to undefined class EBPaymentManager.


=  1.1.2  =
* Added new currency in PayPal for the Australian Dollar, Polish Zloty, Danish Krone and Singapore Dollar.
* Fix - Resolved Paypal Sandbox mode issue.

=  1.1.1  = 
* Fix - Minor issue in page creation functionality.

=  1.1  = 
* Fix - Issue in overriding templates in themes.
* Tweak - Unified licensing section for all Edwiser Bridge extensions.
* Tweak - Refactored & optimized whole plugin codebase using tools like PHPCS & PHPMD.

 =  1.0.2  = 
* Feature - Added a new shortcode [eb_user_profile] which creates a user profile page that lists users data & enrolled courses.
* Fix - Timeout problem in course enrollment.
* Fix - A bug that was preventing plugin translation.
* Tweak - Minor modifications to improve enrollment & synchronization process.

 =  1.0.1  = 
* Fix - A bug that could cause problems on password reset & user enrollment process

 =  1.0  = 
* Plugin Launched

## Upgrade Notice ##

### 1.1.1 ###
1.1 is a major update so it is important that you make backups, and ensure all extensions are version 1.1 compatible for proper functionality across Edwiser Bridge & its extensions.