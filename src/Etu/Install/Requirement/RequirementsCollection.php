<?php

namespace Etu\Install\Requirement;

class RequirementCollection
{
    /**
     * @var Requirement[]
     */
    protected $requirements;

    /**
     * @param Requirement $requirement
     * @return $this
     */
    public function add(Requirement $requirement)
    {
        $this->requirements[] = $requirement;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        foreach ($this->requirements as $requirement) {
            if (! $requirement->isFulfilled()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return void
     */
    public function display()
    {
        foreach ($this->requirements as $requirement) {
            $line = ' ';

            if ($requirement->isFulfilled()) {
                $line .= '[OK] ';
            } else {
                $line .= '[ERROR] ';
            }

            $line .= $requirement->getDescription() . "\n";

            echo $line;
        }
    }
}