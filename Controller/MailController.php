<?php

namespace Mail\EmailManagerBundle\Controller;

use Exception;
use Mail\EmailManagerBundle\Entity\Attachment;
use Mail\EmailManagerBundle\Entity\Mail;
use Mail\EmailManagerBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MailController
 *
 * @Route("/mail")
 *
 * @package Mail\EmailManagerBundle\Controller
 * @author <laurent.cuadras@numeric-wave.eu>
 */
class MailController extends Controller
{

    /**
     * list of mail
     *
     * @Route("/", name="mail_index", methods={"GET"})
     *
     * @return Response
     */
    public function index()
    {
        $mails = $this->getDoctrine()
            ->getRepository(Mail::class)
            ->findBy([],['sentAt'=>'DESC']);
        return $this->render('@MailEmailManager/mail/index.html.twig', [
            'mails' => $mails,
        ]);
    }

    /**
     * This is a route to test the bundle using
     * @Route("/test", name="mail_testbundle", methods={"GET","POST"})
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function testMail(Request $request)
    {
        $mail = new Mail();
        /** Create a new attachment from server */
        $filename = "piece-jointe-5eb2deb068009.jpeg";
        $path =  $this->getParameter('email_manager_uploads_directory');
        $file = new UploadedFile($path . $filename, $filename);
        $attachment_1 = new Attachment($file, true);
        //$attachment_2 = new Attachment($file); /* p_bCanBeDeleted: false */

        $tagsAndContents= "<head><style>";//tags to delete with content for TextContent Version
        /** Set mail informations */
        $mail
            ->setSender("djin66@gmail.com")
            ->setRecipients("laurent.cuadras@numeric-wave.eu,laurent.cuadras@gmail.com")
            ->setSubject("test")
            ->setHtmlContent($this->renderView("MailEmailManagerBundle:mail:mail.html.twig"))
            ->setTextContent($this->get('mail.utils.html_to_text')->htmlToText($mail->getHtmlContent(), $tagsAndContents))
            ->setIsSent(false)
            ->addAttachment($attachment_1)
            //->addAttachment($attachment_2)
        ;
        /** Save mail to Database */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($mail);
        $entityManager->persist($attachment_1);
        //$entityManager->persist($attachment_2);
        $entityManager->flush();
        /** Go to edit the mail */
        return $this->redirectToRoute("mail_edit",["id"=>$mail->getId()]);

    }

    /**
     * @Route("/new", name="mail_new", methods={"GET","POST"})
     * @param Request $p_request
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function newMail(Request $p_request)
    {
        $sReferer = $p_request->headers->get('referer');

        $mail = new Mail();
        $mail->setSender("laurent.cuadras@gmail.com")//TODO:: put swiftmailer host to a sender
             ->setIsSent(false)
        ;
        $form = $this->createForm(MailType::class, $mail);
        $form->handleRequest($p_request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var  $sAttachmentIds */
            $sAttachmentIds = $form['attachmentIds']->getData();/** get a string for all attachments to add */

            /** if string contains values */
            if ($sAttachmentIds !== "") {
                /** save each attachment in a array */
                $aAttachmentIds = explode(",", $form['attachmentIds']->getData());

                foreach ($aAttachmentIds as $attachmentId) {
                    /** find attachment for each id */
                    $attachmentToAdd = $this->getDoctrine()->getRepository(Attachment::class)
                        ->find($attachmentId);
                    /** if attachment to add exist in DB */
                    if ($attachmentToAdd !== null){
                        /** add attachment to the mail */
                        $mail->addAttachment($attachmentToAdd);
                    }
                }
            }
            $this->get('mail.utils.mail_manager')->sendMail($mail);
            return $this->redirect($p_request->headers->get('referer'));
        }
        /** prepare parameters in array */
        $aParameters = [
            "attachmentMaxSize"=>$this->getParameter('email_manager_attachment_max_size'),
            "attachmentAcceptedFiles"=>$this->getParameter('email_manager_attachment_accepted_files'),
//            ""=>$this->getParameter(),
        ];
        return $this->render('@MailEmailManager/mail/new.html.twig', [
            'mail' => $mail,
            'form' => $form->createView(),
            'parameters'=> $aParameters,
            'referer'=> $sReferer
        ]);
    }

    /**
     * @Route("/edit/{id}", name="mail_edit", methods={"GET","POST"})
     * @param Request $p_request
     * @param Mail $p_mail
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function edit(Request $p_request, Mail $p_mail)
    {
        $form = $this->createForm(MailType::class, $p_mail);
        $form->handleRequest($p_request);
        $sReferer = $p_request->headers->get('referer');
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var  $sAttachmentIds  */
            $sAttachmentIds = $form['attachmentIds']->getData();/** get a string for all attachments to add */

            /** @var  $sAttachmentIdsToRemove */
            $sAttachmentIdsToRemove = $form['attachmentIdsToRemove']->getData(); /** get a string for all attachments to remove */

            /** if string contains values */
            if ($sAttachmentIds !== "") {
                /** save each attachment in a array */
                $aAttachmentIds = explode(",", $form['attachmentIds']->getData());

                foreach ($aAttachmentIds as $nAttachmentId) {
                    /** find attachment for each id */
                    $attachmentToAdd = $this->getDoctrine()->getRepository(Attachment::class)
                        ->find($nAttachmentId);
                    /** if attachment to add exist in DB */
                    if ($attachmentToAdd instanceof Attachment) {
                        /** add attachment to the mail */
                        $p_mail->addAttachment($attachmentToAdd);
                    }
                }
            }

            /** if string contains values */
            if ($sAttachmentIdsToRemove !== "") {
                /** save each attachment in a array */
                $aAttachmentIdsToRemove = explode(",", $form['attachmentIdsToRemove']->getData());

                foreach ($aAttachmentIdsToRemove as $attachmentIdToRemove) {
                    /** find attachment for each id */
                    $attachmentToRemove = $this->getDoctrine()->getRepository(Attachment::class)
                        ->find($attachmentIdToRemove);
                    /** if attachment to remove exist in DB */
                    if ($attachmentToRemove !== null){
                        /** remove attachment from the mail */
                        $p_mail->removeAttachment($attachmentToRemove);
                        $attachmentToRemove->setMail();
                        $this->getDoctrine()->getManager()->remove($attachmentToRemove);
                    }
                }
            }
            $this->getDoctrine()->getManager()->flush();

            $this->get('mail.utils.mail_manager')->sendMail($p_mail);// send the mail
            return $this->redirect($sReferer); // redirect to referer
        }
        $aParameters = [
            "attachmentMaxSize"=>$this->getParameter('email_manager_attachment_max_size'),
            "attachmentAcceptedFiles"=>$this->getParameter('email_manager_attachment_accepted_files'),
//            ""=>$this->getParameter(),
        ];
        return $this->render('@MailEmailManager/mail/edit.html.twig', [
            'mail' => $p_mail,
            'form' => $form->createView(),
            'parameters'=> $aParameters,
            'referer'=> $sReferer
        ]);
    }
/**
     * @Route("/send/{id}", name="mail_send", methods={"GET"})
     * @param Request $request
     * @param Mail $mail
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function sendMailAction(Request $request, Mail $mail)
    {
        $this->get('mail.utils.mail_manager')->sendMail($mail);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/{id}", name="mail_delete", methods={"DELETE"})
     * @param Request $p_request
     * @param Mail $p_mail
     * @return RedirectResponse
     */
    public function delete(Request $p_request, Mail $p_mail)
    {
        if ($this->isCsrfTokenValid('delete'.$p_mail->getId(), $p_request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($p_mail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mail_index');
    }




}
