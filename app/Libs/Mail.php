<?php

namespace PHPvian\Libs;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends PHPMailer
{
    private $app, $appName;

    public function __construct()
    {
        parent::__construct();
        $this->app = $this->getMailer();
        $this->appName = config('mail', 'APP_NAME');
    }

    public function confirmEmail($email, $key)
    {
        $mail = $this->app;                                         // get instance of PHPMailer (including some additional info)
        $mail->addAddress($email);                                  // where you want to send confirmation email

        $link = http_host() . "/signup/activeacc/" . $key;                      // link for email confirmation
        $body = file_get_contents(VIEWS . "mails/signup.php");      // load email HTML template

        $body = str_replace("{{website_name}}", $this->appName, $body);    // replace appropriate placeholders
        $body = str_replace("{{link}}", $link, $body);

        $mail->Subject = $this->appName . " - Email confirmation.";              // set subject and body
        $mail->Body = $body;

        // try to send the email
        if (!$mail->send()) {
            echo "Message can not be sent. <br />";
            echo "Mail error: " . $mail->ErrorInfo;
            exit();
        } else {
            echo "We have registered your invite request successfully! You will be contacted soon.";
        }

        $mail->clearAllRecipients();
    }

    public function resetPass($email, $key)
    {
        $mail = $this->app;
        $mail->addAddress($email);

        $link = URL . "/recover/code/" . $key;
        $body = file_get_contents(VIEWS . "mails/resetpass.php");

        $body = str_replace("{{ip}}", get_ip(), $body);
        $body = str_replace("{{website_name}}", $this->appName, $body);
        $body = str_replace("{{link}}", $link, $body);

        $mail->Subject = $this->appName . " - Password Reset.";
        $mail->Body = $body;

        if (!$mail->send()) {
            echo "Message can not be sent. <br />";
            echo "Mail error: " . $mail->ErrorInfo;
            exit();
        } else {
            echo "We have registered your invite request successfully! You will be contacted soon.";
        }

        $mail->clearAllRecipients();
    }

    public function invite($email, $key)
    {
        $mail = $this->getMailer();
        $mail->addAddress($email);

        $link = URL . "/signup/invite/" . $key;
        $body = file_get_contents(VIEWS . "mails/invite.php");

        $body = str_replace("{{website_name}}", $this->appName, $body);
        $body = str_replace("{{invlink}}", $link, $body);

        $mail->Subject = $this->appName . " - user invitation confirmation.";
        $mail->Body = $body;

        if (!$mail->send()) {
            echo "Message can not be sent. <br />";
            echo "Mail error: " . $mail->ErrorInfo;
            exit();
        } else {
            echo "We have registered your invite request successfully! You will be contacted soon.";
        }

        $mail->clearAllRecipients();
    }

    public function thanks($email)
    {
        $mail = $this->getMailer();

        $mail->addAddress($email);

        $body = file_get_contents(VIEWS . "mails/thanks.php");

        $body = str_replace("{{website_name}}", $this->appName, $body);
        $body = str_replace("{{rulink}}", URL . '/rules', $body);
        $body = str_replace("{{falink}}", URL . '/faq', $body);

        $mail->Subject = $this->appName . " - Thank you for sign-up.";
        $mail->Body = $body;

        if (!$mail->send()) {
            echo "Message can not be sent. <br />";
            echo "Mail error: " . $mail->ErrorInfo;
            exit();
        } else {
            echo "We have registered your account.";
        }

        $mail->clearAllRecipients();
    }

    // TODO: finish all this
    private function getMailer()
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 2;                                               // Enable verbose debug output
            $mail->isSMTP();                                                    // Set mailer to use SMTP
            $mail->Host = config('mail', 'SMTP_HOST');                  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                                             // Enable SMTP authentication
            $mail->Username = config('mail', 'SMTP_USERNAME');          // SMTP username
            $mail->Password = config('mail', 'SMTP_PASSWORD');          // SMTP password
            $mail->SMTPSecure = config('mail', 'SMTP_ENCRYPTION');      // Enable TLS encryption, `ssl` also accepted
            $mail->Port = config('mail', 'SMTP_PORT');                  // TCP port to connect to

            $mail->CharSet = "UTF-8";
            $mail->isHTML(true);    // Tell mailer that we are sending HTML email

            $fromMail = config('mail', 'FROM_MAIL');
            $mail->From = $fromMail;
            $mail->FromName = $this->appName;
            $mail->addReplyTo($fromMail, $this->appName);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }

        return $mail;
    }

}