<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/public/user")
 */
class PublicUserController extends ApiController
{
    /**
     * Once you connected the user with EtuUTT, you will want to retrieve informations
     * about him / her.
     *
     * This endpoint allows you to retrieve such informations.
     *
     * It only includes public informations (so you don't need scope `private_user_account`
     * to access this endpoint). For more complete informations, use `private_user_account`
     * scope and use endpoint `/api/private/user/account`.
     *
     * @ApiDoc(
     *   section = "User - Public data",
     *   description = "Get the public informations about the current user"
     * )
     *
     * @Route("/account", name="api_public_user_account", options={"expose"=true})
     * @Method("GET")
     */
    public function accountAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('EtuUserBundle:User')->find($this->getAccessToken()->getUser());

        return $this->format([
            'data' => $this->get('etu.api.user.transformer')->transform($user)
        ]);
    }
}
