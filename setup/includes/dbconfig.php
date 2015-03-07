<?php
require_once('../../includes/dbWrapper.php');
error_reporting(0);

if ($_POST['action'] == 'checkdb') : 
//************************************  check to make sure we can connect to db and that it is empty
$db = new dbWrapper($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpassword'], $_POST['dbname'], true);
if (!$db or $db == '' or empty($db)) {
echo "Couldn't connect to the database. Please check your credentials and try again.";
die();
}
echo 'Success!';
die();
endif;

if ($_POST['action'] == 'finishsetup') :
//************************************  create tables
$db = new dbWrapper($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpassword'], $_POST['dbname'], true);
$result = $db->q("CREATE TABLE ".$_POST['dbprefix']."logon (
  userId int(11) NOT NULL auto_increment PRIMARY KEY,
  userEmail varchar(50) NOT NULL default '',
  password varchar(50) NOT NULL default '',
  userLevel int(1) NOT NULL default '0'
)");
$result = $db->q("CREATE TABLE ".$_POST['dbprefix']."allowedEmails (
  emailId int(11) NOT NULL auto_increment PRIMARY KEY,
  allowedEmail varchar(50) NOT NULL default '',
  timeZone varchar(90) NOT NULL default '',
  emailFormat varchar(30) NOT NULL default ''
)");
$result = $db->q("CREATE TABLE ".$_POST['dbprefix']."bumpQueue (
  bumpId int(11) NOT NULL auto_increment PRIMARY KEY,
  fromEmail varchar(50) NOT NULL default '', 
  subject varchar(50),
  body text,
  timeToSend int(11)  
)");
$result = $db->q("CREATE INDEX ".$_POST['dbprefix']."timeToSend USING BTREE ON ".$_POST['dbprefix']."bumpQueue (timeToSend)");
//************************************  create config file secure it, and make sure that inbox.php is accessible
$fp = fopen('../../includes/bumper.config', 'w');
fwrite($fp, '<?php
define("DBHOST", "'.$_POST['dbhost'].'");
define("DBNAME", "'.$_POST['dbname'].'");
define("DBUSER", "'.$_POST['dbuser'].'");
define("DBPASS", "'.$_POST['dbpassword'].'");
define("DBPREFIX", "'.$_POST['dbprefix'].'");
define("EMAIL_DOMAIN", "'.$_SERVER['HTTP_HOST'].'");
');
fclose($fp);
chmod("../../includes/bumper.config", 0600);
chmod("../../inbox.php", 0755);
//************************************  create admin account and add admin email to allowed emails
$result = $db->q("INSERT INTO ".$_POST['dbprefix']."logon (userEmail, password) VALUES ( ?, MD5(?) )", 'ss', $_POST['adminemail'], $_POST['adminpassword']);
$result = $db->q("INSERT INTO ".$_POST['dbprefix']."allowedEmails (allowedEmail, timeZone, emailFormat) VALUES ( ?, ?, ? )", 'sss', $_POST['adminemail'], 'America/Anguilla', 'html');
echo 'Success!';
die();
endif;