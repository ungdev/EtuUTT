<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\StatLogin;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/oauth")
 */
class SecurityController extends ApiController
{
    /**
     * To access private user data, you need his/her authorization. This endpoint is an HTML page that ask the current
     * logged user if (s)he wants to accept your application. If (s)he doesn't, (s)he is redirected to EtuUTT. I f(s)he
     * does, (s)he is redirected to your application URL with the parameter `code`.
     *
     * Let's take a small example:
     *
     * Your application "MyApp" (reidrect URL: http://myapp.com) wants to access private data of the current user.
     * To do so, you need to redirect user to this page:
     *
     * `/api/oauth/authorize?response_type=code&client_id=<your_client_id>&scope=public%20private_user_account&state=xyz`
     *
     * If the user accept the application, he will be redirected to `http://myapp.com/?code=<authorization_code>`.
     *
     * And using this code (in the parameter `code`), you are now able to get an `access_token` using `/api/oauth/token`.
     *
     *
     * If an error occured on the page and the client_id is provided, the user will be redirect to:
     * `http://myapp.com/?error=<error_type>&error_description=<error_description>` so you can handle the problem.
     *
     * @ApiDoc(
     *   section = "OAuth",
     *   description = "Display the authorization page for user to allow a given application",
     *   parameters = {
     *      {
     *          "name" = "client_id",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Your client ID (given in your developper panel)"
     *      },
     *      {
     *          "name" = "scope",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "List of the scopes you need for the token, separated by spaces, for instance: `public private_user_account`. If not provided, grant only access to public scope."
     *      },
     *      {
     *          "name" = "response_type",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Must be `code` (only authorization_code is supported for the moment)"
     *      },
     *      {
     *          "name" = "state",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Must be `xyz` (only authorization_code is supported for the moment)"
     *      }
     *   }
     * )
     *
     * @Route("/authorize", name="oauth_authorize")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function authorizeAction(Request $sfRequest)
    {
        if (! $this->getUserLayer()->isUser()) {
            return $this->createAccessDeniedResponse();
        }

        $server = $this->get('oauth.server');

        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();

        if (! $server->validateAuthorizeRequest($request, $response)) {
            return $this->get('etu.response_handler')->handle($sfRequest, $response);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([ 'clientId' => $request->query('client_id') ]);

        if (! $client) {
            throw $this->createNotFoundException();
        }

        $form = $this->createFormBuilder()
            ->add('accept', 'submit', [ 'label' => 'Oui, accepter', 'attr' => [ 'class' => 'btn btn-primary', 'value' => '1' ] ])
            ->add('cancel', 'submit', [ 'label' => 'Non, annuler', 'attr' => [ 'class' => 'btn btn-default', 'value' => '0' ] ])
            ->getForm();

        if ($sfRequest->getMethod() == 'POST' && $form->submit($sfRequest)->isValid()) {
            $formData = $sfRequest->request->get('form', []);

            $em->persist(new StatLogin($client, $this->getUser()));
            $em->flush();

            $server->handleAuthorizeRequest($request, $response, isset($formData['accept']), $this->getUser()->getId());
            $response->send();
            exit;
        }

        $user = $em->getRepository('EtuUserBundle:User')->find($client->getUserId());

        $scopesNames = array_merge(explode(' ', $sfRequest->query->get('scope', 'public')), ['public']);

        $qb = $em->createQueryBuilder();

        $scopes = $qb->select('s')
            ->from('EtuCoreApiBundle:OauthScope', 's')
            ->where($qb->expr()->in('s.scope', $scopesNames))
            ->orderBy('s.weight', 'ASC')
            ->getQuery()
            ->getResult();

        return [
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'form' => $form->createView()
        ];
    }

    /**
     * Create an `access_token` to use the API.
     *
     * There are three methods to create an `access_token`:
     *
     * - `authorization_code`
     * - `refresh_token`
     * - `client_credentials`
     *
     * These methods are called the **the grant types**. The required parameters of the endpoint depends on the chosen
     * grant type.
     *
     * @ApiDoc(
     *   section = "OAuth",
     *   description = "Create a OAuth access token using given grant_type",
     *   parameters = {
     *      {
     *          "name" = "grant_type",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "The grant type to use to create the access_token."
     *      },
     *      {
     *          "name" = "scope",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "List of the scopes you need for the token, separated by spaces, for instance: `public private_user_account`. If not provided, grant only access to public scope."
     *      },
     *      {
     *          "name" = "code",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "The authorization_code generated with /api/oauth/authorize. Required if grant_type == 'authorization_code'."
     *      },
     *      {
     *          "name" = "refresh_token",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "The refresh_token provided on the first access_token retrieval. Required if grant_type == 'refresh_token'."
     *      }
     *   }
     * )
     *
     * @Route("/token", name="oauth_token")
     * @Method("POST")
     */
    public function tokenAction()
    {
        $response = $this->get('oauth.server')->handleTokenRequest(\OAuth2\Request::createFromGlobals());
        return $this->get('etu.response_handler')->handle($this->getRequest(), $response);
    }
}