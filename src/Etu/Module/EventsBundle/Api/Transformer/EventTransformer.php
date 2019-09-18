<?php

namespace Etu\Module\EventsBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Module\EventsBundle\Entity\Event;

class EventTransformer extends AbstractTransformer
{
    /**
     * @param Event    $event
     * @param EmbedBag $includes
     *
     * @return array
     */
    public function transformUnique($event, EmbedBag $includes)
    {
        return $this->getData($event, $includes);
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    private function getData(Event $event, EmbedBag $includes)
    {
        $result = [];
        $result = array_merge($result, ['id' => $event->getId()]);

        if ($includes->has('title')) {
            $result = array_merge($result, ['title' => $event->getTitle()]);
        }
        if ($includes->has('category')) {
            $result = array_merge($result, ['category' => $event->getCategory()]);
        }
        if ($includes->has('begin')) {
            $result = array_merge($result, ['begin' => $event->getBegin()]);
        }
        if ($includes->has('end')) {
            $result = array_merge($result, ['end' => $event->getEnd()]);
        }
        if ($includes->has('isAllDay')) {
            $result = array_merge($result, ['isAllDay' => $event->getIsAllDay()]);
        }
        if ($includes->has('location')) {
            $result = array_merge($result, ['location' => $event->getLocation()]);
        }
        if ($includes->has('description')) {
            $result = array_merge($result, ['description' => $event->getDescription()]);
        }
        if ($includes->has('privacy')) {
            $result = array_merge($result, ['privacy' => $event->getPrivacy()]);
        }
        if ($includes->has('orga')) {
            $orga = null;
            try {
                $orga = $event->getOrga()->getLogin();
            } catch (\Exception $e) {
                $orga = null;
            }
            $result = array_merge($result, ['orga' => $orga]);
        }

        return $result;
    }
}
