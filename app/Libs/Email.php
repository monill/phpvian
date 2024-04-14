<?php

namespace PHPvian\Libs;

use PHPMailer\PHPMailer\PHPMailer;

class Email extends PHPMailer
{
    /**
     * Private area
     */
    // TODO: finish all this
    private function getMailer()
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = SMTP_HOST;                              // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = SMTP_USERNAME;                      // SMTP username
            $mail->Password = SMTP_PASSWORD;                      // SMTP password
            $mail->SMTPSecure = SMTP_ENCRYPTION;                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port = SMTP_PORT;                              // TCP port to connect to

            $mail->CharSet = "UTF-8";
            $mail->isHTML(true);                            // Tell mailer that we are sending HTML email

            $mail->From = FROM_MAIL;
            $mail->FromName = SNAME;
            $mail->addReplyTo(FROM_MAIL, SNAME);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }

        return $mail;
    }

    public function confirmEmail($email, $key)
    {
        $mail = $this->getMailer();                                         // get instance of PHPMailer (including some additional info)
        $mail->addAddress($email);                                          // where you want to send confirmation email

        $link = URL . "/signup/activeacc/" . $key;                          // link for email confirmation
        $body = file_get_contents(VIEWS . "emails/confirmacc.php");         // load email HTML template

        $body = str_replace("{{website_name}}", SNAME, $body);              // replace appropriate placeholders
        $body = str_replace("{{link}}", $link, $body);

        $mail->Subject = SNAME . " - Email confirmation.";                  // set subject and body
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
        $mail = $this->getMailer();
        $mail->addAddress($email);

        $link = URL . "/recover/code/" . $key;
        $body = file_get_contents(VIEWS . "emails/resetpass.php");

        $body = str_replace("{{ip}}", Helper::getIP(), $body);
        $body = str_replace("{{website_name}}", SNAME, $body);
        $body = str_replace("{{link}}", $link, $body);

        $mail->Subject = SNAME . " - Password Reset.";
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
        $body = file_get_contents(VIEWS . "emails/invite.php");

        $body = str_replace("{{website_name}}", SNAME, $body);
        $body = str_replace("{{invlink}}", $link, $body);

        $mail->Subject = SNAME . " - user invitation confirmation.";
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

        $body = file_get_contents(VIEWS . "emails/thanks.php");

        $body = str_replace("{{website_name}}", SNAME, $body);
        $body = str_replace("{{rulink}}", URL . '/rules', $body);
        $body = str_replace("{{falink}}", URL . '/faq', $body);

        $mail->Subject = SNAME . " - Thank you for sign-up.";
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

}