<?php

namespace Mail\EmailManagerBundle\Utils;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Mail\EmailManagerBundle\Entity\Mail;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
Use Doctrine\ORM\EntityManager;

/**
 * Class MailManager
 * @package Mail\EmailManagerBundle\Utils
 */
class MailManager
{
    /** @var Swift_Mailer $mailer */
    private $mailer;

    /** @var EntityManager $entityManager */
    private $entityManager;

    /**
     * MailManager constructor.
     * @param Swift_Mailer $p_mailer
     * @param EntityManager $p_entityManager
     */
    public function __construct(Swift_Mailer $p_mailer, EntityManager $p_entityManager)
    {
        $this->mailer = $p_mailer;
        $this->entityManager = $p_entityManager;
    }

    public function generateModalEdit(Mail $mail){

    }

    /**
     * @param Mail $p_mail
     * @return bool
     * @throws Exception
     */
    public function sendMail(Mail $p_mail)
    {

        $aRecipients = explode(",", $p_mail->getRecipients());
        $aCarbonCopyRecipients = [];
        $aBlindCarbonCopyRecipients = [];

        /** if $p_mail contains cc addresses */
        if ($sCc = $p_mail->getCarbonCopyRecipients() !== null){
            $aCarbonCopyRecipients = explode(",", $sCc);
        }
        /** if $p_mail contains bcc addresses */
        if ($sBcc = $p_mail->getBlindCarbonCopyRecipients() !== null){
            $aBlindCarbonCopyRecipients = explode(",", $sBcc);
        }


        $email = new Swift_Message(); //email is a Swift_Message
        $email
            ->setFrom($p_mail->getSender())
            ->setTo($aRecipients)
            ->setCc($aCarbonCopyRecipients)
            ->setBcc($aBlindCarbonCopyRecipients)
            ->setSubject($p_mail->getSubject())
            ->setBody( $p_mail->getHtmlContent(),'text/html' )
            ->addPart( $p_mail->getTextContent(), 'text/plain' )
        ;

        foreach ($p_mail->getAttachments() as $attachment) {
            /** attach each attachment to $email */
            $sPath = $attachment->getPath().$attachment->getFilename();
            $email->attach(Swift_Attachment::fromPath($sPath));
        }
        $this->mailer->send($email);
        /** if email is sent update $p_mail and try to save it into DB */
        $p_mail->setIsSent(true);
        $p_mail->setSentAt(
            new DateTime(
                'now',
                new DateTimeZone('Europe/Paris')
            ));
        try {
            $this->entityManager->persist($p_mail);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) { dump($e);die();
        } catch (ORMException $e) {dump($e);die();
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mail.utils.mail_manager';
    }
}