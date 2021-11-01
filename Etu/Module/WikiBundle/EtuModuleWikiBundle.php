<?php

namespace Etu\Module\WikiBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;

class EtuModuleWikiBundle extends Module
{
    /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        $this->getSidebarBuilder()
             ->getBlock('base.sidebar.services.title')
             ->add('base.user.menu.wiki')
                 ->setIcon('wiki.png')
                 ->setUrl($this->getRouter()->generate('wiki_list'))
                 ->setPosition(2)
             ->end()
        ->end()
        ->getBlock('base.sidebar.services.title')
            ->add('base.user.menu.nutt')
                ->setIcon('newspaper.png')
                ->setUrl($this->getRouter()->generate('wiki_view', ['slug' => 'archives-des-nutt-lectures', 'organization' => 'nutt']))
                ->setPosition(10)
            ->end()
        ->end();
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'wiki';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Wiki';
    }

    /**
     * Module author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Aurelien Labate';
    }

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module de Wiki';
    }

    /**
     * @return array
     */
    public function getAvailablePermissions()
    {
        return [
            new OrgaPermission('wiki', 'Peut gérer le wiki de l\'association'),
        ];
    }

    /**
     * Define the modules requirements (the other required modules) using their identifiers.
     *
     * @return array
     */
    public function getRequirements()
    {
        return [
            'upload',
        ];
    }
}
