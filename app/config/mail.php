<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    public static function sendMail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Cấu hình Server
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'khoanv249@gmail.com'; // Thay bằng email của bạn
            $mail->Password   = 'ahri dkri ayna skra';    // Thay bằng mật khẩu ứng dụng (App Password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
            $mail->Port       = 587;                                    

            // Cấu hình ngôn ngữ và mã hóa
            $mail->CharSet = 'UTF-8';

            // Người gửi và người nhận
            $mail->setFrom('khoanv249@gmail.com', 'ECO-PROTECT STORE');
            $mail->addAddress($to);

            // Nội dung
            $mail->isHTML(true);                                  
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>
