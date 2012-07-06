<?php
define('LANG_DIR', 'rtl'); 
define('ARTA_INSTALLATION', 'نصب آرتا');
define('FORM_NEXT', 'بعدی');
define('FORM_VERIFY', 'بررسی');
define('FORM_BACK', 'بازگشت');

define('STEP_WELCOME', 'خوش آمدید');
define('STEP_ESSENTIALS', 'بررسی شرایط لازم');
define('STEP_LICENSE', 'مجوز');
define('STEP_DB', 'مشخصات بانک اطلاعاتی');
define('STEP_INFO', 'مشخصات دیگر');
define('STEP_FINISH', 'پایان نصب');

define('STEP_WELCOME_I', 'پیش به سوی نصب آرتا!');
define('STEP_ESSENTIALS_I', 'چه چیز هایی قبل از نصب لازم است؟');
define('STEP_LICENSE_I', 'شرایط و ضوابط استفاده');
define('STEP_DB_I', 'اتصال آرتا به بانک اطلاعاتی');
define('STEP_INFO_I', 'جمع آوری اطلاعات نهایی مورد نیاز');
define('STEP_FINISH_I', 'پایان بخشی و آماده سازی همه چیز!');

define('WELCOME_MSG', <<<HTML
<p>به فرآیند نصب آرتا خوش آمدید. شما به کمک این فرآیند کوتاه و آسان، می توانید یک کپی از سیستم آرتا نسخه %s را روی میزبان خود نصب نمایید.</p>
HTML
);
define('ARTA_IS_UNDER_GPL', <<<HTML
<p>آرتا تحت مجوز <a href="license_gpl.txt" target="_blank">GNU GPL</a> منتشر شده است.</p>
HTML
);
define('THE_FOLLOWING_IS_MANDATORY', <<<HTML
<p>قبل از نصب آرتا، شرایط مورد نیاز زیر باید فراهم باشند. آرتا برای کارکرد صحیح خود، نیازمند مساعد بودن شرایط زیر می باشد. اگر هر یک از ویژگی های زیر توسط سیستم شما پشتیبانی نشود، شما قادر نخواهید بود تا آرتا را بر روی سیستم خود نصب کنید. برای ادامه ی نصب، شما ملزم به مساعد سازی شرایط زیر می باشید.</p>
HTML
);

define('IS_PHP5', 'آیا نسخه ی PHP برابر با 5.0.0 یا بالاتر از آن است؟');
define('IS_MYSQL_ENABLED', 'آیا هیچیک از مکمل های MySQL یا MySQLi موجود هستند؟');
define('IS_PCRE_ENABLED', 'آیا مکمل PCRE بارگذاری شده است؟');
define('IS_SIMPLEXML_ENABLED', 'آیا SimpleXML بارگذاری شده است؟');
define('IS_XMLPARSER_ENABLED', 'آیا XML بارگذاری شده است؟');
define('IS_GD_ENABLED', 'آیا کتابخانه ی GD  بارگذاری شده است؟');
define('IS_CURL_ENABLED', 'آیا کتابخانه ی  cURL  بارگذاری شده است؟');
define('IS_ZLIB_ENABLED', 'آیا کتباخانه ی Zlib موجود است؟');

define('BOOL_YES', 'بلی');
define('BOOL_NO', 'خیر');

define('FILE_WRITABLE', 'تغییر پذیر');
define('FILE_UNWRITABLE', 'غیر قابل تغییر');

define('THE_FOLDERS_ARE_MANDATORY', '<p>دایرکتوری های ذیل باید تغییر پذیر باشند تا سیستم بتواند آرتا را اجرا کند. سطح دسترسی مناسب برای دایرکتوری ها 755 و برای فایل ها 644 می باشد. اگر با مشکلی مواجه شدید، میتوانید از سطح دسترسی 777 استفاده کنید.</p>');

define('YOU_ARE_DONE_IN_REQUIREMENTS', <<<HTML
<p>تبریک! سیستم شما قابلیت اجرای آرتا را دارد. می توانید به قدم بعدی بروید.</p>
HTML
);
define('YOU_ARE_FAILED_IN_REQUIREMENTS', <<<HTML
<p>با عرض تاسف، سیستم شما قابلیت اجرای آرتا را ندارد. شما مجبورید مشکلات موجود را برطرف کنید تا بتوانید آرتا را نصب کنید. پس از رفع مشکلات، در هر زمانی می توانید آرتا را دوباره نصب کنید.</p>
HTML
);
define('I_AGREE_LICENSE', 'شرایط و ضوابط این مجوز را قبول دارم.');

define('YOUMUST_ACCEPT_LICENSE', 'شما باید شرایط و ضوابط مجوز را بپذیرید؛ و گر نه نمی توانید از آرتا استفاده کنید.');


define('SET_DB_DETAILS', <<<HTML
<p>آرتا به یک بانک اطلاعاتی MySQL بعنوان ذخیره گاه اطلاعات احتیاج دارد. استفاده از سرور MySQL نسخه 4.1.2 یا بالاتر ضروری است، اما MySQL 5 یا جدید تر توصیه می شود. برای اتصال موفقیت آمیز به سرور، آرتا نیازمند نام میزبان (Host Name) سرور، نام کاربری و رمز عبور بانک اطلاعاتی و نام آن است. شما می توانید این اطلاعات را با مراجعه به وبسایت شرکت ارائه دهنده ی میزبان و یا پرسش از اپراتورهای آن فراهم کنید.</p>
HTML
);
define('SET_DB_HOST', <<<HTML
<p>این همان آدرس سرور MySQL است. آدرس سرور از دو بخش تشکیل شده است: <b>نام میزبان (Host Name)</b> و <b>شماره درگاه (Port Number)</b>. ساختار آدرس بصورت روبرو است <code>hostname:port</code>. مثال: <code>localhost:3306</code></p>
<p><b>میزبان (Host)</b>: این معمولاً "localhost" است، اما ممکن است عبارت دیگری باشد. اگر مطمئن نیستید که این همان "localhost" است، با ارائه دهنده ی میزبانی خود تماس بگیرید.</p>
<p><b>درگاه (Port)</b>: مقدار پیش فرض برابر <code>3306</code> است. اگر مقداری را تعیین نکنید، 3306 استفاده خواهد شد. برای مثال <code>localhost</code> هم ارز <code>localhost:3306</code> است.</p>
<p>با احتمال 99% شما نیازی به تغییر این مقدار نخواهید داشت.</p>
HTML
);
define('DB_HOST', 'میزبان بانک اطلاعاتی (DB Host)');

define('SET_DB_NAME', <<<HTML
<p>نام بانک اطلاعاتی را در اینجا وارد کنید. اگر هنوز بانک اطلاعاتی نساخته اید، با نامی مشخص، یک بانک اطلاعاتی ایجاد کنید. توصیه می شود که بانک اطلاعاتی مورد استفاده خالی از هر گونه جدول باشد. فراموش نکنید که Database Collation را برابر <code>utf8_general_ci</code> قرار دهید.</p>
HTML
);
define('DB_NAME', 'نام بانک اطلاعاتی (DB Name)');

define('SET_DB_CREDENTIALS', <<<HTML
<p>برای اتصال به سرور، به یک حساب کاربری نیاز دارید که علاوه بر دسترسی به بانک اطلاعاتی ساخته شده، توانایی اجرای دستورات <dfn title="SELECT,INSERT,UPDATE,DELETE">مدیریت اطلاعات</dfn> و <dfn title="CREATE,ALTER,INDEX,DROP">ساختار</dfn> را داشته باشد.</p>
HTML
);
define('DB_USER', 'نام کاربری');
define('DB_PASS', 'رمز عبور');

define('SET_DB_PREFIX', <<<HTML
<p>این همان پیشوند نام جداول داخل بانک اطلاعاتی است که توصیه می شود آن را تغییری ندهید. برای مثال اگر <code>arta_</code> را انتخاب کنید، جدول "Users" با نام  <code>arta_users</code> ساخته خواهد شد.</p>
HTML
);
define('DB_PREFIX', 'پیشوند جداول');

define('SET_DB_TYPE', <<<HTML
<p>رابط اتصال به سرور را انتخاب کنید. پیشنهاد می شود که در صورت امکان، از <b>MySQLi</b> استفاده کنید.</p>
HTML
);
define('DB_TYPE', 'رابط اتصال');

define('INVALID_DB_PREFIX_CHARS', 'پیشوند جداول شامل کاراکتر غیر مجاز می باشد. شما می توانید از حروف انگلیسی، اعداد و خط زیر استفاده کنید؛ اما حرف اول نباید عدد باشد.');
define('INVALID_DB_INFO', 'خطا: اتصال به بانک اطلاعاتی موفقیت آمیز نبود. <br/>پیام سرور: "%s"');
define('MYSQL_MUSTBE_GREATER_THAN_412', 'خطا: سرور MySQL باید جدیدتر از نسخه ی  4.1.2 باشد وگرنه آرتا نمی تواند عمل کند.');
define('NO_ENOUGH_PRIVS', 'سطح دسترسی کاربر یاد شده کافی نیست.');
define('CANNOT_EXECUTE_TEST_QUERIES', 'دستورات مدیریت اطلاعات با موفقیت اجرا نشدند.');
define('DB_CONNECTED_SUCC', 'ارتباط با موفقیت برقرار شد.');
define('TEST_QUERIES_EXECD_SUCC', 'تست توانایی تغییر داده ها، با موفقیت انجام شد.');
define('DB_IS_NOW_AVAILABLE', 'ارتباط با سرور آماده ی استفاده است. لطفاً به قدم بعدی پیش بروید.');
define('THE_LAST_STEP_IS_HERE', <<<HTML
<p>برای شروع عملیات نصب، هنوز نیازمند مقدار دیگری از اطلاعات شما هستیم. لطفاً با ارائه ی اطلاعات درست آماده ی نصب شوید.</p>
HTML
);
define('SET_WEBSITE_TITLE', <<<HTML
<p><b>نام وبسایت:</b> نامی که برای وبسایت خود انتخاب کرده اید. مانند: تک آنلاین، خبرگزاری روز<br/>
<b>عنوان صفحه ی خانگی:</b> عنوان اولین صفحه ی وبسایت شما. می تواند شعار شرکت شما یا چیز دیگری باشد که بطور خلاصه محتوای شما را توضیح دهد.<br/>
<b>شرح وبسایت:</b> این توصیف، توسط موتور های جستجو برای معارفه ی وبسایت شما استفاده خواهد شد. مانند متونی که در نتیجه ی جستجوی گوگل مشاهده می کنید.<br/>
<b>کلمه های کلیدی وبسایت:</b> آنها را با کاما جدا کنید: ", ". این کلمات کلیدی به موتور های جستجو کمک می کنند تا وبسایت شما را بهتر فهرست بندی کنند. مثال: "خبر, روزنامه, خبرگزاری, اخبار روز"
</p>
HTML
);
define('L_SITENAME', 'نام وبسایت');
define('L_HOMEPAGE_TITLE', 'عنوان صفحه ی خانگی');
define('L_DESCRIPTION', 'شرح وبسایت');
define('L_KEYWORDS', 'کلمه های کلیدی وبسایت');

define('SET_USER_CREDENTIALS', <<<HTML
<p>ما نیاز به یک کاربر داریم تا بعنوان مدیریت وبسایت تعیین شود. یک نام کاربری و رمز عبور برای اولین کاربر سیستم انتخاب کنید. فراموش نکنید که باید از یک آدرس پست الکترونیکی مجاز استفاده کنید و گرنه ممکن است در آینده ی نزدیک با مشکلات غیر قابل حلی مواجه شوید. رمز عبور باید حداقل 6 حرف باشد. توجه کنید که نام کاربری باید فاقد فاصله باشد.</p>
HTML
);
define('L_USERNAME', 'نام کاربری');
define('L_PASSWORD', 'رمز عبور');
define('L_PASSWORD_VERIFY', 'تکرار رمز عبور');
define('L_EMAIL', 'آدرس پست الکترونیکی');

define('SET_CALENDAR', <<<HTML
<p><b>مبدا زمانی:</b> مبدا زمانی خود را انتخاب کنید. این به آرتا کمک می کند تا زمان ها را به درستی محاسبه کند.<br/>
<b>نوع تقویم:</b> سیستم تقویمی را که متمایل به استفاده از آن هستید، انتخاب کنید. شما میتوانید از تقویم های میلادی (Anno Domini) و هجری شمسی (جلالی) استفاده کنید. <b>توجه داشته باشید که تقویم فارسی جلالی از ویژگی های منحصر به فرد آرتا در میان نمونه های مشابه می باشد.</b>
</p>
HTML
);
define('L_TIME_OFFSET', 'مبدا زمانی');
define('L_CAL_TYPE', 'نوع تقویم');

define('SET_SEF', <<<HTML
<p><p>آرتا می تواند با بهینه سازی آدرس ها، URL های دوستانه با موتور های جستجو بسازد، اما این سیستم منوط به پشتیبانی سرور است. به شدت توصیه می شود که از این ویژگی عالی استفاده کنید. استفاده از این امکان می تواند به طور چشم گیری امتیاز وبسایت شما را در موتورهای جستجو افزایش دهد. </p>حال با انجام تستی پشتیبانی سرور را از این سیستم بررسی می کنیم.<br/>
<b>تست:</b> به مربعی که در کنار این پاراگراف قرار گرفته است، توجه کنید. <br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
اگر عبارت سفید رنگ "OK" را در پس زمینه ی سبز رنگ مشاهده می کنید، بدان معناست که سرور شما از بهینه سازی URL ها پشتیبانی می کند.<br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
وگرنه، شما نمی توانید از بهینه سازی استفاده کنید. <br/><br/>
بعد از گذراندن تست، با انتخاب <dfn title="من عبارت &quot;OK&quot; درون جعبه ای سبز رنگ می بینم و می خواهم بهینه سازی فعال شود.">بلی</dfn> یا <dfn title="من جعبه ای سبز رنگ با عبارت سفید رنگ &quot;OK&quot; در آن نمی بینم یا نمی خواهم بهینه سازی را فعال کنم.">خیر</dfn> تصمیم گیری کنید.
</p>
HTML
);
define('L_URL_FRIENDLY', 'بهینه سازی URL ها فعال شود؟');
define('YES', 'بلی');
define('NO', 'خیر');

define('NO_SITENAME_SPECIFIED', 'شما باید نامی برای وبسایت خود انتخاب کنید.');
define('NO_USERNAME_SPECIFIED', 'نام کاربری نا معتبر است. بیاد داشته باشید که نباید از فاصله استفاده کنید.');
define('INVALID_PASS_SPECIFIED', 'رمز ورود نامعتبر است. رمز ورود باید بیش از 6 حرف باشد.');
define('INVALID_PASSV_SPECIFIED', 'تکرار رمز عبور با آن برابر نیست.');
define('READY_TO_INST', 'بسیار خوب. ما آماده ی نصب آرتا بر روی میزبان شما هستیم. به عملیات نصب پیش روید.');
define('CAL_GRE', 'میلادی');
define('CAL_JAL', 'هجری شمسی');
define('INVALID_EMAIL_SPECIFIED', 'پست الکترونیکی نامعتبر وارد شده است.');

define('CONFIG_WRITE_SUCC', 'فایل پیکربندی با موفقیت ایجاد شد. همه چیز تمام شد!');

define('FINISH_MSG', <<<HTML
<p>عالی بود! شما آرتا را با موفقیت نصب کردید! شما از هم اکنون می توانید از آرتا استفاده کنید!</p>
<p>برای کاربران پیشرفته: شما می توانید تنظیمات سیستم را با مراجعه به <a href="%s" target="_blank">Configuration -> System Configuration</a> در پنل مدیریت، بازبینی کنید.</p>
HTML
);

define('LICENSE_MSG', <<<HTML
<p>فراموش نکنید که آرتا تحت مجوز GNU GPL v3 منتشر شده است.</p>
<p>برای اطلاع بیشتر از مجوز GPL می توانید <a href="http://fa.wikipedia.org/wiki/%D8%A7%D8%AC%D8%A7%D8%B2%D9%87%E2%80%8C%D9%86%D8%A7%D9%85%D9%87_%D8%B9%D9%85%D9%88%D9%85%DB%8C_%D9%87%D9%85%DA%AF%D8%A7%D9%86%DB%8C_%DA%AF%D9%86%D9%88">صفحه ی ویکی پدیا</a> مربوط به آن را ببینید.</p>
HTML
);

define('REMOVAL_MSG', <<<HTML
<p>به شدت توصیه می شود که به علل امنیتی دایرکتوری install را تغییر نام داده یا حذف کنید. قبل از استفاده ی آرتا بصورت آنلاین، آن را حذف کنید.</p>
HTML
);

define('TO_ADMIN', 'رفتن به پنل مدیریت');
define('BYEBYE_MSG', 'موفق باشید!');

?>