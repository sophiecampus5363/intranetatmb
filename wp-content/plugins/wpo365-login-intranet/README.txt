=== Wordpress + Office 365 login premium ===
Contributors: wpo365
Tags: office 365, O365, azure active directory, Azure AD, AAD, authentication, single sign-on, sso, login, oauth, microsoft, microsoft graph, sharepoint online, sharepoint
Requires at least: 4.8.1
Tested up to: 5.2.2
Stable tag: 9.4
Requires PHP: 5.6.20

== Description ==

Wordpress + Office 365 login premium allows Microsoft O365 users to seamlessly and securely log on to your corporate Wordpress intranet: No username or password required. Why you need this, you may wonder. Because this way you can ensure that your corporate content such as news, documents etc. that is normally classified as "Internal" or maybe even "Confidential" is not available to just any pair of curious unauthenticated eyes!

= Plugin Features =

== BASIC (free) ==

- Single Sign-on for (manually registered) Office 365 / Azure AD accounts [more](https://www.wpo365.com/sso-for-office-365-azure-ad-user/)
- Make your WordPress (intranet) private [more](https://www.wpo365.com/make-your-wordpress-intranet-private/)
- Support for WordPress multisite [more](https://www.wpo365.com/support-for-wordpress-multisite-networks/)
- Client-side solutions can request access tokens e.g. for SharePoint Online and Microsoft Graph [more](https://www.wpo365.com/pintra-fx/)
- Developers can include a simple and robust API from [npm](https://www.npmjs.com/package/pintra-fx) [more](https://www.wpo365.com/pintra-fx/)
- Authors can inject Pintra Framework apps into any page or post using a simple WordPress shortcode [more](https://www.wpo365.com/pintra-fx/)

Now all versions include three new modern INTRANET apps

- SharePoint Online Library [more](https://www.wpo365.com/documents/)
- SharePoint Online Search [more](https://www.wpo365.com/content-by-search/)
- Employee Directory (Microsoft Graph / Azure AD) [more](https://www.wpo365.com/employee-directory/)

== PROFESSIONAL ==

- All features of the BASIC edition, plus ...
- Let users choose to login with O365 or with WordPress [more](https://www.wpo365.com/redirect-to-login/)
- Require authentication for only a few pages [more](https://www.wpo365.com/private-pages/)
- Require authentication for all pages but not for the homepage [more](https://www.wpo365.com/public-homepage/)
- Redirect users to a custom login error page [more](https://www.wpo365.com/error-page/)
- Automatically register any Office 365 / Azure AD user from your tenant [more](https://www.wpo365.com/sso-for-office-365-azure-ad-user/)
- Allow users from other Office 365 tenants to register (extranet) [more](https://www.wpo365.com/automatically-register-new-users-from-other-tenants/)
- Allow users with a Microsoft Services Account e.g. outlook.com to register (extranet) [more](https://www.wpo365.com/automatically-register-new-users-with-msal-accounts/)
- Prevent Office 365 user from changing their WordPress password and / or email address [more](https://www.wpo365.com/prevent-update-email-address-and-password/)
- Intercept manual login attempts for Office 365 users [more](https://www.wpo365.com/intercept-manual-login/)
- Sign out a user from Office 365 when signin out from your website [more](https://www.wpo365.com/intercept-manual-login/)

== PREMIUM ==

- All features of the PROFESSIONAL edition, plus ...
- Place a customizable "Sign in with Microsoft" link on a post, page or theme using a simple shortcode [more](https://www.wpo365.com/authentication-shortcode/)
- Enrich a user's WordPress / BuddyPress profile with information from Azure AD e.g. job title, department and mobile phone [more](https://www.wpo365.com/extra-buddypress-profile-fields-from-azure-ad/)
- Replace a user's default WordPress avatar with a profile image downloaded from Office 365 [more](https://www.wpo365.com/office-365-profile-picture-as-wp-avatar/)
- Dynamically assign WordPress user role(s) based on Azure AD group membership(s) [more](https://www.wpo365.com/role-based-access-using-azure-ad-groups/)
- Deny / allow access based on a user's Azure AD group membership(s) [more](https://www.wpo365.com/role-based-access-using-azure-ad-groups/)
- Quickly enroll new users to WordPress from Azure AD (per user or in batches) [more](https://www.wpo365.com/synchronize-users-between-office-365-and-wordpress/)
- Update WordPress user profiles and WordPress role(s) (per user or in batches) [more](https://www.wpo365.com/synchronize-users-between-office-365-and-wordpress/)
- Review WordPress users without a matching Azure AD account [more](https://www.wpo365.com/synchronize-users-between-office-365-and-wordpress/)

== INTRANET ==

- All features of the PREMIUM edition, plus advanced versions of the INTRANET apps
- SharePoint Online Library with support for folder and breadcrumb navigation [more](https://www.wpo365.com/documents/)
- SharePoint Online Search with support for query templates, auto-search, templates and [more](https://www.wpo365.com/content-by-search/)
- Employee Directory with support for user profile images and additional fields (Microsoft Graph / Azure AD) [more](https://www.wpo365.com/employee-directory/)

https://youtu.be/fNrwX24p1gU

= Prerequisites =

- Make sure that you have disabled caching for your Website in case your website is an intranet and access to WP Admin and all pubished pages and posts requires authentication. With caching enabled, the plugin may not work as expected
- We have tested our plugin with Wordpress 4.8.1 and PHP >= 5.6.25
- You need to be (Office 365) Tenant Administrator to configure both Azure Active Directory and the plugin
- You may want to consider restricting access to the otherwise publicly available wp-content directory

= Support =

We will go to great length trying to support you if the plugin doesn't work as expected. Go to our [Support Page](https://www.wpo365.com/how-to-get-support/) to get in touch with us. We haven't been able to test our plugin in all endless possible Wordpress configurations and versions so we are keen to hear from you and happy to learn!

= Feedback =

We are keen to hear from you so share your feedback with us at info@wpo365.com and help us get better!

== Installation ==

Please refer to [this post](https://www.wpo365.com/how-to-install-wordpress-office-365-login-plugin/) for detailed installation and configuration instructions.

== Frequently Asked Questions ==

== Screenshots ==

== Upgrade Notice ==

* v8.3 The previous solution to redirect users via /wp-admin in an attempt to bypass server-side cache (and avoid "Your login has been tampered with" errors) has been discontinued. Redirection to login.onmicrosoft.com is now delegated to a client-side solution. Please ensure that the WordPress REST API i.e. https://www.example.com/wp-json/wpo365/ is not blocked e.g. by basic auth, another plugin or your firewall.
* v8.0 As a result of switching to a client-side solution (to redirect users) the plugin no longer requires you to use the WordPress Admin URL as the Azure AD App registration Redirect URI. Instead you can now simply use your website's base website address e.g. https://www.example.com/ (but don't forget the trailing slash).
* v8.0 When the client-side solution (to redirect users) is not working as expected, you can enable a fallback that uses server-side redirection by checking the "Use server side redirect" option on the miscellaneous tab of the plugin's wizard. This option, however, does not try and bypass server-side cache and as a result you may start seeing "Your login has been tampered with" errors again.
* v8.0 When using the "Sign in with Microsoft" shortcode you're advised to upgrade to version 2 of the shortcode and take advantage of the new client-side redirection to work around server-side caching issues. See https://www.wpo365.com/authentication-shortcode/ for details.
* v8.0 When using the "Dual Login" feature (previously referred to as "Redirect to login") you are advised to start using version 2 of the template and take advantage of the new client-side redirection to work around server-side caching issues. See https://www.wpo365.com/redirect-to-login/ for details.
* v7.18 Please update your Azure AD App registration to avoid "Could not create or retrieve your login. Please contact your System Administrator." errors and users not being able to have their account created or login into the website. See https://www.wpo365.com/azure-application-registration/ for details.
* v7.18 When you still see "Could not create or retrieve your login" errors, you can enable a fallback that users the older ID token parser by chcking the "Use older ID token parser" option on the miscellaneous tab of the plugin's wizard.

There is a [new video](https://youtu.be/fNrwX24p1gU) available on YouTube that covers the updated configuration (for v7.18, not for v8.0) in detail.

== Changelog ==

= v9.4 =
* Improvement: An administrator can now configure the plugin to automatically assign users a WordPress role by creating one or more mappings between a (username's login) domain on the one side and a WordPress role on the other side. Visit https://www.wpo365.com/domain-roles-mappings/.
* Improvement: Added support for so-called Azure single sign out. Visit https://www.wpo365.com/enable-logout-without-confirmation/.
* Improvement: An administrator can now configure a domain hint to prevent users that are already logged on toanother Azure AD / Office 365 tenant from signing in with possibly the wrong Microsoft work or school account. Visit https://www.wpo365.com/domain-hint/.
* Improvement: The plugin, when receiving the authentication response from Microsoft, will now additionally search in WordPress for users by account name i.e. the user's principal name (= Office 365 login name) without the domain suffix. However, please be aware that some plugin features expect a WordPress username to be a legitimate Azure AD login name. Features not working when the WordPress user name is not a fully qualified Azure AD user principal name are the Avatar synchronization, mapping of Azure AD group memberships to WordPress roles and adding additional Office 365 user profile properties to a user's WordPress and / or BuddyPress profile as well as the deep integration in MS Graph and SharePoint Online.
* Improvement: Some 3rd party themes and plugins that hook into the user_register action e.g. to send an email with a confirmation link, would run into a fatal error when the action was triggered. This new configuration setting (on the Miscellaneous tab) - when checked - is a work-around to disable the action from being triggered (when a new user is created automatically by the plugin). Visit https://www.wpo365.com/skip-user-register-action/.
* Fix: Error "Undefined variable: resource Auth.php on line 774".

= v9.3 =
* Change: The plugin now ships with a built-in SharePoint Online Documents app (see https://www.wpo365.com/documents/). 
* Improvement: A new setting "Retrieve all group memberships" allows you to retrieve all sorts of groups memberships when synchronizing users instead of only the security-enabled group memberships.

= v9.2 =
* Fix: Now getting / setting WordPress transients take into account WordPress multisite to prevent "Your login has been tampered with" error when signing into a subsite (when authentication configuration is shared between all sites in the network).

= v9.1 =
* Improvement: Optionally you can specify your custom query when synchronizing users.
* Improvement: Optionally you can specify a Welcome Page URL where new users are sent after they signed on with Microsoft the very first time.
* Improvement: You can now (try to) activate your license.
* Fix: When redirecting, the plugin now writes a proper HTML document incl. doctype.
* Fix: The plugin now tries to obtain the initial URL the users intended to load on the client to preserve query parameters and fragments (hash).

= v9.0 =
* Change: The plugin now ships with a built-in SharePoint Online Search app (see https://www.wpo365.com/content-by-search/). 
* Change: The plugin now ships with a built-in Employee Directory app that queries Microsoft Graph (see https://www.wpo365.com/employee-directory/).
* Change: When using BuddyPress you can now instruct the plugin to show the Office 365 profile picture instead.
* Fix: When synchronizing users the plugin will now also update core user fields (email, first name, last name, display name).
* Fix: When synchronizing users the plugin will now also retrieve a user's Office 365 profile picture (if this feature is enabled and if an older version that has not yet expired is not found).
* Fix: If the plugin detects a different scheme between the Azure AD redirect URL and the URL the user navigated to before the SSO workflow started the plugin autocorrects the scheme (changes http:// to https://) to avoid infinite loops. An error will be generated in the log and the admin should take appropriate measures e.g. updating .htaccess to ensure the site automatically redirects to its secure version.

= 8.6 =
* Fix: The plugin will only (try to) retrieve additional user fields (from O365) if the user signed in with Microsoft (assumption made by analyzing the email domain).
* Fix: When the Dual Login feature is activated, the plugin now redirects the user to the WordPress site instead to initiate the login workflow.
* Fix: A typo caused the BASIC edition to cause a warning when trying to show the discount banner.
* Fix: When redirecting to Microsoft the plugin would sometimes not remember the state correctly, resulting in a login error.
* Fix: Cache buster for the wizard was not set correctly and therefore wizard updates were not immediately visible after an upgrade.
* Fix: More robust detection whether WordPress is loaded in an iframe.

= 8.5 =
* Change: Now the plugin will no longer require access to WP REST API or WP AJAX API. Instead the plugin adds an additional POST request to trigger the Single Sign-on workflow. This request uses a cache breaker to work-around server-side cache, allowing admins to configure the home url (instead of the WP Admin url) as a Redirect URI for the Azure AD App registration.
* Change: User synchronization no longer requires (unattended) access to the WP AJAX API. Instead the plugin will "loop" until all users found in Microsoft Graph have been processed. For the admin starting the synchronization this will appear as a synchronous action but in reality the synchronization is executed in batches of 10 users. By doing so the synchronization will not eventually time out (but as a drawback can also not be executed unattended).

= 8.4 =
* Fix: Removed the "too" opinionated validation of schemes used for redirect URI and WordPress URL.
* Fix: Improved the detection of HTTPS (but it is up to the administrator to ensure SSL is being enforced for the front and back end).
* Fix: Removed dead code.

= 8.3 =
* Change: Moved the custom API for users to obtain the Microsoft authentication endpoint e.g. login.microsoftonline.com to the WordPress REST API. Please ensure that this endpoint i.e. https://www.example.com/wp-json/wpo365/ is not blocked e.g. by basic auth, another plugin or your firewall.
* Change: If the custom (WP REST) API is not available to end users (e.g. because it is disabled or blocked) the user will see an error message and instructions on how to resolve the issue are printed to the developer console (F12).
* Change: The option to bypass the NONCE verification (at your own risk) to work around server-side cache has been re-activated. This options should only be used in combination with SSL.
* Change: The client-side redirect script will try and detect if it's being loaded in an iframe (which is by default not supported by Microsoft) and if this is the case it will try and open a popup instead. Please make sure popup blockers are disable for your domain, if you are trying to place your website in an iframe. For Internet Explorer / Edge please make sure that login.microsoftonline.com and your website are both added to the same security zone.
* Change: Logging has been improved with a filter to only show errors and error descriptions now offer more guidance on how they can be resolved.
* Fix: When WordPress multisite has been installed, the plugin will detect when the user changes the (sub) site (when the admin configured WPO_MU_USE_SUBSITE_OPTIONS (true)) and if this is the case signs out the user and eventually redirects the user to Microsoft to authenticate for the new (sub) site.

= 8.2 =
* Fix: WPO365 admin menu not available when WPO_MU_USE_SUBSITE_OPTIONS (true) has been configured.
* Fix: O365 user fields now requested using the user's principal name (upn) instead of email address.

= 8.1 =
* Fix: Compatibility with older browsers, specifically IE11.
* Fix: Added a plugcache breaker when loading pintra-redirectjs.

= 8.0 =
* Change: To work-around server-side caching the previous solution to redirect via /wp-admin has been discontinued. Instead the plugin will now output a short (cachable) JavaScript that will request the authentication URL from a custom WordPress AJAX service and redirect the user accordingly.
* Change: The way nonces are generated and validated has been changed to ensure that nonces are really used only once.
* Change: A version 2 of the "Sign-in with Microsoft" shortcode has been added to take advantage of the beforementioned client-side redirection to prevent server-side caching. Older "Sign-in with Microsoft" shortcode templates will continue to work but it is recommended that they are updated accordingly.
* Change: A version 2 of the "Dual Login" feature (= previously referred to as "Redirect to login")  has been added to take advantage of the beforementioned client-side redirection to prevent server-side caching. Older Dual Login templates will continue to work but it is recommended that they are updated accordingly.
* Change: The plugin now requires that the Azure AD "Redirect URI" and your WordPress (Site) Address use the same scheme e.g. http(s). If this is not the case it will show a "Plugin is not configured" error and will basically disable it self, to prevent infinite loops.
* Change: Debug log will now show the debug in descending order (latest entries first).
* Change: The plugin will now try and automatically add a trailing slash whenever it tries to redirect the user.
* Change: When using the "Dual Login" feature (= previously referred to as Redirect to login) the plugin will now remember the URL the user initially requested and redirect the user accordingly upon successful authentication.
* Change: The plugin's wizard "Test authentication" button has been removed. Instead the configuration is always saved and then tested. The authentication URL used for testing will now appear after clicking "Save configuration" since this URL (and the corresponding nonce) is generated server-side and must be unique.
* Fix: A legacy function to prevent client-side caching that generated unnecessary error log entries (and thus unnecessary warnings in WP admin) has been removed.

= 7.18 =
* Change: The plugin will regularly check the error log to see if recently new errors were logged and if so show a dismissable notice in the WordPress admin area.
* Change: The administrator can choose to surpress the error notice in the WordPress admin area.
* Fix: Improved the improved way of parsing the ID token (trying to get the user principal name first if available).
* Fix: The plugin would throw an previously uncaught exception when trying to log an event when the synchronization of users would fail.

= 7.17 =
* Change: Now that Microsoft has made the new Azure App registration portal General Available, the recommended Azure AD endpoint to use is v2.0 (see https://www.wpo365.com/azure-application-registration/)
* Change: The plugin now supports retrieving manager data (display name, email, telephone number(s), office location, country) of an O365 user through Microsoft Graph.
* Change: When configuring "Redirect to login" you can now choose to hide the SSO link which is otherwise shown above the login form.
* Change: You can now configure a custom login URL (which is automatically added to the Pages Blacklist).
* Fix: Improved way of parsing the ID token, avoiding unexpected WP user names, especially for Azure AD guests and users from other tenants.
* Fix: Display name property now correctly set when creating a new WP user using the information from the parsed ID token.
* Fix: Now the plugin will check - when multisite is activated - whether the logged in user autenticated for the current site and if not the user will be logged out and forced to authenticate again.
* Fix: WP user now created with a stronger default password.

= 7.16 =
* Fix: Improved caching of license check result to prevent it from impacting the overall website performance.
* Fix: Now the wizard is loaded with a cache breaker to ensure with each new plugin version the latest version shows immediately.
* Fix: White spaces at the beginning and end of configuration options that are strings are now properly trimmed.

= 7.15 =
* Change: Added software licensing and replaced automated upgrade with license key based solution (professional and premium version).
* Fix: Additional logging when synchronizing user (premium version).

= 7.14 =
* Change: Added an extra option (see Miscellaneous tab of the plugin's configuration wizard) to prevent the wp-login hook from being fired as it may cause an error in combination with some 3rd party themes.
* Fix: The plugin now recognize the super administrator (available only for WordPress multisite) as an administrator of (any) subsite.

= 7.13 =
* Fix: The plugin now checks whether a user is an administrator by verifying roles instead of capabilities.
* Fix: The plugin's URL cache now resolves the WordPress home URL instead of the site address for the website's front end home.
* Fix: The plugin now correctly recognizes a "bounced" request when preparing to redirect the user to Microsoft's authentication endpoint.

= 7.12 =
* Change: The plugin can be configured to skip authentication when requesting data from the WordPress REST API when a Basic authentication header is present (professional and premium editions only).
* Change: You can configure the plugin to skip nonce verification (however, it is not recommended to do so but instead find the root cause e.g. an aggressive server-side caching strategy).
* Change: User synchronization is now supported at the level of a (sub) site in a WordPress Multisite WPMU network (premium edition only).
* Change: User synchronization now checks user capabilities and won't delete users that have the administrator capability (premium edition only).
* Fix: Check for admin capabilities would not always return true for a WordPress Multisite WPMU Network.
* Fix: Due to a regression the number of user synced per batch was set to 1 instead of 10 (premium edition only).
* Fix: Manual login attempts will now be intercepted even when redirect to login is checked (professional and premium editions only).

= 7.11 =
* Change: User Synchronization is now executed in asynchronous batches of 25 users each until finished to prevent a timeout exception. As soon as the asynchronous user synchronization has finished the plugin will (try and) send an email to website's administrator (premium version only).
* Change: When you have selected the Intranet (Authentication) Scenario, you can check the "Public Homepage" option to allow anonymous access to the WordPress frontpage i.e. your website's home page (premium and professional version only).
* Change: A direct link to the WPO365 Wizard has been added to the Admin Dashboard Menu.
* Change: You can now toggle debug mode comfortably from the "Debug" tab that has been added to the plugin's configuration wizard. The debug log can now be viewed on that tab as well and you can copy the log to the clipboard.
* Change: The plugin now partially obscures a number of configuration secrets e.g. application ID, application secret, nonce etc.
* Change: The plugin's wizard has been enhanced with a number of warnings in the form of popups to provide more guidance when configuring the plugin.
* Fix: Synchronizing external users has been improved and the user name configured by the plugin is the external user's own email address (instead of the - sanitized - Azure AD User Principal Name) (premium version only).
* Fix: When a user - for any reason - cannot be created, the plugin would try and log that user's ID, causing an irrecoverable exception, which is now caught and logged adequately.

= 7.10 =
* Fix: Stricter validation of the Error Page URL and Pages Blacklist entries to ensure that the website is not accidently added (causing the plugin to skip authentication alltogether).
* Fix: Automatic update for the PROFESSIONAL edition failed.

= 7.9 =
* Fix: Custom error messages were ignored due to an error with the property's casing.
* Change: The professional and premium version now offer a "Redirect to login" option that when checked will send the user to the default WordPress login form (instead of the Microsoft) and on the login form a message will inform the user that he / she can also sign into the website using his / her Microsoft Office 365 / Azure AD account (and provide a link that when clicked will sign in the user with Microsoft)

= 7.8 =
* Fix: Auto-fix for bypassing server-side cache dind't work as expected.
* Change: The BASIC edition will now show an appropriate error message when user not found.
* Change: Added a short code that can be used on a custom error page to display the plugin's error message.

= 7.7 =
* Fix: Removed "Plugin not configured" error redirection which prevented users to logon with their WordPress-only admin account when then plugin was not yet configured.
* Fix: (Smoke) Tested against PHP 7.3.3 and replaced deprecated create_function call.

= 7.6 =
* Change: When you change the authentication scenario to "Internet" the Pages Blacklist will be replaced by a Private Pages list. Posts and Pages added to the new Private Pages list will only be accessible for authenticated users. If the user is authenticated, the plugin will try and sign in the user with Microsoft.
* Change: You can now configure an Error Page. When configured, the plugin will redirect the user to this page each time it runs into an error e.g. user not found, plugin not configured etc. If no Error Page is configured, the plugin will instead redirect the user to the default WordPress login form. The plugin will automatically skip the Error Page when authenticate a request (to avoid an infinite loop). The error code will be sent along as query string parameter and can be used to customize your own Error Page.
* Fix: Added MIME Type and Content Headers to the New User Notification email template.

= 7.5 =
* Change: The plugin can now be configured to send a (customizable) new user registration email. 

= 7.4 =
* Fix: If a user is not manually registered prior to trying to sign into the WordPress site with Microsoft, the user would end up in an infinite loop (only impacts basic version).
* Fix: Remove crossorigin from Pintra Fx template since this was causing an issue downloading react files from UNPKG CDN.

= 7.3 =
* Fix: A new setting "Don't try bypass (server side) cache" on the Miscellaneous Tab now controls whether the plugin will try and bypass the server side cache by redirecting the user first to /wp-admin before redirecting the user to Microsoft's Identity Provider.
* Fix: A new global constant WPO_MU_USE_SUBSITE_OPTIONS allows administrators of a WordPress multisite network to toggle between a "shared" scenario in which all subsites in the network share the same Azure AD application registration and a "dedicated" scenario in which all sites in the network will have to be configured individually. 

= 7.2 =
* Fix: Missing namespace import causing server error when user cannot be added successfully [professional, premium]

= 7.1 =
* Change: Now the plugin can redirect users based on their Azure AD Group Membership [premium]
* Fix: User synchronization would not work correctly with Graph Version set to beta
* Fix: Added support for wp_login hook
* Fix: Lowered priority when hooking into the wp_authenticate hook

= 7.0 =
* Plugin options are now managed through a new Wizard app that can be opened from the WordPress Plugins page where a new action link has been added to the wpo365-login plugin
* Support for configuring options through wp-config.php and Redux Platform has been discontinued (existing options will be upgraded automatically)
* Harmonized version number across all versions

= 5.3 =
* Change: Extra user fields will now show on a BuddyPress profile page as Directory Info
* Change: User synchronization will never update a user that is an administrator (the option *Do not update existing admins* has been deleted)
* Fix: User synchronization does not work for WordPress Multisite
* Fix: User synchronization for WordPress Multisite should only be available for the main (root) site

= 5.2 =
* Change: Removed the (Redux) WPO365 Option for scope
* Change: Support for Azure AD v2.0 authentication and access token requests (see separate upcoming post)
* Change: Updated the access token (AJAX) service API to support Azure AD v2.0 scope based token requests
* Change: Authorization, access and refresh codes and tokens are stored as JSON encoded standard classes now
* Change: Previously deprecated methods have been removed (other / third party plugins and apps must integrate using the API now)

= 5.1 =
* Fix: Msft_Graph::fetch may return WP_Error and the avatar function was not handling this correctly

= 5.0 =
* Change: A configuration option has been added to always redirect a user to a designated page upon signin into the website
* Change: A client (side) application can now request an oauth access token for any Azure AD secured resource e.g. Graph and SharePoint Online
* Change: A configuration option has been added to disable the aforementioned AJAX service for Azure AD oauth access tokens
* Change: A Configuration section has been added that allows administrators to define custom login error messages
* Change: Refresh tokens e.g. for Graph and SharePoint Online are now set to expire after 14 days
* Change: The plugin will now cache the Microsoft signin keys used to verify the incoming ID token for 6 hours to improve overall performance
* Change: The flow to obtain access tokens has been refactored and greatly simplied (existing methods have been marked deprecated)
* Fix: Dynamic role assignment will not add default role when user has existing role(s)

= 4.6 =
* Change: Pages Blacklist can now include query string parts e.g. "?api=" but administrators need to be aware that this can potentially weaken overall security [read more](https://www.wpo365.com/pages-blacklist/)

= 4.5 =
* Fix: user_nicename - a WP_User field that is limited to 50 characters - was wrongly set to a user's full name which under circumstances prevented a user from being created successfully

= 4.4 =
* Fix: Change PHP language construct to restore compatibility with PHP 5.3.29

= 4.3 =
* Change: An extra configuration option has been added to instruct the plugin to only try and add the default role if no other role(s) could be assiged i.e. no valid Azure AD to WordPress role mapping exists for that user
* Fix: Check before redirecting whether headers are sent and if yes the plugin now falls back to an alternative method to redirect

= 4.2 =
* Fix: Added missing plugin file in plugin archive

= 4.1 =
* The User Synchronization job will now show additionally a list of existing WordPress users with an Office 365 account
* When running User Synchronization you can choose to update existing WordPress users - if properly configured - the plugin will retrieve 1) additional user information from Microsoft Graph and 2) evaluate the Office 365 Azure AD Security Groups to WordPress roles mappings (and assign new roles when needed)
* When creating new WordPress by either running a User Synchronization job or by manually clicking "Create" - if properly configured - the plugin will retrieve 1) additional user information from Microsoft Graph and 2) evaluate the Office 365 Azure AD Security Groups to WordPress roles mappings (and assign new roles when needed)
* The plugin is now capable of assigning multiple roles to a user and when doing so it will either first delete any existing roles before assigning new ones or instead preserve existing roles prior to adding new ones
* The setting "Update user role" has been deprecated and instead the plugin will always try to update the user's role in one of two possible modes: "add" (default) or "replace"
* The plugin offers a new setting that let's you choose between replacing all existing roles with new ones or instead only add any possible new roles (default behaviour)
* Now that the plugin is capable of assigning multiple roles it will always (at least) add the default role for the main site as per configuration before adding any applicable roles as per Office 365 Azure AD Security Group to WordPress role mappings
* The HTML template for the "Sign in with Microsoft" shortcode can be customized in the shortcode body (see documentation)
* When the premium plugin is activated it will check whether the "personal blog / free" version is still activated and if yes try and deactivate it

= 4.0 =
* Additional Office 365 user fields e.g. Job Title, Mobile Phone etc. can now be changed by the user in WordPress (not synchronized back to Office 365 Azure AD)
* Use a WordPress shortcode [wpo365-sign-in-with-microsoft-sc] to place a login link on your site wherever you want
* When synchronizing users with Office 365 Azure AD the plugin will try and retrieve additional user information immediately
* Now you can supply a list of semi-colon separated own domains (e.g. "wpo365.com;wp-o365.com") to support enterprises that have mapped multiple domains to their Office 365 tenant
* Select the preferred Microsoft Graph version i.e. v1.0 or beta (which is experimental but returns - for example - more user fields)
* Moved the JWT class into the Wpo namespace (to avoid class loading issues)
* Added psr-4 type auto class loading
* Code refactoring to allow for the [SharePoint Online Plugin](https://wordpress.org/plugins/wpo365-spo/) and other extensions to re-use existing code base

= 3.2 =
* Now Azure AD group to WordPress role mappings can be used to check and possibly update a user's WordPress role dynamically each time a user signs into your website.

= 3.1 =
* Now you can logout from Office 365 when you logout from WordPress
* Added support for multi tenanted (Azure AD) applications to support extranet scenarios

= 3.0 =
* Added user synchronization feature.

= 2.4 =
* Added an option to force WordPress to sent no-cache headers when the global variable WPO_NOCACHE has been defined and set to true e.g. <code>define( 'WPO_NOCACHE', true );</code>
* Fixed an issue where the plugin tried to read the session duration from a global variable without a fallback option to a Redux variable causing unnecessary token refresh roundtrips
* Fixed a minor bug where the plugin not checked whether an error occurred when creating a new WP user

= 2.3 =
* Added plugin update checker. Please add a new wp-config setting WPO_LOGIN_DOWNLOAD_LINK (Redux: Downloads > wpo365-login download link) and copy the download link you have received when purchasing the plugin
* Fixed an issue with array function dereferencing
* Removed email settings
* Minor refactoring

= 2.2 =
* Fixed an issue in the Auth class (line 96) where the PHP language construct empty() was given a function but it can only handle variables


= 2.1 =
* Fixed an issue with the Aure AD Groups Whitelist - When an Azure AD Groups Whitelist was configured, a user was required to be a member of all groups rather than just one
* Added license file
* Updated README

= 2.0 =
* Enhances a user's profile with additional fields from Microsoft Graph (mobilePhone, businessPhones, officeLocation, jobTitle)
* new wp-config.php settings WPO_GRAPH_USER_DETAILS (true|false) to enable|disable retrieving and showing additional user fields from Microsoft Graph
* Fixed an issue with the avatar always showing the profile picture from the current user
* Fixed an issue when retrieving global boolean variables that were set to false
* Fixed an issue when exploding an empty string which returned a non-empty array

= 1.7 =
* Replaced array construct to remain compatibel with older PHP versions
* Now the plugin decides to prepend https to the state property based on the protocol used for the redirect url. Some WordPress hosters use SLL terminating proxies, causing default WordPress SSL detection to fail. This may cause the plugin to redirect the user after login to the wrong website address starting with http instead of https and this eventually may lead to the user being caught in an infinite authentication loop.
* Simplified the nonce algorithm

= 1.6 =
* Replaced the nonce algorithm to avoid too much dependency on current URL (e.g. with or without www)
* New wp-config.php setting WPO_NONCE_SECRET
* Fixed error related to callback for destroy_wpo365_session action

= 1.5 =
* Prevent email and password changes exclusively for Office 365 users only
* Forward any manual login request from an Office 365 user to Microsoft
* new wp-config.php settings WPO_CUSTOM_DOAMIN (string), WPO_DEFAULT_DOMAIN (string), WPO_INTERCEPT_WP_LOGIN (true|false) and WPO_GOTO_AFTER_SIGNON_URL (string)

= 1.4 =
* Replaces a user's default WordPress avatar with the Office 365 (O365) profile picture and caches it
* new wp-config.php settings WPO_USE_AVATAR (true|false) and WPO_AVATAR_REFRESH (1296000)

= 1.3 =
* Div. bug-fixes

= 1.2 =
* Added a configurable leeway time to account for clock skew when checking the id token validity
* new wp-config.php setting WPO_LEEWAY (300)

= 1.1 =
* Limit access by Office 365 or Azure AD Security Group (new wp-config.php setting WPO_GROUPS_WHITELIST)
* Create mappings between Office 365 or Azure AD Security Groups (new wp-config.php setting WPO_GROUP_MAPPINGS)

= 1.0 =
* Plugin's necessary configuration can new also be added to wp-config.php as an alternative to using Redux. This improves overall performance and increases security in case of a multisite environment.
* The "Enable debug" option has been removed and instead the WP_DEBUG switch in wp-config.php is used.
* The (Azure AD) "Application Name" configuration option has been removed because it was unused.
