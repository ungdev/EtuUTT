<?php

namespace Etu\Module\UploadBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\UploadBundle\Entity\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MainController extends Controller
{
    /**
     * @Route("/upload/index/{organization}", name="upload_index", options={"expose"=true})
     * @Template()
     *
     * @param null|mixed $organization
     */
    public function indexAction(Request $request, $organization = null)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization) {
            $organization = $em->getRepository('EtuUserBundle:Organization')
                ->findOneBy(['login' => $request->get('organization')]);
            if (!$organization) {
                return $this->createNotFoundException('Organization not found');
            }
        }

        $file = new UploadedFile();
        $file->setOrganization($organization);
        $file->setAuthor($this->getUser());
        $file->setValidated(false);

        $form = $this->createFormBuilder($file)
            ->add('name', TextType::class, ['label' => 'upload.main.index.name'])
            ->add('description', TextareaType::class, ['label' => 'upload.main.index.description', 'required' => false]);

        $organization_id = ($organization) ? $organization->getId() : null;
        $organization_name = ($organization) ? $organization->getName() : null;
        $rights = $this->get('etu.upload.permissions_checker');
        foreach (UploadedFile::RIGHT as $right) {
            if ($rights->has($right, $organization_id)) {
                $choices[$this->get('translator')->trans('upload.main.right.'.$right, ['%orga%' => $organization_name])] = $right;
            }
        }
        $form = $form->add('readRight', ChoiceType::class, ['choices' => $choices, 'required' => true, 'label' => 'upload.main.edit.readRight']);
        $form = $form->add('file', FileType::class, ['label' => 'upload.main.index.file']);
        $form = $form->add('submit', SubmitType::class, ['label' => 'upload.main.index.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Only organization member can upload
            if (!$rights->canUpload($organization)) {
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'error',
                    'message' => 'upload.main.index.not_in_organization',
                ]);

                if ($request->headers->get('referer')) {
                    return $this->redirect($request->headers->get('referer'));
                }

                return $this->redirect($this->generateUrl('homepage'));
            }

            $name = preg_replace('/[\/\:\*\?"\|\\\\]/', '-', $file->getName());
            $file->setName($name);

            $extension = $file->file->guessExtension();
            $file->setExtension($extension);

            $em->persist($file);
            $em->flush();

            $file->file->move($this->getKernel()->getRootDir().'/../web/uploads/users_files/', $file->getId());

            $this->get('session')->getFlashBag()->set(
                'message',
                [
                    'type' => 'success',
                    'message' => 'upload.main.index.confirm',
                ]
            );

            return $this->redirect($this->generateUrl('upload_description', ['id' => $file->getId()]));
        }

        if ($organization) {
            $files = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->findBy([
                'organization' => $organization,
            ], ['createdAt' => 'DESC']);
        } else {
            $files = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->findBy([
                'author' => $this->getUser(),
            ], ['createdAt' => 'DESC']);
        }

        return [
            'form' => $form->createView(),
            'rights' => $rights,
            'files' => $files,
            'organization' => $organization,
        ];
    }

    /**
     * @Route("/upload/{download}/{id}/{fullname}", requirements={"download" = "download|view"}, name="upload_download")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $download
     * @param mixed $fullname
     */
    public function downloadAction(Request $request, $id, $download, $fullname)
    {
        // Find file
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->find($id);
        if (!$file) {
            return $this->createNotFoundException('File not found');
        }

        // Check rights
        $rights = $this->get('etu.upload.permissions_checker');
        if (!$rights->canRead($file)) {
            return $this->createAccessDeniedResponse();
        }

        // Redirect to the correct filename
        if ($fullname != $file->getName().'.'.$file->getExtension()) {
            return $this->redirect($this->generateUrl('upload_download', [
                'download' => $download,
                'id' => $id,
                'fullname' => $file->getName().'.'.$file->getExtension(),
            ]), 301);
        }

        // "Print" content
        $response = new BinaryFileResponse($this->getKernel()->getRootDir().'/../web/uploads/users_files/'.$file->getId());
        if ($download == 'download' || $file->getExtension() == 'html') {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getName().'.'.$file->getExtension());
        }

        return $response;
    }

    /**
     * @Route("/upload/description/{id}", name="upload_description")
     * @Template()
     *
     * @param mixed $id
     */
    public function descriptionAction(Request $request, $id)
    {
        // Find file
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->find($id);
        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        // Check rights
        $rights = $this->get('etu.upload.permissions_checker');
        if (!$rights->canRead($file)) {
            return $this->createAccessDeniedResponse();
        }

        if ($file->getOrganization()) {
            $files = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->findBy([
                'organization' => $file->getOrganization(),
            ], ['createdAt' => 'DESC']);
        } else {
            $files = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->findBy([
                'author' => $this->getUser(),
            ], ['createdAt' => 'DESC']);
        }

        return [
            'file' => $file,
            'rights' => $rights,
            'files' => $files,
        ];
    }

    /**
     * @Route("/upload/delete/{id}", name="upload_delete")
     * @Template()
     *
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id)
    {
        // Find file
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->find($id);
        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        // Check rights
        $rights = $this->get('etu.upload.permissions_checker');
        if (!$rights->canDelete($file)) {
            return $this->createAccessDeniedResponse();
        }

        // get organization
        $organization = $file->getOrganization();

        // Delete file
        unlink($this->getKernel()->getRootDir().'/../web/uploads/users_files/'.$file->getId());
        $em->remove($file);
        $em->flush();

        // Confirmation
        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'upload.main.delete.confirm',
        ]);

        return $this->redirect($this->generateUrl('upload_index', [
            'organization' => $organization->getLogin(),
        ]));
    }

    /**
     * @Route("/upload/edit/{id}", name="upload_edit")
     * @Template()
     *
     * @param mixed $id
     */
    public function editAction(Request $request, $id)
    {
        // Find file
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository('EtuModuleUploadBundle:UploadedFile')->find($id);
        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        // Check rights
        $rights = $this->get('etu.upload.permissions_checker');
        if (!$rights->canEdit($file)) {
            return $this->createAccessDeniedResponse();
        }

        $form = $this->createFormBuilder($file)
            ->add('name', TextType::class, ['label' => 'upload.main.index.name'])
            ->add('description', TextareaType::class, ['label' => 'upload.main.index.description', 'required' => false]);

        $organization_id = ($file->getOrganization()) ? $file->getOrganization()->getId() : null;
        $organization_name = ($file->getOrganization()) ? $file->getOrganization()->getName() : null;
        $rights = $this->get('etu.upload.permissions_checker');
        foreach (UploadedFile::RIGHT as $right) {
            if ($rights->has($right, $organization_id)) {
                $choices[$this->get('translator')->trans('upload.main.right.'.$right, ['%orga%' => $organization_name])] = $right;
            }
        }
        $form = $form->add('readRight', ChoiceType::class, ['choices' => $choices, 'required' => true, 'label' => 'upload.main.edit.readRight']);
        $form = $form->add('submit', SubmitType::class, ['label' => 'upload.main.index.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $name = preg_replace('/[\/\:\*\?"\|\\\\]/', '-', $file->getName());
            $file->setName($name);

            $em->persist($file);
            $em->flush();

            $this->get('session')->getFlashBag()->set(
                'message',
                [
                    'type' => 'success',
                    'message' => 'upload.main.edit.confirm',
                ]
            );

            return $this->redirect($this->generateUrl('upload_description', ['id' => $file->getId()]));
        }

        return [
            'form' => $form->createView(),
            'rights' => $rights,
        ];
    }

    /**
     * Editor automatic upload.
     *
     * @Route("/upload/editor/{organization}", name="upload_editor", options={"expose"=true})
     * @Template()
     *
     * @param null|mixed $organization
     */
    public function editorAction(Request $request, $organization = null)
    {
        // Find organization
        $em = $this->getDoctrine()->getManager();
        if ($organization) {
            $organization = $em->getRepository('EtuUserBundle:Organization')
                ->findOneBy(['login' => $request->get('organization')]);
            if (!$organization) {
                return $this->createNotFoundException('Organization not found');
            }
        }

        // Only organization member can upload
        $rights = $this->get('etu.upload.permissions_checker');
        if (!$rights->canUpload($organization)) {
            return $this->createAccessDeniedResponse();
        }

        if (!$request->files->has('file')) {
            return $this->createAccessDeniedResponse();
        }
        $uploadedFile = $request->files->get('file');

        // Find file
        $em = $this->getDoctrine()->getManager();
        $file = $em->createQueryBuilder()
            ->select('f')
            ->from('EtuModuleUploadBundle:UploadedFile', 'f')
            ->where('f.author = :author')->setParameter(':author', $this->getUser())
            ->andWhere('f.name = :name')->setParameter(':name', urldecode(pathinfo($uploadedFile->getClientOriginalName())['filename']))
            ->andWhere('f.extension = :extension')->setParameter(':extension', $uploadedFile->guessExtension())
            ->andWhere('f.createdAt > :date')->setParameter(':date', (new \DateTime())->modify('-60 minute'));
        if ($organization) {
            $file = $file->andWhere('f.organization = :organization')->setParameter(':organization', $organization);
        } else {
            $file = $file->andWhere('f.organization IS NULL');
        }
        $file = $file->getQuery()
            ->getResult();
        if (!$file) {
            $file = new UploadedFile();

            $name = preg_replace('/[\/\:\*\?"\|\\\\]/', '-', pathinfo($uploadedFile->getClientOriginalName())['filename']);
            $file->setName(urldecode($name));

            $file->setExtension($uploadedFile->guessExtension());
            $file->setAuthor($this->getUser());
            $file->setOrganization($organization);
            $file->setDescription('Modification du fichier '.$file->getName());
        } else {
            $file = $file[0];
        }
        $file->setFile($uploadedFile);
        $file->setValidated(false);
        $file->setUpdatedAt(new \DateTime());

        // Validate data
        $validator = $this->get('validator');
        $errors = $validator->validate($file);

        if (count($errors) > 0) {
            $response = new Response();
            $response->setStatusCode(500);
            $response->setContent((string) $errors);

            return $response;
        }

        $em->persist($file);
        $em->flush();
        $file->file->move($this->getKernel()->getRootDir().'/../web/uploads/users_files/', $file->getId());

        return new JsonResponse([
            'location' => $this->generateUrl('upload_download', [
                'download' => 'view',
                'id' => $file->getId(),
                'fullname' => $file->getName().'.'.$file->getExtension(),
            ]),
        ]);
    }

    /**
     * Editor image proxy to let JS edit cross domain images.
     *
     * @Route("/upload/imageproxy", name="upload_imageproxy", options={"expose"=true})
     * @Template()
     *
     * @param mixed $url
     */
    public function proxyAction(Request $request, $url = '')
    {
        // No need for proxy if you cannot upload image
        $rights = $this->get('etu.upload.permissions_checker');
        if (!$rights->canUpload()) {
            return $this->createAccessDeniedResponse();
        }

        // You need to have the url argument
        if (!$request->query->has('url')) {
            throw new \Exception('url parameter is required');
        }
        $url = $request->query->get('url');

        // Check for http/https
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === false || in_array($scheme, ['http', 'https']) === false) {
            throw new \Exception('Invalid protocol');
        }

        $content = file_get_contents($url);
        $info = getimagesizefromstring($content);

        $validMimeTypes = ['image/gif', 'image/jpeg', 'image/png'];
        if ($info === false || in_array($info['mime'], $validMimeTypes) === false) {
            throw new \Exception('Url doesn\'t seem to be a valid image.');
        }

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', $info['mime']);
        $response->setCallback(function () use ($url) {
            $c = curl_init($url);
            curl_exec($c);
            curl_close($c);
        });

        return $response;
    }
}
