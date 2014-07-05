<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Organization;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class OrgaTransformer extends AbstractTransformer
{
    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @param Router $router
     * @param UserTransformer $userTransformer
     */
    public function __construct(Router $router, UserTransformer $userTransformer)
    {
        $this->router = $router;
        $this->userTransformer = $userTransformer;
    }

    /**
     * @param Organization $orga
     * @return array
     */
    public function transformUnique($orga)
    {
        $root = $this->router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return [
            'login' => $orga->getLogin(),
            'name' => $orga->getName(),
            'mail' => $orga->getContactMail(),
            'phone' => $orga->getContactPhone(),
            'description' => $orga->getDescription(),
            'descriptionShort' => $orga->getDescriptionShort(),
            'website' => $orga->getWebsite(),
            'image' => $root . 'uploads/logos/' . $orga->getAvatar(),
            'president' => ($orga->getPresident()) ? $this->userTransformer->transform($orga->getPresident()) : null,
        ];
    }
}