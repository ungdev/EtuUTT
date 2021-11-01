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
        return [
            // 'images' => $images,
            // 'form' => $form->createView()
        ];
    }

    /**
     * @Route("/upload/remove/{id}/{confirm}", defaults={"confirm"=false}, name="upload_remove")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $confirm
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
            if ($file->isFile() && in_array(mb_strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                if (mb_substr(md5($file->getBasename()), 0, 10) == $id) {
                    $image = [
                        'id' => mb_substr(md5($file->getBasename()), 0, 10),
                        'name' => $file->getBasename(),
                        'absolute' => $file->getPathname(),
                    ];
                }
            }
        }

        if (false === $image) {
            throw $this->createNotFoundException('Image not found');
        }

        if ($confirm) {
            unlink($image['absolute']);

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'upload.main.remove.confirm',
            ]);

            return $this->redirect($this->generateUrl('upload_index'));
        }

        return [
            'image' => $image,
        ];
    }
}
