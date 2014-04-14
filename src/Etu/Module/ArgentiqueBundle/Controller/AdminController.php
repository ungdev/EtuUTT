<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Doctrine\ORM\EntityManager;
use DPZ\Flickr;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\ArgentiqueBundle\Entity\Collection;
use Etu\Module\ArgentiqueBundle\Entity\Photo;
use Etu\Module\ArgentiqueBundle\Entity\PhotoSet;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/argentique/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/synchronize", name="argentique_admin_synchronize")
     * @Template()
     */
    public function synchronizeAction()
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        if (! in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))) {
            throw new AccessDeniedHttpException('You are not an argentique administrator');
        }

        // Authenticate if needed
        $this->createFlickrAccess();

        file_put_contents(
            $this->getKernel()->getBundle('EtuModuleArgentiqueBundle')->getPath() . '/Resources/config/synchronizing.bool', '1'
        );

        return [];
    }

    /**
     * @Route("/synchronize/start", name="argentique_admin_synchronize_start", options={"expose"=true})
     */
    public function synchronizeStartAction()
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        if (! in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))) {
            throw new AccessDeniedHttpException('You are not an argentique administrator');
        }

        /*
         * Find what we currently have in the database
         */
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Collection[] $collections */
        $collections = $em->getRepository('EtuModuleArgentiqueBundle:Collection')
            ->createQueryBuilder('c')
            ->select('c, s')
            ->leftJoin('c.sets', 's')
            ->getQuery()
            ->getResult();

        // To find which collection to remove, we store it by default as true
        $removes = [
            'collections' => [],
            'sets' => [],
            'photos' => [],
        ];

        foreach ($collections as $key => $collection) {
            unset($collections[$key]);

            $removes['collections'][$collection->getFlickrId()] = $collection;

            $sets = $collection->getSets()->toArray();

            /** @var $set PhotoSet */
            foreach ($sets as $keySet => $set) {
                unset($sets[$keySet]);
                $sets[$set->getFlickrId()] = $set;

                $removes['sets'][$set->getFlickrId()] = $removes;
            }

            $collection->setSets($sets);

            $collections[$collection->getFlickrId()] = $collection;
        }

        /*
         * Compare it with the Flickr information. We recognize insert, update or remove using the flickr identifier.
         */
        $flickr = $this->createFlickrAccess();

        $apiCollections = $flickr->call('flickr.collections.getTree')['collections'];

        if (empty($apiCollections)) {
            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'error',
                'message' => 'argentique.error.no_collection'
            ));

            return $this->redirect($this->generateUrl('argentique_admin_index'));
        }

        $apiCollections = $apiCollections['collection'];

        /** @var Collection[] $imported */
        $imported = [];

        foreach ($apiCollections as $apiCollection) {
            // Insert
            if (! isset($collections[$apiCollection['id']])) {
                $collection = new Collection();
                $collection->setFlickrId($apiCollection['id']);
            }

            // Update
            else {
                $collection = $collections[$apiCollection['id']];
            }

            $collection->setTitle($apiCollection['title']);
            $collection->setDescription($apiCollection['description']);

            // Disable remove for this collection
            $removes['collections'][$collection->getFlickrId()] = false;

            if (isset($apiCollection['set'])) {
                foreach ($apiCollection['set'] as $apiSet) {
                    // Insert
                    if (! isset($collection->getSets()[$apiSet['id']])) {
                        $set = new PhotoSet();
                        $set->setFlickrId($apiSet['id']);
                        $set->setCollection($collection);
                    }

                    // Update
                    else {
                        $set = $collection->getSets()[$apiSet['id']];
                        $set->setCollection($collection);
                    }

                    $set->setTitle($apiSet['title']);
                    $set->setDescription($apiSet['description']);

                    // Disable remove for this collection
                    $removes['sets'][$set->getFlickrId()] = false;

                    $collection->addSet($set);

                    $em->persist($set);
                }
            }

            $em->persist($collection);

            $imported[] = $collection;
        }

        $em->flush();

        /** @var Photo[] $existings */
        $existings = $em->getRepository('EtuModuleArgentiqueBundle:Photo')->findAll();

        foreach ($existings as $key => $existing) {
            unset($existings[$key]);
            $existings[$existing->getFlickrId()] = $existing;

            $removes['photos'][$existing->getFlickrId()] = $existing;
        }

        $importingPhotos = [];

        foreach ($imported as $collection) {
            foreach ($collection->getSets() as $set) {
                $apiPhotos = $flickr->call('flickr.photosets.getPhotos', ['photoset_id' => $set->getFlickrId()]);

                foreach ($apiPhotos['photoset']['photo'] as $apiPhoto) {
                    // Insert
                    if (! isset($existings[$apiPhoto['id']])) {
                        $photo = new Photo();
                        $photo->setFlickrId($apiPhoto['id']);
                        $photo->setTitle($apiPhoto['title']);

                        $importingPhotos[$apiPhoto['id']] = $apiPhoto;
                    }

                    // Update
                    else {
                        $photo = $existings[$apiPhoto['id']];
                    }

                    $photo->setPhotoSet($set);

                    $set->importingPhotos[$apiPhoto['id']] = $photo;

                    // Disable remove for this photo
                    $removes['photos'][$photo->getFlickrId()] = false;
                }
            }
        }

        foreach ($removes['collections'] as $entity) {
            if ($entity) {
                $em->remove($entity);
            }
        }

        $em->flush();

        foreach ($removes['sets'] as $entity) {
            if ($entity) {
                $em->remove($entity);
            }
        }

        $em->flush();

        $uploadDir = $this->getKernel()->getRootDir() . '/../web/argentique';

        /** @var $entity Photo */
        foreach ($removes['photos'] as $entity) {
            if ($entity) {
                @unlink($uploadDir.'/'.$entity->getIcon());
                @unlink($uploadDir.'/'.$entity->getFile());

                $em->remove($entity);
            }
        }

        $em->flush();

        $this->get('session')->set('argentique_sync_collections', $imported);
        $this->get('session')->set('argentique_sync_photos', $importingPhotos);

        $response = new Response(json_encode([
            'count' => count($importingPhotos),
            'photos' => array_values($importingPhotos)
        ]), 200);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/synchronize/end", name="argentique_admin_synchronize_end", options={"expose"=true})
     */
    public function synchronizeEndAction()
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        if (! in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))) {
            throw new AccessDeniedHttpException('You are not an argentique administrator');
        }

        file_put_contents(
            $this->getKernel()->getBundle('EtuModuleArgentiqueBundle')->getPath() . '/Resources/config/synchronizing.bool', '0'
        );

        $response = new Response(json_encode([ 'status' => 'ok' ]), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/synchronize/{photoId}", name="argentique_admin_synchronize_photo", options={"expose"=true})
     */
    public function synchronizePhotoAction($photoId)
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        if (! in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))) {
            throw new AccessDeniedHttpException('You are not an argentique administrator');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // Flickr
        $flickr = $this->createFlickrAccess();

        $apiPhotos = $this->get('session')->get('argentique_sync_photos');
        $apiPhoto = $apiPhotos[$photoId];

        $sizes = $flickr->call('flickr.photos.getSizes', ['photo_id' => $apiPhoto['id']]);

        $uploadDir = $this->getKernel()->getRootDir() . '/../web/argentique';

        // Thumbnail
        file_put_contents($uploadDir.'/'.$apiPhoto['id'].'_'.$apiPhoto['secret'].'_t.jpg', file_get_contents($sizes['sizes']['size'][2]['source']));

        // Original
        file_put_contents($uploadDir.'/'.$apiPhoto['id'].'_'.$apiPhoto['secret'].'_o.jpg', file_get_contents($sizes['sizes']['size'][9]['source']));

        /** @var Collection[] $collections */
        $collections = $this->get('session')->get('argentique_sync_collections');

        $photo = false;
        $photoSet = false;

        foreach ($collections as $collection) {
            foreach ($collection->getSets() as $set) {
                if (isset($set->importingPhotos[$apiPhoto['id']])) {
                    $photo = $set->importingPhotos[$apiPhoto['id']];
                    $photoSet = $set;
                }
            }
        }

        if ($photo) {
            $set = $em->getRepository('EtuModuleArgentiqueBundle:PhotoSet')->find($photoSet->getId());

            $entity = new Photo();
            $entity->setTitle($photo->getTitle());
            $entity->setFlickrId($photo->getFlickrId());
            $entity->setIcon($apiPhoto['id'].'_'.$apiPhoto['secret'].'_t.jpg');
            $entity->setFile($apiPhoto['id'].'_'.$apiPhoto['secret'].'_o.jpg');
            $entity->setPhotoSet($set);

            $em->persist($entity);
            $em->flush();
        }

        $response = new Response(json_encode([
            'title' => $photo->getTitle(),
            'icon' => $photo->getIcon(),
        ]), 200);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @return Flickr
     */
    private function createFlickrAccess()
    {
        @$flickr = new Flickr(
            '03073c12e007751f01ee16ac5488c764',
            '838160e0782e8718',
            $this->generateUrl('argentique_admin_synchronize', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $flickr->authenticate('read');

        return $flickr;
    }
}
