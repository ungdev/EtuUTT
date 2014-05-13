<?php

namespace Etu\Module\CovoitBundle\Model;

use Etu\Module\CovoitBundle\Entity\Covoit;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isDateValid"})
 */
class Proposal
{
    /**
     * @var integer
     *
     * @Assert\Choice(choices={"1", "2"})
     */
    public $type = Covoit::TYPE_FINDING;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $phoneNumber;

    /**
     * @var string
     */
    public $notes;

    /**
     * @var integer
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     */
    public $capacity = 3;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    public $date;

    /**
     * @var ProposalStep[]
     *
     * @Assert\NotBlank()
     */
    public $steps;

    /**
     * Constructor
     *
     * Create the start and end steps.
     */
    public function __construct()
    {
        $this->steps[] = new ProposalStep(0);
        $this->steps[] = new ProposalStep(99);
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function isDateValid(ExecutionContextInterface $context)
    {
        if ($this->date < new \DateTime()) {
            $context->addViolationAt('date', 'covoit.proposal.date.past', [], null);
        }
    }
}