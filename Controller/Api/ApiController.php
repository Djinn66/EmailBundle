<?php

namespace Mail\EmailManagerBundle\Controller\Api;

use Exception;
use Mail\EmailManagerBundle\Entity\Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * Used for upload files with dropzone (attachments)
     * @Route("/add/upload", name="api_add_upload", methods={"GET","POST"})
     * @param Request $p_request
     * @return JsonResponse
     */
    public function apiAddUploadAction(Request $p_request)
    {
        try{
            $attachment = $this->get('mail.utils.file_uploader')->upload($p_request->files->get('file'));

            /** Save attachment into DB */
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($attachment);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'attachment' => strval($attachment->getId()),//id to add in form
            ]);
        }
        catch (Exception $exception){
            return new JsonResponse([$exception],415);//format error
        }

    }

    /**
     *
     * @Route("/add/summernote/img", name="api_add_summernote_img", methods={"GET","POST"})
     * @param Request $p_request
     * @return JsonResponse
     */
    public function apiAddSummernoteImgAction(Request $p_request)
    {
        try{
            $file = $this->get('mail.utils.file_uploader')->summernoteUpload($p_request->files->get('file'));

            return new JsonResponse([
                'file' => $file->getFilename(),
                'success' => true,
            ]);
        }
        catch (Exception $exception){
            return new JsonResponse([$exception],415);
        }

    }

    /**
     *
     * @Route("/delete/upload", name="api_delete_upload", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function apiDeleteUploadAction(Request $request)
    {
        $nAttachmentIdToDelete = $request->request->get('attachmentId');/** get attachment id to delete*/

        $attachment = $this->getDoctrine()
            ->getRepository(Attachment::class)
            ->find($nAttachmentIdToDelete);/** find it into DB*/

        try{
            /** delete the uploaded file links to the attachment */
            unlink($attachment->getPath().$attachment->getFilename());
        }catch (Exception $exception){
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
        /** delete the attachment into DB*/
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($attachment);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true
        ]);

    }

    /**
     *
     * @Route("/get/users", name="api_get_users", methods={"GET"})
     * @return JsonResponse
     */
    public function getUserList()
    {
        //TODO::Replace to an entity list
        $entityManager = $this->getDoctrine()->getManager();
        $users = $entityManager->getRepository('MailUserBundle:User')->findAll();

        if (empty($users)) {
            $error = [
                "errorCode" => 102
            ];

            return new JsonResponse($error);
        }

        $serializer = $this->get('jms_serializer');
        $data = $serializer->serialize($users, 'json');
        $users_tab = [];
        $results = json_decode($data, true);
        $i=0;
        foreach ($results as $result){
//            $users_tab[$i]['id'] = $result['id'];
//            $users_tab[$i]['name'] = $result['username'];
            $users_tab[$i]/*['email']*/ = $result['email'];
            $i++;
        }

        return new JsonResponse($users_tab);
    }
}