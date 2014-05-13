<?php

namespace Etu\Module\CovoitBundle\Model;

use Etu\Core\CoreBundle\Entity\City;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isPriceValid"})
 */
class ProposalStep
{
    /**
     * @var City
     *
     * @Assert\NotBlank()
     */
    public $city = 749;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $adress;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $hour;

    /**
     * @var integer
     *
     * @Assert\NotBlank()
     */
    public $order;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     */
    public $price;

    /**
     * @param $order
     */
    function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function isPriceValid(ExecutionContextInterface $context)
    {
        if (! is_numeric($this->price)) {
            $context->addViolationAt('date', 'covoit.proposal_step.price.not_numeric', [], null);
        }
    }
}