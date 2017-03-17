<?php

namespace Etu\Module\ForumBundle\Model;

use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\ForumBundle\Entity\Category;
use Etu\Module\ForumBundle\Entity\Permissions;

/**
 * Check permissions for a given user about a given page.
 */
class PermissionsChecker
{
    /**
     * @var User|Organization
     */
    protected $user;

    /**
     * @var Member[]
     */
    protected $memberships;

    /**
     * @param User|Organization $user
     */
    public function __construct($user)
    {
        if ($user == null) {
            $user = new User();
        }
        $this->user = $user;
        $this->memberships = ($this->user instanceof User) ? $this->user->getMemberships() : [];
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canRead(Category $category)
    {
        $permissions = new Permissions();

        foreach ($category->getPermissions() as $value) {
            if ($value->getType() == 1) {
                $permissions = $value;
            }
        }
        if ($permissions->getRead()) {
            return true;
        }

        if ($this->user instanceof Organization) {
            foreach ($category->getPermissions() as $value) {
                if ($value->getOrganization() == $this->user) {
                    $permissions = $value;
                }
            }
            if ($permissions->getRead()) {
                return true;
            }
        } else {
            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getRead()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getRead()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canPost(Category $category)
    {
        if ($category->getDepth() == 0 || $this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getPost()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getPost()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getPost()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canAnswer(Category $category)
    {
        if ($this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getAnswer()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getAnswer()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getAnswer()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canEdit(Category $category)
    {
        if ($this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getEdit()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getEdit()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getEdit()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canSticky(Category $category)
    {
        if ($this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getSticky()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getSticky()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getSticky()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canLock(Category $category)
    {
        if ($this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getLock()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getLock()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getLock()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canMove(Category $category)
    {
        if ($this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getMove()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getMove()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getMove()) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function canDelete(Category $category)
    {
        if ($this->user instanceof Organization) {
            return false;
        }

        if (!$this->user->isReadOnly()) {
            $permissions = new Permissions();

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 1) {
                    $permissions = $value;
                }
            }
            if ($permissions->getDelete()) {
                return true;
            }

            foreach ($category->getPermissions() as $value) {
                if ($value->getType() == 2) {
                    foreach ($this->memberships as $membership) {
                        if ($value->getOrganization() == $membership->getOrganization()) {
                            $permissions = $value;
                            if ($permissions->getDelete()) {
                                return true;
                            }
                        }
                    }
                }
                if ($value->getType() == 3) {
                    if ($value->getUser() == $this->user) {
                        $permissions = $value;
                        if ($permissions->getDelete()) {
                            return true;
                        }
                    }
                }
            }
        }
    }
}
