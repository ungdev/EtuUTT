<?php

namespace Etu\Module\UVBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Module\UVBundle\Entity\Review;

class ReviewTransformer extends AbstractTransformer
{
    /**
     * @param Review $review
     *
     * @return array
     */
    public function transformUnique($review, EmbedBag $includes)
    {
        return array_merge($this->getData($review), $this->getLinks($review));
    }

    /**
     * @return array
     */
    private function getData(Review $review)
    {
        return [
            'id' => $review->getId(),
            'createdAt' => $review->getCreatedAt(),
            'semester' => $review->getSemester(),
            'sender' => ['login' => $review->getSender()->getLogin(), 'fullName' => $review->getSender()->getFullName()],
            'type' => $review->getType(),
            'validated' => $review->getValidated(),
        ];
    }

    /**
     * @return array
     */
    private function getLinks(Review $review)
    {
        return [
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/uvs/reviews/file/'.$review->getFilename(),
                ],
            ],
        ];
    }
}
