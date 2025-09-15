<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function mailer($to, $subject, $body, $from = 'no-reply@idjwi-tourisme.com', $fromName = 'Idjwi Tourisme') {
    $mail = new PHPMailer(true);

    try {
        // Paramètres du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gentillenoir075@gmail.com';
        $mail->Password   = "igtv elqr rypn yzby";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Destinataires
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>