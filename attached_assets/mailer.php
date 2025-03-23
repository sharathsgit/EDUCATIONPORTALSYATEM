<?php
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendVerificationEmail($to, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "saratkumarbommineni@gmail.com";
        $mail->Password = "gqnu lxcq bxit flpy";
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("saratkumarbommineni@gmail.com", "Verification Code");
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = "Email Verification Code";
        $mail->Body = "Your verification code is: <strong>$code</strong>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
