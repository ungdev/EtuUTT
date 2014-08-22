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

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var FLickr $flickr */
        $flickr = $this->createFlickrAccess();


        /* ************************************************************************
         *  Collections
         * ************************************************************************/

        /** @var Collection[] $dbCollections */
        $dbCollections = $em->getRepository('EtuModuleArgentiqueBundle:Collection')->findAll();

        /** @var array $apiCollections */
        $apiCollections = $flickr->call('flickr.collections.getTree')['collections'];

        // If there is no collections in API, remove all the entities in local
        if (empty($apiCollections)) {
            $em->createQueryBuilder()
                ->delete('EtuModuleArgentiqueBundle:Collection')
                ->getQuery()
                ->execute();

            $em->createQueryBuilder()
                ->delete('EtuModuleArgentiqueBundle:PhotoSet')
                ->getQuery()
                ->execute();

            $photos = $em->getRepository('EtuModuleArgentiqueBundle:Photo')->findAll();

            foreach ($photos as $photo) {
                $em->remove($photo);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'error',
                'message' => 'argentique.error.no_collection'
            ));

            return $this->redirect($this->generateUrl('argentique_index'));
        } else {
            $apiCollections = $apiCollections['collection'];
        }

        // Map API and local collections
        foreach ($dbCollections as $key => $dbCollection) {
            unset($dbCollections[$key]);
            $dbCollections[$dbCollection->getId()] = $dbCollection;
        }

        foreach ($apiCollections as $key => $apiCollection) {
            unset($apiCollections[$key]);
            $apiCollections[$apiCollection['id']] = $apiCollection;
        }

        // Apply modifications
        $toAdd = array_diff(array_keys($apiCollections), array_keys($dbCollections));
        $toRemove = array_diff(array_keys($dbCollections), array_keys($apiCollections));
        $toUpdate = [];

        foreach (array_keys($dbCollections) as $id) {
            if (! in_array($id, $toRemove)) {
                $toUpdate[] = $id;
            }
        }

        /** @var Collection[] $newCollections */
        $newCollections = [];

        // Add
        foreach ($toAdd as $add) {
            $api = $apiCollections[$add];

            $collection = new Collection($api['id']);
            $collection->setTitle($api['title']);
            $collection->setDescription($api['description']);

            $em->persist($collection);

            $newCollections[$collection->getId()] = $collection;
        }

        $em->flush();

        // Update
        foreach ($toUpdate as $update) {
            $api = $apiCollections[$update];

            $collection = $dbCollections[$update];
            $collection->setTitle($api['title']);
            $collection->setDescription($api['description']);

            $em->persist($collection);

            $newCollections[$collection->getId()] = $collection;
        }

        $em->flush();

        // Remove
        foreach ($toRemove as $remove) {
            $em->remove($dbCollections[$remove]);
        }

        $em->flush();


        /* ************************************************************************
         *  PhotoSets
         * ************************************************************************/

        /** @var PhotoSet[] $dbSets */
        $dbSets = $em->getRepository('EtuModuleArgentiqueBundle:PhotoSet')->findAll();

        /** @var array $apiSets */
        $apiSets = [];

        foreach ($apiCollections as $collection) {
            if (isset($collection['set']) && is_array($collection['set'])) {
                $sets = $collection['set'];

                foreach ($sets as &$set) {
                    $set['collection'] = $collection['id'];
                }

                $apiSets = array_merge($apiSets, $sets);
            }
        }

        // Map API and local sets
        foreach ($dbSets as $key => $dbSet) {
            unset($dbSets[$key]);
            $dbSets[$dbSet->getId()] = $dbSet;
        }

        foreach ($apiSets as $key => $apiSet) {
            unset($apiSets[$key]);
            $apiSets[$apiSet['id']] = $apiSet;
        }

        // Apply modifications
        $toAdd = array_diff(array_keys($apiSets), array_keys($dbSets));
        $toRemove = array_diff(array_keys($dbSets), array_keys($apiSets));
        $toUpdate = [];

        foreach (array_keys($dbSets) as $id) {
            if (! in_array($id, $toRemove)) {
                $toUpdate[] = $id;
            }
        }

        /** @var PhotoSet[] $newSets */
        $newSets = [];

        // Add
        foreach ($toAdd as $add) {
            $api = $apiSets[$add];

            $set = new PhotoSet($api['id']);
            $set->setTitle($api['title']);
            $set->setDescription($api['description']);
            $set->setCollection($newCollections[$api['collection']]);

            $em->persist($set);

            $newSets[$set->getId()] = $set;
        }

        $em->flush();

        // Update
        foreach ($toUpdate as $update) {
            $api = $apiSets[$update];

            $set = $dbSets[$update];
            $set->setTitle($api['title']);
            $set->setDescription($api['description']);
            $set->setCollection($newCollections[$api['collection']]);

            $em->persist($set);

            $newSets[$set->getId()] = $set;
        }

        $em->flush();

        // Remove
        foreach ($toRemove as $remove) {
            $em->remove($dbSets[$remove]);
        }

        $em->flush();


        /* ************************************************************************
         *  Photos
         * ************************************************************************/

        /** @var Photo[] $dbPhotos */
        $dbPhotos = $em->getRepository('EtuModuleArgentiqueBundle:Photo')->findAll();

        /** @var array $apiPhotos */
        $apiPhotos = [];

        foreach ($newSets as $set) {
            $api = $flickr->call('flickr.photosets.getPhotos', [ 'photoset_id' => $set->getId() ]);

            if (isset($api['photoset']) && isset($api['photoset']['photo'])) {
                foreach ($api['photoset']['photo'] as $photo) {
                    $photo['set'] = $set->getId();
                    $apiPhotos[] = $photo;
                }
            }
        }

        // Map API and local photos
        foreach ($dbPhotos as $key => $dbPhoto) {
            unset($dbPhotos[$key]);
            $dbPhotos[$dbPhoto->getId()] = $dbPhoto;
        }

        foreach ($apiPhotos as $key => $apiPhoto) {
            unset($apiPhotos[$key]);
            $apiPhotos[$apiPhoto['id']] = $apiPhoto;
        }

        // Apply modifications
        $toAdd = array_diff(array_keys($apiPhotos), array_keys($dbPhotos));
        $toRemove = array_diff(array_keys($dbPhotos), array_keys($apiPhotos));
        $toUpdate = [];

        foreach (array_keys($dbPhotos) as $id) {
            if (! in_array($id, $toRemove)) {
                $toUpdate[] = $id;
            }
        }

        /** @var Photo[] $newPhotos */
        $newPhotos = [];

        // Add
        foreach ($toAdd as $add) {
            $api = $apiPhotos[$add];

            $photo = new Photo($api['id']);
            $photo->setTitle($api['title']);
            $photo->setReady(false);
            $photo->setPhotoSet($newSets[$api['set']]);

            $em->persist($photo);

            $newPhotos[$photo->getId()] = $photo;
        }

        $em->flush();

        // Update
        foreach ($toUpdate as $update) {
            $api = $apiPhotos[$update];

            $photo = $dbPhotos[$update];
            $photo->setTitle($api['title']);
            $photo->setPhotoSet($newSets[$api['set']]);

            $em->persist($photo);

            $newPhotos[$photo->getId()] = $photo;
        }

        $em->flush();

        // Remove
        foreach ($toRemove as $remove) {
            $em->remove($dbPhotos[$remove]);
        }

        $em->flush();

        // Return photos to import
        $toImport = [];

        foreach ($newPhotos as $photo) {
            if (! $photo->getReady()) {
                $toImport[] = $photo->getId();
            }
        }

        $response = new Response(json_encode([
            'count' => count($toImport),
            'photos' => $toImport
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

        $this->get('session')->getFlashBag()->set('message', array(
            'type' => 'success',
            'message' => 'argentique.admin.synchronize.success'
        ));

        return $this->redirect($this->generateUrl('argentique_index'));
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

        // Imagine
        $imagine = new \Imagine\Gd\Imagine();

        // Get database photo
        /** @var Photo $photo */
        $photo = $em->getRepository('EtuModuleArgentiqueBundle:Photo')->find($photoId);

        if ($photo) {
            // Sizes
            $sizes = $flickr->call('flickr.photos.getSizes', ['photo_id' => $photo->getId()]);

            // Download the photo
            $uploadDir = $this->getKernel()->getRootDir() . '/../web/uploads/argentique';

            // Thumbnail
            file_put_contents($uploadDir.'/'.$photo->getId().'_t.jpg', file_get_contents($sizes['sizes']['size'][2]['source']));

            // Original
            $image = $imagine->open($sizes['sizes']['size'][9]['source']);

            $width = $image->getSize()->getWidth();
            $height = $image->getSize()->getHeight();

            if ($width > $height) {
                $box = new \Imagine\Image\Box(1500, 1500 * ($height / $width));
            } elseif ($width < $height) {
                $box = new \Imagine\Image\Box(1500 * ($width / $height), 1500);
            } else {
                $box = new \Imagine\Image\Box(1500, 1500);
            }

            $image->resize($box)->save($uploadDir.'/'.$photo->getId().'_o.jpg');

            $photo->setFile($photo->getId().'_o.jpg');
            $photo->setIcon($photo->getId().'_t.jpg');
            $photo->setReady(true);

            $em->persist($photo);
            $em->flush();

            $response = new Response(json_encode([
                'title' => $photo->getTitle(),
                'icon' => $photo->getIcon(),
            ]));
        } else {
            $response = new Response(json_encode([
                'title' => 'Not found',
                'icon' => 'Not found',
            ]));
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @return Flickr
     */
    private function createFlickrAccess()
    {
        @$flickr = new Flickr(
            $this->container->getParameter('argentique_client_id'),
            $this->container->getParameter('argentique_client_secret'),
            $this->generateUrl('argentique_admin_synchronize', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $flickr->authenticate('read');

        return $flickr;
    }
}
