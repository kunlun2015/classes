<?php
/**
 * 发送邮件类
 * @authors Amos
 * @date    2016-01-05 11:09:29
 * @copyright www.weipaidang.net
 */
require_once './PHPMailer/PHPMailerAutoload.php';
class SendMail {
    
    private $receiverAdderss;
    private $sendContent;
    private $smtpPort = 25;
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    private $mailDebug      = 0;
    private $debugOutPut    = 'html';
    private $host           = '';//邮件服务器地址
    private $smtpAuth       = true;
    private $userName       = '';//邮箱登录名称
    private $passWord       = '';//邮箱登录密码
    private $setFromAddress = '';//邮箱地址
    private $setFromName    = '';//邮件发送方
    private $addReplyTo     = '';//邮箱回复地址
    private $altBody        = '';
    private $Smtp;

    public function __construct($receiverAdderss, $receiverName, $subject, $sendContent){
        //实例化邮件发送类
        $this->Smtp = new PHPMailer;  
        $this->Smtp-> isSMTP();
        $this->Smtp->SMTPDebug    = $this->mailDebug;
        $this->Smtp->Debugoutput  = $this->debugOutPut;
        $this->Smtp->Host         = $this->host;
        $this->Smtp->Port         = $this->smtpPort;
        $this->Smtp->SMTPAuth     = $this->smtpAuth;
        $this->Smtp->Username     = $this->userName;
        $this->Smtp->Password     = $this->passWord;
        $this->Smtp->Subject      = '邮件发送测试';
        $this->Smtp->setFrom($this->setFromAddress, $this->setFromName);
        $this->Smtp->addReplyTo($this->setFromAddress, $this->setFromName);
        $this->Smtp->addAddress($receiverAdderss, $receiverName);
        $this->Smtp->AltBody = $this->altBody;
        $this->sendContent = $sendContent;
    }

    public function send(){
        $this->Smtp->msgHTML($this->sendContent, '');
        $result = $this->Smtp->send();
        if(!$result){
            echo "Mailer Error: " . $this->Smtp->ErrorInfo;
        }else{
            echo '邮件已发送';
        }
    }

    private function sendContent(){

    }
}

$sendMail = new SendMail($receiver, $receiver_name,$title, $content);
$sendRst = $sendMail->send();