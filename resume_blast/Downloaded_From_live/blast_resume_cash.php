<?php
ob_start();
ini_set("max_execution_time","50000");
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(0);

include("dbfunctions_jt.php");
require_once("Emailer.class.php");
$link=dblink();

global $Emailer;
$Emailer = new Emailer;

//echo "hai";

$order_id = $_POST[order_id];
$user_id = $_POST[user_id];
$email_id = $_POST[email_id];
$resume_blast_id = $_POST[resume_blast_id];
$admin_email="noreply@jobtardis.in";

/*echo $order_id."<br>";
echo $user_id."<br>";
echo $email_id."<br>";
echo $resume_blast_id;*/

//exit(0);


function copyemz($file1,$file2){ 
	$contentx =@file_get_contents($file1); 
	$openedfile = fopen($file2, "w"); 
	fwrite($openedfile, $contentx);
	fclose($openedfile); 
	if ($contentx === FALSE) { 
		$status=false; 
	} else $status=true; 	
	 return $status; 
}

function smtp_mail_send($from, $to, $subject, $msg, $cc="", $bcc=""){
	//headers
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'from: ' . $from . "\r\n";
	if(!empty($cc))
	{
	$headers .= "CC: $cc" . "\r\n";
	}
	if(!empty($bcc))
	{
	$headers .= "Bcc: $bcc" . "\r\n";
	}
	$headers .='X-Mailer: PHP/' . phpversion();

	//message
	$messageheader =$msg;
	$message = wordwrap(($messageheader), 70);
	//send mail
	$response = mail($to, $subject, $message, $headers);
	return $response;
 }

//$resume_blast_id = '520';

echo $query_word = "SELECT blast_resume, blast_email,blast_fname FROM jt_resume_blast WHERE blast_id='".$resume_blast_id."'";
$result_word = mysql_query($query_word,$link);
$client_word = mysql_fetch_array($result_word);
$user_email=$client_word['blast_email'];
$blast_fname=$client_word['blast_fname'];
$path = $client_word['blast_resume'];

$base_path = basename($client_word['blast_resume']);

//Load the Emailer class into a variable
$Emailer->set_from("JOBTARDIS");
//$Emailer->set_useremail(trim($email_id));
$Emailer->set_sender("<$admin_email>");
$Emailer->set_subject("Resume from JOBTARDIS");
//$url	=	'http://www.jobtardis.in/';
//$url_path	=	str_replace('../','',$path);
//echo $url.$url_path;
//echo $path;
//exit(0);

$path = "http://www.jobtardis.in/blastres/".$base_path;

//echo $path	=	basename($path);

//$new_path	= "blastres/68714e770d6d98e65.doc";

$new_path	= "blastres/".$base_path;

copyemz($path,$new_path);

echo $Emailer->add_attachments($new_path);

$query_name = "select email, company_name, contact_person  from jt_login,jt_empdetails where (user_type=2 or user_type=3) and jt_login.user_id=jt_empdetails.user_id ";
$result_name = mysql_query($query_name,$link);
$emailCount = 1;

while($client_email = mysql_fetch_array($result_name))
//$testEmailArray = array('nkvuppala@yahoo.com', 'kv@anantha.co.uk', 'neeraj@anantha.co.in', 'jeeva@anantha.co.in', 'sirisha@anantha.co.ins');
//foreach($testEmailArray as $index => $client_email)
{					
	$client_email = $client_email['email'];
	//$client_email = "senthil090680@gmail.com";
	//$client_email = "veni.anburaj@gmail.co.uk,yrajeash@gmail.co.uk";
	$client_company = $client_email['company_name'];
	$client_contact_person= $client_email['contact_person'];

	//Setup where and where from the message is being sent.
	$Emailer->set_to($client_email);

	//Add some message percifics
	$msg = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
			<title>Cover Sheet</title>
			<style type="text/css">
			<!--
			body {
				margin-left: 0px;
				margin-top: 0px;
				margin-right: 0px;
				margin-bottom: 0px;
				background-color: #7d7d7d;
			}
			.content {
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 12px;
				line-height: 18px;
				color: #363a3a;
				text-decoration: none;
			}
			.content_b {
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 12px;
				line-height: 18px;
				color: #363a3a;
				text-decoration: none;
				font-weight: bold;
			}
			.whiteContent {
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 12px;
				line-height: 18px;
				color: #FFFFFF;
				text-decoration: none;
			}
			-->
			</style>
		</head>
		<body>
			<table border="0" align="center" cellpadding="0" cellspacing="0">
				<tr>
					<td  align="top" bgcolor="#FFFFFF" class="content" style="padding:10px">
					Dear Employer / Recruiter,<br/><br/>
					I am submitting my resume through Jobtardis. Kindly refer me if you have any requirements relevant to my profile.
					</td>
				</tr>
				<tr>
					<td class="content" style="padding:3px;">
						Thanks and Regards,<br/>'.
						$blast_fname.'
					</td>
				</tr>
			</table>
		</body>
	</html>';
	$Emailer->set_html($msg);
	$result = $Emailer->send();

	if($result)
	{
		$stat = "1";
	}
	else
		$stat = "0";
	$emailCount++;
	//break;
//exit(0);
}

##echo "Total Email Sent : ".$emailCount;
//To update the status of blasted resume
$query_update_blast = "UPDATE jt_resume_blast SET blast_status=1 and tot_receiver='$emailCount' WHERE blast_id='".$resume_blast_id."'";
//echo $query_update_blast; die;
$res_query_blast=mysql_query($query_update_blast,$link);
/*if(mysql_query($query_update_blast,$link)){
	##echo 'Record Updated in jt_resume_blast Table. <br/>';
}else {
	##echo 'Error on Updating Record in jt_resume_blast Table. <br />';
		}*/

// Sent Mail to blast-user
$subject="Resume Blasting";
$msg_user = "<b>Dear $blast_fname,</b><br/><br/>\n".
"Your transaction is successful and your profile has been sent to all employers and recruiters.<br/><br/>\n" .
$msg_user = $msg_user."Heartly Invites,<br/>\n".
"Admin<br/>\n".
"<a href='www.jobtardis.in'>jobtardis.in</a><br/>\n".
"<img src='http://www.jobtardis.in/images/logo.gif' alt='Jobtardis' /><br/>\n";

//$rval = smtp_mail_send($admin_email, trim($user_email), $subject, $msg_user,$cc,$bcc);
		$query_purchase="SELECT * FROM jt_login where user_id='$user_id'";
		$result_purchase=mysql_query($query_purchase,$link);
		$row_purchase=mysql_fetch_array($result_purchase);

		$from_ad="admin@jobtardis.in";
		$to_ad="admin@jobtardis.in";

		$from_ad="senthil090680@gmail.com";
		$to_ad="senthil090680@gmail.com";

		$subject_ad="Resume Blasting payment confirmation";
		$msg_txt_ad="<b>Dear admin,</b><br><br>\n $blast_fname has registered for  <b> Resume Blast</b>.<br><br>\n".

			"Regards,<br/>\n".
				"Admin<br/>\n".
				"<a href='www.jobtardis.in'>jobtardis.in</a>,<br/>\n".
					"<img src='http://www.jobtardis.in/images/logo.gif'/><br/>\n";

		$activate_mail_ad = smtp_mail_send($from_ad, $to_ad, $subject_ad, $msg_txt_ad,$cc,$bcc);

		$query_send = "INSERT INTO jt_intranet_email (user_id, msg_to, msg_subject, msg_body, sender_status, receiver_status, read_status) VALUES ('1', '".$user_id."', 'Confirmation Mail - Personal Ad payment confirmation', '".addslashes($msg_user)."', 'send', 'receive', 'unread')";
		$res_send = mysql_query($query_send, $link);
		
		//exit(0);
		
		?>
		<form action="<?php echo ROOTPATH_BLAST_CASH; ?>" method="post" name="returnBlastForm">
		<input type="hidden" name="status_blast" value="<?php echo $status_blast; ?>" />
		<input type="hidden" name="ccavenue_post_order_id" value="<?php echo $order_id; ?>" />
		</form>
		<?php
			//exit(0);
		?>
		<script type="text/javascript">
		document.forms['returnBlastForm'].submit();
		</script>