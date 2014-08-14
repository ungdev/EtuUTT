<?php

namespace Etu\Core\UserBundle\Sync\Iterator\Element;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Ldap\Model\User;

use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

/**
 * LDAP element to import in database
 */
class ElementToImport
{
	/**
	 * @var User
	 */
	protected $element;

	/**
	 * @var Registry
	 */
	protected $doctrine;

	/**
	 * @param Registry $doctrine
	 * @param User $element
	 * @throws \RuntimeException
	 */
	public function __construct(Registry $doctrine, User $element)
	{
		if (! $element instanceof User) {
			if (is_object($element)) {
				$type = get_class($element);
			} else {
				$type = gettype($element);
			}

			throw new \RuntimeException(sprintf(
				'EtuUTT synchonizer can only import User objects (%s given)', $type
			));
		}

		$this->element = $element;
		$this->doctrine = $doctrine;
	}

	/**
	 * Import the element in the database
	 */
	public function import($flush = false, $bdeOrga = null)
	{
		if ($this->element instanceof User) {
			return $this->importUser($bdeOrga, $flush);
		}

		return false;
	}

	/**
	 * Import a user in the database
	 *
	 * @param Organization $bdeOrga
	 * @param bool $flush
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	protected function importUser($bdeOrga = null, $flush = false)
	{
		$imagine = new Imagine();
		$webDirectory = __DIR__.'/../../../../../../../web';

		$avatar = $this->element->getLogin().'.jpg';

		if (! file_exists($webDirectory.'/uploads/photos/'.$this->element->getLogin().'.jpg')) {
			// Resize photo
			try {
				$image = $imagine->open('http://local-sig.utt.fr/Pub/trombi/individu/'.$this->element->getStudentId().'.jpg');

				$image->copy()
					->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)
					->save($webDirectory.'/uploads/photos/'.$this->element->getLogin().'.jpg');
			} catch (\Exception $e) {
				$avatar = 'default-avatar.png';
			}
		}

		$niveau = null;
		$branch = $this->element->getNiveau();

		preg_match('/^[^0-9]+/i', $this->element->getNiveau(), $match);

		if (isset($match[0])) {
			$branch = $match[0];
			$niveau = str_replace($branch, '', $this->element->getNiveau());
		}

		$user = new \Etu\Core\UserBundle\Entity\User();
		$user->setAvatar($avatar);
		$user->setLogin($this->element->getLogin());
		$user->setFullName($this->element->getFullName());
		$user->setFirstName($this->element->getFirstName());
		$user->setLastName($this->element->getLastName());
		$user->setFiliere($this->element->getFiliere());
		$user->setFormation(ucfirst(strtolower($this->element->getFormation())));
		$user->setNiveau($niveau);
		$user->setBranch($branch);
		$user->setMail($this->element->getMail());
		$user->setPhoneNumber($this->element->getPhoneNumber());
		$user->setRoom($this->element->getRoom());
		$user->setStudentId($this->element->getStudentId());
		$user->setTitle($this->element->getTitle());
		$user->setIsStudent($this->element->getIsStudent());
		$user->setKeepActive(false);
		$user->setUvs(implode('|', $this->element->getUvs()));

		$this->doctrine->getManager()->persist($user);

		// Subscribe to BDE
		if ($bdeOrga) {
			$subscription = new Subscription();
			$subscription->setEntityType('orga')
				->setEntityId($bdeOrga->getId())
				->setUser($user);

			$this->doctrine->getManager()->persist($subscription);
		}

		// Subscribe to all events
		$subscription = new Subscription();
		$subscription->setEntityType('event')
			->setEntityId(0)
			->setUser($user);

		$this->doctrine->getManager()->persist($subscription);

		// Flush if needed
		if ($flush) {
			$this->doctrine->getManager()->flush();
		}

		return $user;
	}

	/**
	 * @return \Etu\Core\UserBundle\Ldap\Model\Organization|\Etu\Core\UserBundle\Ldap\Model\User
	 */
	public function getElement()
	{
		return $this->element;
	}
}
