<?php

namespace Etu\Module\TVBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TVController extends Controller
{
    /**
     * Show stuff on television screen.
     *
     * @Route("/tv/view", name="tv_view")
     * @Template()
     */
    public function viewAction()
    {
        return array(
            // 'images' => $images,
            // 'form' => $form->createView()
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
