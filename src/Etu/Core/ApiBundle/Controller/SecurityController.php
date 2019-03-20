<?php

namespace Etu\Core\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthAuthorization;
use Etu\Core\ApiBundle\Entity\OauthAuthorizationCode;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\ApiBundle\Entity\OauthScope;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Oauth\OauthServer;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function authorizeAction(Request $request)
    {
        /*
         * Initialize OAuth
         */
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if (!$request->query->has('client_id')) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'L\'application externe n\'a pas été trouvée. Vous avez été redirigé vers EtuUTT.',
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        }

        // Find the client
        /** @var OauthClient $client */
        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([
            'clientId' => $request->query->get('client_id'),
            'deletedAt' => null,
        ]);

        if (!$client) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'L\'application externe n\'a pas été trouvée. Vous avez été redirigé vers EtuUTT.',
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        }

        // Check if user is logged in and can use external applications
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY') && !$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => $this->get('translator')->trans('user.api_login.login', ['%name%' => $client->getName()]),
            ]);
        } elseif (!$this->isGranted('ROLE_API_USE')) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => $this->get('translator')->trans('user.api_login.orga'),
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        }
        $this->denyAccessUnlessGranted('ROLE_API_USE');

        // Get current user
        $user = $this->getUser();

        $requestedScopes = ['public'];

        if ($request->query->has('scope')) {
            $requestedScopes = array_unique(array_merge($requestedScopes, explode(' ', $request->query->get('scope'))));
        }
        if ($request->query->has('scopes')) { //deprecated
            $requestedScopes = array_unique(array_merge($requestedScopes, explode(' ', $request->query->get('scopes'))));
        }

        // Search if user already approved the app
        $authorization = $em->getRepository('EtuCoreApiBundle:OauthAuthorization')->findOneBy([
            'client' => $client,
            'user' => $user,
        ]);

        if ($authorization) {
            $authorizationScopes = [];

            foreach ($authorization->getScopes() as $scope) {
                $authorizationScopes[] = $scope->getName();
            }

            // Compare scopes : if more requested, reask authorization, otherwise redirect
            $newScopes = array_diff($requestedScopes, $authorizationScopes);

            if (empty($newScopes)) {
                $authorizationCode = new OauthAuthorizationCode();
                $authorizationCode->setUser($user);
                $authorizationCode->setClient($client);
                $authorizationCode->generateCode();

                foreach ($authorization->getScopes() as $scope) {
                    $authorizationCode->addScope($scope);
                }

                $em->persist($authorizationCode);
                $em->flush();

                if ($request->query->has('state')) {
                    return $this->redirect($client->getRedirectUri().'?authorization_code='.$authorizationCode->getCode().'&code='.$authorizationCode->getCode().'&state='.$request->query->get('state', ''));
                }

                return $this->redirect($client->getRedirectUri().'?authorization_code='.$authorizationCode->getCode().'&code='.$authorizationCode->getCode());
            }
        }

        // Scopes
        $qb = $em->createQueryBuilder();

        /** @var OauthScope[] $scopes */
        $scopes = $qb->select('s')
            ->from('EtuCoreApiBundle:OauthScope', 's')
            ->where($qb->expr()->in('s.name', $requestedScopes))
            ->orderBy('s.weight', 'ASC')
            ->getQuery()
            ->getResult();

        // If the use didn't already approve the app, ask him / her
        $form = $this->createFormBuilder()
            ->add('state', HiddenType::class, ['data' => $request->query->get('state', '')])
            ->add('accept', SubmitType::class, ['label' => 'Oui, accepter', 'attr' => ['class' => 'btn btn-primary', 'value' => '1']])
            ->add('cancel', SubmitType::class, ['label' => 'Non, annuler', 'attr' => ['class' => 'btn btn-default', 'value' => '0']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->get('form');

            if (isset($formData['accept'])) {
                // Remove old authorizations
                $em->createQueryBuilder()
                    ->delete()
                    ->from('EtuCoreApiBundle:OauthAuthorization', 'a')
                    ->where('a.client = :client')
                    ->andWhere('a.user = :user')
                    ->setParameter('client', $client->getId())
                    ->setParameter('user', $this->getUser()->getId())
                    ->getQuery()
                    ->execute();

                $authorizationCode = new OauthAuthorizationCode();
                $authorizationCode->setUser($this->getUser());
                $authorizationCode->setClient($client);
                $authorizationCode->generateCode();

                /** @var OauthScope[] $defaultScopes */
                $defaultScopes = $em->getRepository('EtuCoreApiBundle:OauthScope')->findBy(['isDefault' => true]);

                foreach ($defaultScopes as $defaultScope) {
                    $authorizationCode->addScope($defaultScope);
                }

                foreach ($scopes as $scope) {
                    if (!$scope->getIsDefault()) {
                        $authorizationCode->addScope($scope);
                    }
                }

                $em->persist($authorizationCode);

                // Persist authorization to not ask anymore
                $em->persist(OauthAuthorization::createFromAuthorizationCode($authorizationCode));

                $em->flush();

                return $this->redirect($client->getRedirectUri().'?authorization_code='.$authorizationCode->getCode().'&code='.$authorizationCode->getCode().'&state='.($formData['state'] ?? ''));
            }

            return $this->redirect($client->getRedirectUri().'?error=authentification_canceled&error_message=L\'utilisateur a annulé l\'authentification.');
        }

        return [
            'client' => $client,
            'clientName' => $client->getName(),
            'user' => $client->getUser(),
            'scopes' => $scopes,
            'form' => $form->createView(),
        ];
    }

    /**
     * Classic Oauth2 auth cannot be used for a client application (android app for instance) because it would
     * require to store the app_secret in the local code of the application wich would make it not really secret.
     *
     * To solve this problem, you can redirect the user to this page `https://etu.utt.fr/api/oauth/client-create?name=Name of your app&device=Android OnePlus 5&device_uid=ABCDEFGXYZ&scope=public private_user_account`
     * (via a webview). User will authenticate and then be redirected to `http://etuutt.invalid/?client_id=XXXXX&client_secret=YYYY`.
     * This URI is invalid and will show nothing, but you can easily detect it from you application, close the webview
     * and parse the uri to get the generated `client_id` and `client_secret`.
     *
     * Once you got those parameters you can send them to `/api/oauth/token` with `grant_type=client_credentials` to get
     * a token that you can use with the whole api
     *
     * In case of failure, user will be redirected to: `http://etuutt.invalid/?error=authentification_canceled&error_message=L\'utilisateur a annulé l\'authentification.`
     *
     * @ApiDoc(
     *   section = "OAuth",
     *   description = "Display the authorization page for user to generate new client credential for client applciations (android app for instance)",
     *   parameters = {
     *      {
     *          "name" = "name",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Name of the application that will be shown for the granting page. (3 to 32 characters)"
     *      },
     *      {
     *          "name" = "device",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Name of the device (please try to be explicit by giving a lot of details: OS, Brand, model, etc.) (3 to 128 characters)"
     *      },
     *      {
     *          "name" = "device_uid",
     *          "required" = true,
     *          "dataType" = "string",
     *          "description" = "Universal ID of the device: try to give here and ID that will no change if the application is reinstalled but is different for all devices. If you don't have something like this, you can generate a hash from all device data you got. (3 to 128 characters)"
     *      },
     *      {
     *          "name" = "scope",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "List of the scopes you need for the token, separated by spaces, for instance: `public private_user_account`. If not provided, grant only access to public scope."
     *      },
     *   }
     * )
     *
     * @Route("/client-create", name="oauth_client_create")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function clientCreateAction(Request $request)
    {
        /*
         * Initialize
         */
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if (!$request->query->has('name') || !$request->query->has('device') || !$request->query->has('device_uid')
            || mb_strlen(trim($request->query->get('name'))) < 3
            || mb_strlen(trim($request->query->get('device'))) < 3
            || mb_strlen(trim($request->query->get('device_uid'))) < 3
        ) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'L\'authentification n\'a pas été possible car les parametres donné par l\'application ne sont pas valide. Contactez l\'auteur de l\'application.',
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        }

        // Check if user is logged in and can use external applications
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY') && !$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => $this->get('translator')->trans('user.api_login.login', ['%name%' => $request->query->get('name')]),
            ]);
        } elseif (!$this->isGranted('ROLE_API_USE')) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => $this->get('translator')->trans('user.api_login.orga'),
            ]);

            return $this->redirect($this->generateUrl('homepage'));
        }
        $this->denyAccessUnlessGranted('ROLE_API_USE');

        // Get current user
        $user = $this->getUser();

        // Read scope
        $requestedScopes = ['public'];
        if ($request->query->has('scope')) {
            $requestedScopes = array_unique(array_merge($requestedScopes, explode(' ', $request->query->get('scope'))));
        }

        // Scopes
        $qb = $em->createQueryBuilder();

        /** @var OauthScope[] $scopes */
        $scopes = $qb->select('s')
            ->from('EtuCoreApiBundle:OauthScope', 's')
            ->where($qb->expr()->in('s.name', $requestedScopes))
            ->orderBy('s.weight', 'ASC')
            ->getQuery()
            ->getResult();

        // Approve form
        $form = $this->createFormBuilder()
            ->add('state', HiddenType::class, ['data' => $request->query->get('state', '')])
            ->add('name', HiddenType::class, ['data' => $request->query->get('name', '')])
            ->add('device', HiddenType::class, ['data' => $request->query->get('device', '')])
            ->add('device_uid', HiddenType::class, ['data' => $request->query->get('device_uid', '')])
            ->add('accept', SubmitType::class, ['label' => 'Oui, accepter', 'attr' => ['class' => 'btn btn-primary', 'value' => '1']])
            ->add('cancel', SubmitType::class, ['label' => 'Non, annuler', 'attr' => ['class' => 'btn btn-default', 'value' => '0']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->get('form');

            if (isset($formData['accept'])) {
                // Remove same app name on same device of same user
                $em->createQuery('UPDATE EtuCoreApiBundle:OauthClient c
                                  SET c.deletedAt = :now
                                  WHERE (c.name = :name
                                  and c.device = :device
                                  and c.deviceUID = :deviceUID
                                  and c.user = :user)')
                    ->setParameter('name', $formData['name'])
                    ->setParameter('device', $formData['device'])
                    ->setParameter('deviceUID', $formData['device_uid'])
                    ->setParameter('user', $this->getUser()->getId())
                    ->setParameter('now', new \DateTime())
                    ->execute();

                // Create the new one
                $client = new OauthClient();
                $client->setUser($this->getUser());

                foreach ($scopes as $scope) {
                    $client->addScope($scope);
                }

                $client->setRedirectUri('http://etuutt.invalid');
                $client->setName($formData['name']);
                $client->setDevice($formData['device']);
                $client->setDeviceUID($formData['device_uid']);
                $client->setNative(1);

                $client->generateClientId();
                $client->generateClientSecret();

                $em->persist($client);
                $em->flush();

                $client->upload();

                return $this->redirect('http://etuutt.invalid/?client_id='.$client->getClientId().'&client_secret='.$client->getClientSecret().'&state='.$formData['state']);
            }

            return $this->redirect('http://etuutt.invalid/?error=authentification_canceled&error_message=L\'utilisateur a annulé l\'authentification.');
        }

        return $this->render('EtuCoreApiBundle:Security:authorize.html.twig', [
            'scopes' => $scopes,
            'form' => $form->createView(),
            'clientName' => mb_substr(trim($request->query->get('name')), 0, 32),
        ]);
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
     *          "name" = "scopes",
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
     *      },
     *      {
     *          "name" = "client_id",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "Your Client ID (given in your developper panel). Required if not using Basic Authentification."
     *      },
     *      {
     *          "name" = "client_secret",
     *          "required" = false,
     *          "dataType" = "string",
     *          "description" = "Your Client Secret Token (given in your developper panel). Required if not using Basic Authentification."
     *      }
     *   }
     * )
     *
     * @Route("/token", name="oauth_token")
     * @Method("POST")
     */
    public function tokenAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $clientId = $request->get('client_id', $request->server->get('PHP_AUTH_USER'));
        $clientSecret = $request->get('client_secret', $request->server->get('PHP_AUTH_PW'));

        /** @var OauthClient $client */
        $client = $em->getRepository('EtuCoreApiBundle:OauthClient')->findOneBy([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'deletedAt' => null,
        ]);

        if (!$client) {
            return $this->format([
                'error' => 'invalid_client',
                'error_message' => 'Client credentials are invalid',
            ], 401, [], $request);
        }

        /** @var OauthServer $server */
        $server = $this->get('etu.oauth.server');

        $request->attributes->set('_oauth_client', $client);

        $grantType = $request->get('grant_type');

        try {
            $token = $server->createToken($grantType, $request);
        } catch (\RuntimeException $exception) {
            return $this->format([
                'error' => 'grant_type_error',
                'error_message' => $exception->getMessage(),
                'received_request' => $request->request->all(),
            ], 400, [], $request);
        }

        return $this->format($server->formatToken($grantType, $token), 200, [], $request);
    }
}
