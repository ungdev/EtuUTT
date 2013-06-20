<?php

namespace Etu\Module\UploadBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/upload", name="upload_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		$directory = $this->getKernel()->getRootDir().'/../web/uploads/'.$this->getUser()->getLogin();

		if (! file_exists($directory)) {
			mkdir($directory, 0777, true);
		}

		$iterator = new \DirectoryIterator($directory);
		$images = array();

		$i = 0;
		$j = 0;

		/** @var $file \SplFileInfo */
		foreach ($iterator as $file) {
			if ($file->isFile() && in_array($file->getExtension(), array('png', 'jpg', 'jpeg', 'gif', 'bmp'))) {
				if ($i == 5) {
					$i = 0;
					$j++;
				}

				$images[$j][] = array(
					'id' => $i.'-'.$j,
					'name' => $file->getBasename()
				);

				$i++;
			}
		}

		return array(
			'images' => $images
		);
	}
}
