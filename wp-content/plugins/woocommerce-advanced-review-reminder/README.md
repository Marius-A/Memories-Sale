
WooCommerce Advanced Review Reminder
====================================

* http://kodemann.com

## Changelog

### 1.5.3
* Fix for using mb_encode_mimeheader() on PHP installations without mb_ extension installed. 
(http://php.net/manual/en/function.mb-encode-mimeheader.php - Install instructions here: http://php.net/manual/en/mbstring.installation.php)

### 1.5.2
* Minor PHP Notice fix for using esc_sql() instead of mysql_real_escape_string() 

### 1.5.1
* Fix: When setting 'Day(s) after order' to empty '', no emails will be sent, only by clicking the manual button on the order page.

### 1.5 
* Unsubscribe confirmation - Users now get a confirmation email they have unsubscribed from further emails.

### 1.4.3
* Minor fix for PHP undefined index, 'send_reminder_now'

### 1.4.2
* Fix for UTF8 encoding problem in database.

### 1.4.1
* Fix: Bug in the scheduling and immediate order sending fixed.

### 1.4
* New: See email sending log notes directly on order page in admin
* New: Send review request immediately via button on order page in admin  

### 1.3
* Added {customer_firstname} and {customer_lastname} macros by customer request.

### 1.2
* New: Introducing logging, so you can see what is going on.

### 1.1.1 
 * Fix: Save THEN send test email. No need to first save and then afterwards send test email.
 * Fix: Changed link in documentation to new CodeCanyon link: http://codecanyon.net/user/kodemann


### 1.1
 * You can now send a test email out from the settings page.

###1.0.1 
 * Removed buggy update script.

### 1.0
 * First release.
