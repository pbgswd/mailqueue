<?php
/***********************************************************************************
    script run by cron to send out mail from db table 

    Copyright (C) 2007  Peter Gordon superwebdeveloper@gmail.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Alright, this is a simple script using specific PEAR libraries to send out mail.
You are welcome to use other tools to make this thing work. 

It is meant to control 3 different types of mail that can get pushed out through a web server 
to send mail. A website can have times with very high fluctuations of email volumes, and 
this script will mange that. 

If sending email is your problem, this may help you and enable you to get stuff done without
having to scale up.

You will need PEAR MDB2, Mail, Mail_Mime


***********************************************************************************/
require_once('site.php');
require_once("Mail-1.1.14/Mail.php");
require_once("Mail_Mime-1.5.2/mime.php");
$mail = Mail::factory('mail');
// regular, subscriber, bulk

$priority = htmlentities($argv[1]);
switch($priority)
{
    case 'bulk':
        $limit_string = 5;
        break;
     case 'subscription':
        $limit_string = 10;
        break;
     case 'regular':
        $limit_string = 10;
        break;
     default:
        $priority = 'regular';
        $limit_string = 20;
       break;
}

$test = false;
if ($test == true)
{
	$priority = 'subscription'; // test hack
	$limit_string = 1;
	$sql = "SELECT *
			FROM mail_queue
			WHERE recipient = '$siteEmail'
			AND sent_time IS NULL
			AND priority = '$priority'    
			LIMIT $limit_string";
}
else
{ 
	$sql = "SELECT *
		    FROM mail_queue
		    WHERE sent_time
		    IS NULL 
			AND priority = '$priority'    
		    LIMIT $limit_string";
}
diode($result = $mlog->queryAll($sql),$sql);

set_time_limit(0);

foreach($result as $msg)
{
    $try_sent = (int)$msg['try_sent'] + 1;

    $sql = "UPDATE mail_queue
            SET time_to_send=now(), try_sent='$try_sent'
            WHERE id ='{$msg['id']}'";
    $mlog->query($sql);

    // messages to send out
    $mime = new Mail_mime("\r\n");

    if ($msg['file_name'] !='')
    {
        $mime->addAttachment($paths["newsletters"] . "/" . $msg['file_name'], $msg['file_type']);
    }

    $mime->setTXTBody($msg['text_body']);
    $mime->setHTMLBody(stripslashes($msg['html_body']));
    $body = $mime->get();
    $headers = unserialize($msg['headers']);
    $hdrs = $mime->headers($headers);
    //   $msg['recipient'] = '$siteEmail'; // for testnig
    $recipient = trim($msg['recipient']);

    // check to see if test is set or not.
    $errorMsg = 'error_message = null';
    $sentTime = 'sent_time = now()';
      
    $isTest = $test == true ? 'is_test = 1' : 'is_test = 0';

    $report = $mail->send($recipient, $hdrs, $body); // send line 

    if (PEAR::isError($report))
      {
	$errorMsg = 'error_message = "' . mysql_real_escape_string($report->getMessage()) . '"';
	$sentTime = 'sent_time = null';
      }
    
    $sql = "UPDATE mail_queue SET
	  {$sentTime}, {$isTest}, {$errorMsg}
	  WHERE id ='{$msg['id']}'";

    $mlog->query($sql);

    usleep(5000); // be gentle on sendmail, sleep for half a half a half a second
}
set_time_limit(30);
// when its all done
//$sql = "DELETE FROM mail_queue
//          WHERE sent_time
//	  IS NOT NULL";
//$mlog->query($sql);