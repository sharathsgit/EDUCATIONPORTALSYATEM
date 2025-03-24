<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendVerificationEmail($to, $code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = getenv("MAIL_USERNAME") ?: "saratkumarbommineni@gmail.com";
        $mail->Password = getenv("MAIL_PASSWORD") ?: "gqnu lxcq bxit flpy ";
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($mail->Username, "Educational Management System");
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Email Verification Code";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #3498db; text-align: center;'>Email Verification</h2>
                <p>Thank you for registering with our Educational Management System. Please use the following code to verify your email address:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 24px; font-weight: bold; background-color: #f8f9fa; padding: 10px 20px; border-radius: 5px;'>{$code}</span>
                </div>
                <p>If you did not request this code, please ignore this email.</p>
                <p>Regards,<br>Educational Management System Team</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error instead of displaying it
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendPasswordResetEmail($to, $reset_token) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = getenv("MAIL_USERNAME") ?: "your_email@gmail.com";
        $mail->Password = getenv("MAIL_PASSWORD") ?: "your_app_password";
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/pages/reset_password.php?token=" . $reset_token;

        // Recipients
        $mail->setFrom($mail->Username, "Educational Management System");
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #3498db; text-align: center;'>Password Reset Request</h2>
                <p>We received a request to reset your password. Please click the button below to set a new password:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$reset_link}' style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                </div>
                <p>If you did not request a password reset, please ignore this email.</p>
                <p>Regards,<br>Educational Management System Team</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error instead of displaying it
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
