<?php

namespace Etu\Module\UploadBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\HttpFoundation\Request;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class MainController extends Controller
{
    /**
     * @Route("/upload", name="upload_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_UPLOAD');

        $directory = $this->getKernel()->getRootDir().'/../web/uploads/users_files/'.$this->getUser()->getLogin();

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $iterator = new \DirectoryIterator($directory);
        $images = array();

        /** @var $file \SplFileInfo */
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array(strtolower($file->getExtension()), array('png', 'jpg', 'jpeg', 'gif', 'bmp'))) {
                $images[] = array(
                    'id' => substr(md5($file->getBasename()), 0, 10),
                    'name' => $file->getBasename(),
                );
            }
        }

        $form = $this->createFormBuilder()
            ->add('file', FileType, array('required' => true))
            ->getForm();

        if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {

            /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = $form->getData()['file'];

            if (!in_array(
                strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)),
                array('png', 'jpg', 'jpeg', 'gif', 'bmp'))) {
                $this->get('session')->getFlashBag()->set('message', array(
                    'type' => 'error',
                    'message' => 'upload.main.index.error_type',
                ));

                return $this->redirect($this->generateUrl('upload_index'));
            }

            if ($file->getSize() > 2000000) {
                $this->get('session')->getFlashBag()->set('message', array(
                    'type' => 'error',
                    'message' => 'upload.main.index.error_size',
                ));

                return $this->redirect($this->generateUrl('upload_index'));
            }

            $name = StringManipulationExtension::slugify(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

            if (file_exists($directory.'/'.$name.'.'.$extension)) {
                $name .= '-'.substr(md5(uniqid(true)), 0, 4);
            }

            $name .= '.'.$extension;

            $file->move($directory, $name);

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'upload.main.index.confirm',
            ));

            return $this->redirect($this->generateUrl('upload_index'));
        }

        return array(
            'images' => $images,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/upload/remove/{id}/{confirm}", defaults={"confirm"=false}, name="upload_remove")
     * @Template()
     */
    public function removeAction($id, $confirm = false)
    {
        $this->denyAccessUnlessGranted('ROLE_UPLOAD');

        $directory = $this->getKernel()->getRootDir().'/../web/uploads/users_files/'.$this->getUser()->getLogin();

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $iterator = new \DirectoryIterator($directory);
        $image = false;

        /** @var $file \SplFileInfo */
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array(strtolower($file->getExtension()), array('png', 'jpg', 'jpeg', 'gif', 'bmp'))) {
                if (substr(md5($file->getBasename()), 0, 10) == $id) {
                    $image = array(
                        'id' => substr(md5($file->getBasename()), 0, 10),
                        'name' => $file->getBasename(),
                        'absolute' => $file->getPathname(),
                    );
                }
            }
        }

        if ($image === false) {
            throw $this->createNotFoundException('Image not found');
        }

        if ($confirm) {
            unlink($image['absolute']);

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'upload.main.remove.confirm',
            ));

            return $this->redirect($this->generateUrl('upload_index'));
        }

        return array(
            'image' => $image,
        );
    }
}
