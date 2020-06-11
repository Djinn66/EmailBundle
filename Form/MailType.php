<?php

namespace Mail\EmailManagerBundle\Form;

use Mail\EmailManagerBundle\Entity\Mail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sender', EmailType::class,[
                'disabled' => true,
                'label'=>'Expéditeur'
            ])
            ->add('recipients', TextType::class,[
                'attr' => ['class'=>'emailAddressTags  text-recipients'],
                'label'=>'Destinataires'
            ])
            ->add('carbon_copy_recipients', TextType::class,[
                'attr' => ['class'=>'emailAddressTags  text-carbon_copy_recipients'],
                'required' => false
            ])
            ->add('blind_carbon_copy_recipients', TextType::class,[
                'attr' => ['class'=>'emailAddressTags  text-blind_carbon_copy_recipients'],
                'required' => false
            ])
            ->add('subject', TextType::class, [
                'label'=>'Sujet'
            ])
            ->add('attachmentIds', HiddenType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('attachmentIdsToRemove', HiddenType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('htmlContent', TextareaType::class, [
                'attr' => ['class'=>'summernote'],
                'label'=>'Contenu du mail'
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mail::class,
        ]);
    }
}