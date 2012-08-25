<?php
define('LANG_DIR', 'ltr'); 
define('ARTA_INSTALLATION', 'Arta Installation');
define('FORM_NEXT', 'Next');
define('FORM_VERIFY', 'Verify');
define('FORM_BACK', 'Back');

define('STEP_WELCOME', 'Welcome');
define('STEP_ESSENTIALS', 'Check Requirements');
define('STEP_LICENSE', 'License');
define('STEP_DB', 'Database Info');
define('STEP_INFO', 'Other Informations');
define('STEP_FINISH', 'Finishing Installation');

define('STEP_WELCOME_I', 'Let\'s install Arta!');
define('STEP_ESSENTIALS_I', 'What is needed before installation?');
define('STEP_LICENSE_I', 'Terms and Conditions');
define('STEP_DB_I', 'Connect Arta to Database');
define('STEP_INFO_I', 'Getting some other informations');
define('STEP_FINISH_I', 'Everything is going to be done!');

define('WELCOME_MSG', <<<HTML
<p>Welcome to Arta Installation Wizard. The wizard will help you to install a copy of Arta v%s on this Host.</p>
HTML
);
define('ARTA_IS_UNDER_GPL', <<<HTML
<p>Arta is released under <a href="license_gpl.txt" target="_blank">GNU GPL</a> license.</p>
HTML
);
define('THE_FOLLOWING_IS_MANDATORY', <<<HTML
<p>The following conditions MUST be available to install Arta. Arta needs these conditions to work correctly. If any of these items are not supported, system will not be able to run Arta. You should take appropriate actions to make following items ready to use.</p>
HTML
);

define('IS_PHP5', 'Is PHP version 5.0.0 or higher?');
define('IS_MYSQL_ENABLED', 'Is any of MySQL or MySQLi extensions available?');
define('IS_PCRE_ENABLED', 'Is PCRE extensions loaded?');
define('IS_SIMPLEXML_ENABLED', 'Is SimpleXML loaded?');
define('IS_XMLPARSER_ENABLED', 'Is XML Parser loaded?');
define('IS_GD_ENABLED', 'Are GD libraries loaded?');
define('IS_CURL_ENABLED', 'Is cURL library loaded?');
define('IS_ZLIB_ENABLED', 'Is Zlib library available?');

define('BOOL_YES', 'Yes');
define('BOOL_NO', 'No');

define('FILE_WRITABLE', 'Writeable');
define('FILE_UNWRITABLE', 'Not Writeable');

define('THE_FOLDERS_ARE_MANDATORY', '<p>The following directories MUST be writable to install and run Arta. The suitable permission mode is 755 for directories and 644 for files. You can use 777 if you encounter problems with 644 and 755. </p>');

define('YOU_ARE_DONE_IN_REQUIREMENTS', <<<HTML
<p>Congratulations! Your system meets the minimum requirements of Arta. You can just proceed to next step.</p>
HTML
);
define('YOU_ARE_FAILED_IN_REQUIREMENTS', <<<HTML
<p>We are sorry, but your system does not meet minimum system requirements. You should make necessary changes to your system. You can install Arta when the requirements are available at any time.</p>
HTML
);
define('I_AGREE_LICENSE', 'I agree terms and conditions of the license.');

define('YOUMUST_ACCEPT_LICENSE', 'You must accept terms and conditions of the license. If you don\'t accept the license, you cannot install Arta.');


define('SET_DB_DETAILS', <<<HTML
<p>Arta needs a MySQL database to use as data storage. It's mandatory to use MySQL Server version 4.1.2 or later, but MySQL 5 or later is recommended. Arta needs Host name of MySQL Server, User name and Password of Database and Database Name to establish a successful connection to Database Server. Please gather these informations from your hosting company website or by contacting your host provider.</p>
HTML
);
define('SET_DB_HOST', <<<HTML
<p>This is MySQL Server Address. The server address is consist of two sections: <b>Host name</b> and <b>Port number</b>. The structure of address is like <code>hostname:port</code>. Address example: <code>localhost:3306</code></p>
<p><b>Host</b>: It's usually "localhost" but it may be different. If you are not sure that it's "localhost", contact your host provider to get it.</p>
<p><b>Port</b>: The default value is <code>3306</code>. If you do not specify any port numbers, 3306 will be used. For example <code>localhost</code> is just like <code>localhost:3306</code>.</p>
<p>99% chance you won't need to change this value.</p>
HTML
);
define('DB_HOST', 'Database Host');

define('SET_DB_NAME', <<<HTML
<p>Set Database Name here. You should create a Database with a specific name if you are not created a Database yet. It's recommended to use a database without any tables inside. Do not forget to set Database Collation to <code>utf8_general_ci</code>.</p>
HTML
);
define('DB_NAME', 'Database Name');

define('SET_DB_CREDENTIALS', <<<HTML
<p>Enter credentials of a Database acoount with permissions of accessing Database and execute any <dfn title="SELECT,INSERT,UPDATE,DELETE">Data</dfn> and <dfn title="CREATE,ALTER,INDEX,DROP">Structure</dfn> statements.</p>
HTML
);
define('DB_USER', 'Database Username');
define('DB_PASS', 'Database Password');

define('SET_DB_PREFIX', <<<HTML
<p>This is the prefix of table names inside database. For example if you specify <code>arta_</code> then "Users" table will be <code>arta_users</code>.</p>
HTML
);
define('DB_PREFIX', 'Tables prefix');

define('SET_DB_TYPE', <<<HTML
<p>Select Database Connector Interface. It's recommended to use <strong>MySQLi</strong> if available.</p>
HTML
);
define('DB_TYPE', 'Database Connector');

define('INVALID_DB_PREFIX_CHARS', 'Tables prefix contains invalid characters. You can use letters, numbers and underlines; but first character MUST be a letter.');
define('INVALID_DB_INFO', 'Error: Could not connect to Database Server. Please try again. <br/>Server said: "%s"');
define('MYSQL_MUSTBE_GREATER_THAN_412', 'Error: MySQL Version must be greater than 4.1.2 or arta will not work.');
define('NO_ENOUGH_PRIVS', 'The provided user\'s privileges are insufficient.');
define('CANNOT_EXECUTE_TEST_QUERIES', 'Cannot execute data read/write testing queries on the database. Make sure that DB User can execute essential statements and DB Collation is set to utf8_unicode_ci.');
define('DB_CONNECTED_SUCC', 'Database connection successfully established.');
define('TEST_QUERIES_EXECD_SUCC', 'Data modification ability test completed successfully.');
define('DB_IS_NOW_AVAILABLE', 'Database connection is ready to use. Please proceed to next step.');
define('THE_LAST_STEP_IS_HERE', <<<HTML
<p>We just need some more informations to start installation process. Please fill the requested entries to start installation.</p>
HTML
);
define('SET_WEBSITE_TITLE', <<<HTML
<p><b>Site Name:</b> The name of your website. e.g. TechLife Online<br/>
<b>Homepage Title:</b> Title of your website's Homepage. It can be slogan of your company or something else which describes your website in few words.<br/>
<b>Site Description:</b> Describe your website here. This description will be shown on Search Results of search engines like Google.<br/>
<b>Site Keywords:</b> Add keywords corresponding to your website content to help search engines detecting your contents better. Separate words with comma (,).
</p>
HTML
);
define('L_SITENAME', 'Site Name');
define('L_HOMEPAGE_TITLE', 'Homepage Title');
define('L_DESCRIPTION', 'Site Description');
define('L_KEYWORDS', 'Site Keywords');

define('SET_USER_CREDENTIALS', <<<HTML
<p>We need a user to set as Administrator of website. Choose a username and password to create first user. Do not forget to use a valid E-Mail address or you may face some insoluble problems in near future. Password must be at least 6 characters. Please note that username should not contain any spaces.</p>
HTML
);
define('L_USERNAME', 'Username');
define('L_PASSWORD', 'Password');
define('L_PASSWORD_VERIFY', 'Verify Password');
define('L_EMAIL', 'E-Mail Address');

define('SET_CALENDAR', <<<HTML
<p><b>Time Offset:</b> Set this to your area's time offset. It will help arta to calculate Timedates correctly.<br/>
<b>Calendar Type:</b> Calendar System to use. You can select Gregorian (Anno Domini) or Jalali (Persian) Calendar types.
</p>
HTML
);
define('L_TIME_OFFSET', 'Time Offset');
define('L_CAL_TYPE', 'Calendar Type');

define('SET_SEF', <<<HTML
<p><p>Arta can make URLs of your website Search Engine Friendly, but it is depending to server support. It is highly recommended to use this nice feature. Using this feature will greatly improve your website's score in rating systems of search engines. </p>We will do a test to find out is your server supporting SEF or not.<br/>
<b>Test:</b> Look at the square placed just next to this paragraph. <br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
IF you see a white "OK" word inside a green background, then your server is compatible with Arta SEF URLs.<br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
Otherwise, You cannot use SEF URLs. <br/><br/>
Make your choice by passing the test and selecting <dfn title="I see &quot;OK&quot; word inside a green box and want to activate SEF.">Yes</dfn> or <dfn title="I do not see a green box with a &quot;OK&quot; word inside it OR do not want to activate SEF.">No</dfn>.
</p>
HTML
);
define('L_URL_FRIENDLY', 'Enable SEF URLs?');
define('YES', 'Yes');
define('NO', 'No');

define('NO_SITENAME_SPECIFIED', 'You must specify a name for your new website.');
define('NO_USERNAME_SPECIFIED', 'Invalid Username is specified. Remember not to use any spaces in username.');
define('INVALID_PASS_SPECIFIED', 'Invalid Password is specified. Password must be longer than 6 characters.');
define('INVALID_PASSV_SPECIFIED', 'Password is not matching with it\'s verification.');
define('READY_TO_INST', 'OK. We are ready to install Arta on your host. Proceed to Installation progress.');
define('CAL_GRE', 'Gregorian');
define('CAL_JAL', 'Jalali');
define('INVALID_EMAIL_SPECIFIED', 'Invalid E-Mail address is specified.');

define('CONFIG_WRITE_SUCC', 'Configuration file created successfully. Everything is done.');

define('FINISH_MSG', <<<HTML
<p>Hooray! You just finished installing Arta! You can start using Arta just right now!</p>
<p>For advanced users: You can review system settings by visiting <a href="%s" target="_blank">Configuration -> System Configuration</a> in administration panel.</p>
HTML
);

define('LICENSE_MSG', <<<HTML
<p>Do not forget that Arta is published under GNU GPL v3 license.</p>
<p>For more information about GPL license you can read <a href="http://en.wikipedia.org/wiki/GNU_General_Public_License">Wikipedia's Page</a> about it.</p>
HTML
);

define('REMOVAL_MSG', <<<HTML
<p>It's highly recommended to remove or rename installer directory for security reasons. Please do it before using your website for online cases.</p>
HTML
);

define('TO_ADMIN', 'Go to Administration Panel');
define('BYEBYE_MSG', 'Good luck!');

?>