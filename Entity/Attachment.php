<?php

namespace Mail\EmailManagerBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mail\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Attachment
 *
 * @ORM\Table(name="attachment")
 * @ORM\Entity
 */
class Attachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     */
    private $filename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="original_filename", type="string", length=255, nullable=true)
     */
    private $originalFilename;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=255, nullable=false)
     */
    private $mimeType;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="added_at", type="datetime", options={"default": "CURRENT_TIMESTAMP"} )
     */
    private $addedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=true )
     */
    private $size;

    /**
     * @var Mail|null
     *
     * @ORM\ManyToOne(targetEntity="Mail\EmailManagerBundle\Entity\Mail", inversedBy="attachments")
     * * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mail", referencedColumnName="id",onDelete="CASCADE",)
     * })
     */
    private $mail;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Mail\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="owner", referencedColumnName="id")
     * })
     */
    private $owner;

    /**
     * @var UploadedFile|null
     */
    private $uploadedFile;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_be_deleted", type="boolean")
     */
    private $canBeDeleted;

    /**
     * Constructor
     * @param UploadedFile|null $p_uploadedFile
     * @param bool $p_bCanBeDeleted
     */
    public function __construct($p_uploadedFile = null, $p_bCanBeDeleted = false)
    {
        $this->canBeDeleted = $p_bCanBeDeleted;

        if($p_uploadedFile instanceof UploadedFile){
            $this->size = $p_uploadedFile->getSize();
            $this->mimeType = $p_uploadedFile->getMimeType();
            $this->setPath($p_uploadedFile->getPath());
            $this->filename = $p_uploadedFile->getFilename();
            $this->addedAt = new DateTime();
            $this->addedAt->setTimestamp($p_uploadedFile->getATime());
        }else $this->addedAt = new DateTime("now");

    }


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return Attachment
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return Attachment
     */
    public function setPath($path)
    {
        if(strripos($path,'/')!==(strlen($path)-1)){
            $path.="/";
        }
        $this->path = $path;
        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set addedAt.
     *
     * @param DateTime|null $addedAt
     *
     * @return Attachment
     */
    public function setAddedAt($addedAt = null)
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    /**
     * Get addedAt.
     *
     * @return DateTime|null
     */
    public function getAddedAt()
    {
        return $this->addedAt;
    }

    /**
     * Set size.
     *
     * @param int|null $size
     *
     * @return Attachment
     */
    public function setSize($size = null)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set owner.
     *
     * @param User|null $owner
     *
     * @return Attachment
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return User|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set mimeType.
     *
     * @param string $mimeType
     *
     * @return Attachment
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set originalFilename.
     *
     * @param string $originalFilename
     *
     * @return Attachment
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * Get originalFilename.
     *
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * Set uploadedFile.
     *
     * @param UploadedFile|null $uploadedFile
     *
     * @return Attachment
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    /**
     * Get uploadedFile.
     *
     * @return UploadedFile|null
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @return bool
     */
    public function isCanBeDeleted()
    {
        return $this->canBeDeleted;
    }

    /**
     * @param bool $canBeDeleted
     *
     * @return Attachment
     */
    public function setCanBeDeleted($canBeDeleted)
    {
        $this->canBeDeleted = $canBeDeleted;
        return $this;
    }

    public function __toString()
    {
        return $this->filename;
    }


    /**
     * Set mail.
     *
     * @param Mail|null $mail
     *
     * @return Attachment
     */
    public function setMail(Mail $mail = null)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail.
     *
     * @return Mail
     */
    public function getMail()
    {
        return $this->mail;
    }
}
