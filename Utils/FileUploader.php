<?php

namespace Mail\EmailManagerBundle\Utils;

use DateTime;
use Mail\EmailManagerBundle\Entity\Attachment;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @param UploadedFile $file
     * @return Attachment
     */
    public function upload(UploadedFile $file)
    {
        $sOriginalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sFileName = $sOriginalFilename.'-'.uniqid().'.'.$file->guessExtension();//add a unique id to have a unique filename
        $sMimeType = $file->getMimeType();
        $sTargetDirectory = $this->getTargetDirectory();
        $nSize = $file->getSize();

        $file->move($sTargetDirectory, $sFileName);

        $attachment = new Attachment();
        return $attachment
            ->setFilename($sFileName)
            ->setOriginalFilename($sOriginalFilename)
            ->setPath($sTargetDirectory)
            ->setMimeType($sMimeType)
            ->setCanBeDeleted(true)
            ->setSize($nSize)
            ->setAddedAt(new DateTime("now"))
            ;

    }
/**
     * @param UploadedFile $file
     * @return File
     */
    public function summernoteUpload(UploadedFile $file)
    {
        $sOriginalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sFileName = $sOriginalFilename.'-'.uniqid().'.'.$file->guessExtension();
        $sTargetDirectory = $this->getTargetDirectory();
        return $file->move($sTargetDirectory, $sFileName);

    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mail.utils.file_uploader';
    }
}