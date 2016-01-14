<?php

/*
 * @author: Darwin Themes
 */
switch ($_POST['ajaxForm']) {
    case "ajax_send_contact_email":
        echo json_encode(send_contact_email());
        break;
}

function send_contact_email() {
    if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $_REQUEST['email'])) {
        $return["details"] = "Invalid email format.";
        $return["status"] = false;
        return $return;
    }
    $to = 'support@darwinthemes.com'; // emails where the email goes
    $subject = 'Contact form submission';
    $eol = PHP_EOL;

    $separator = md5(time());
    $message = "Name: " . $_REQUEST['name'] . "\r\n";
    if ($_REQUEST['country'] != "") {
        $message .= "Country: " . $_REQUEST['country'] . "\r\n";
    }
    if ($_REQUEST['phone'] != "") {
        $message .= "Phone: " . $_REQUEST['phone'] . "\r\n";
    }
    $message.= "\r\nMessage:\r\n\r\n";
    $message.= $_REQUEST['message'] . "\r\n";

    require dirname(__FILE__) . '/PHPMailer-master/PHPMailerAutoload.php';

    $mail = new PHPMailer();
    $mail->setFrom($_REQUEST['email'], $_REQUEST['name']);
    $mail->addReplyTo($_REQUEST['email'], $_REQUEST['name']);
    $mail->addAddress($to, '');
    $mail->Subject = $subject;

    $mail->Body = $message;
    $mail->AllowEmpty = true;
    $response['mail'] = $mail;
    if (!$mail->send()) {
        $return["details"] = $mail->ErrorInfo;
        $return["status"] = false;
    } else {
        $return["status"] = true;
    }
    return $return;
}
