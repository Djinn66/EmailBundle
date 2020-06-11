<?php

namespace Mail\EmailManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class MailEmailManagerExtension
 *
 * @package Mail\EmailManagerBundle\DependencyInjection
 *
 * @author <Laurent mail here>
 */
class MailEmailManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $parameter => $value)
            $container->setParameter(sprintf('mail_email_manager.%s', $parameter), $value);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
