=== SMTP by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: smtp, smtp plugin, smtp mail, email, mail, mail ssl, mail tls, phpmailer, send email via smtp, mailer, test email, add smtp plugin
Requires at least: 3.8
Tested up to: 4.7.3
Stable tag: 1.1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Configure SMTP server to receive email messages from WordPress to Gmail, Yahoo, Hotmail, and other services.

== Description ==

Simple plugin to configure SMTP server to receive email messages from your WordPress website to Gmail, Yahoo, Hotmail, and others.

Install, configure, test, and save your time!

http://www.youtube.com/watch?v=KsBBg57YC-A

= Features =

* Send test email
* Display test email sending log
* Customize “From” field:
	* Name
	* Email
* Set SMTP:
	* Host
	* Port
* Choose SMTP secure connection:
	* None
	* SSL
	* TLS
* Enable authentication with:
	* Username
	* Password
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

= Documentation & Videos =

* [[Doc] Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)
* [[Doc] How to Use](https://docs.google.com/document/d/1u2QAHYmoeRMYDD8eq_8uCiKob7x86ms1lJhmQlwWEpw/)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help — <https://support.bestwebsoft.com/>

= Translation =

* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](http://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](http://www.poedit.net/download.php).

= Recommended Plugins =

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=1d159d662eeb8f066701430a8681c9d8) - Automatically check and update WordPress core with all installed plugins to the latest versions. Manual mode, email notifications and backups of all your files and database before updating.

== Installation ==

1. Upload the `bws-smtp` folder to `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "BWS Panel" > "SMTP".

[View a Step-by-step Instruction on SMTP by BestWebSoft Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)

== Frequently Asked Questions ==

= Why is my email not sent? =

Please make sure that the data specified on the plugin settings page is correct. In case you are using such mail services as Gmail, Yahoo, or other, you need to contact your mail service support team to get the correct settings.

= I've adjusted the settings the right way, and yet the email messages still fail to be sent =

Please try sending a test email, having marked a "Display log" checkbox. You will then see a log, in which you can find the bug that triggers sending failures.

= On the plugin settings page, I see 'Not Confirmed' in the Settings Status block. Why? =

To confirm that the settings are correct, you need to send a test email first. When the test email is sent successfully, please click "Settings are Correct" button.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<https://support.bestwebsoft.com>). If no, please provide the following data along with your problem's description:

1. the link to the page where the problem occurs
2. the name of the plugin and its version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste into the message your system status report. Please read more here: [Instruction on System Status](https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/)

== Screenshots ==

1. Plugin settings page.
2. Sending a test message page.

== Changelog ==

= V1.1.0 - 14.04.2017 =
* Bugfix : Multiple Cross-Site Scripting (XSS) vulnerability was fixed.

= V1.0.9 - 08.02.2017 =
* Update : Plugin optimization completed.

= V1.0.8 - 12.10.2016 =
* Update : BWS plugins section is updated.

= V1.0.7 - 08.07.2016 =
* Update : BWS panel section is updated.

= V1.0.6 - 26.05.2016 =
* NEW : An ability to use data from 3rd party plugins for "From" field.
* Update : All functionality for wordpress 4.5.2 was updated.

= V1.0.5 - 09.12.2015 =
* Bugfix : The bug with plugin menu duplicating was fixed.

= V1.0.4 - 26.10.2015 =
* Update : Textdomain was changed.
* Update : We updated all functionality for wordpress 4.3.1.

= V1.0.3 - 02.07.2015 =
* NEW : Ability to restore settings to defaults.

= V1.0.2 - 15.05.2015 =
* Update : BWS plugins section is updated.
* Update : We updated all functionality for wordpress 4.2.2.

= V1.0.1 - 13.03.2015 =
* Bugfix : Bug with plugin's option when sending email was fixed.
* Update : BWS plugins section was updated.

= V1.0.0 - 09.02.2015 =
* NEW : The SMTP by BestWebSoft plugin is ready for use.

== Upgrade Notice ==

= V1.1.0 =
* Bugs fixed.

= V1.0.9 =
* Plugin optimization completed.

= V1.0.8 =
* Plugin optimization completed.

= V1.0.7 =
BWS panel section is updated.

= V1.0.6 =
An ability to use data from 3rd party plugins for "From" field. All functionality for wordpress 4.5.2 was updated.

= V1.0.5 =
The bug with plugin menu duplicating was fixed.

= V1.0.4 =
Textdomain was changed. We updated all functionality for wordpress 4.3.1.

= V1.0.3 =
Ability to restore settings to defaults.

= V1.0.2 =
BWS plugins section is updated. We updated all functionality for wordpress 4.2.2.

= V1.0.1 =
Bug with plugin's option when sending email was fixed. BWS plugins section was updated.

= V1.0.0 =
The SMTP by BestWebSoft is ready for use.
