<?php

namespace Etu\Core\UserBundle\Ldap;

class LdapManager
{
	/**
	 * @var resource
	 */
	protected $connection;

	/**
	 * @param $host
	 * @param $port
	 * @throws \RuntimeException
	 */
	public function __construct($host, $port)
	{
		$this->connection = ldap_connect($host, $port);

		if (! $this->connection) {
			throw new \RuntimeException(sprintf('LDAP connection to %s:%s failed.', $host, $port));
		}
	}

	/**
	 * @return Model\User[]|Model\Organization[]
	 */
	public function getAll()
	{
		$infos = ldap_get_entries(
			$this->connection,
			ldap_list(
				$this->connection, 'ou=people,dc=utt,dc=fr', 'uid=*'
			)
		);

		$users = array();

		foreach ($infos as $values) {
			if (! is_numeric($values)) {
				if (($user = $this->map($values)) !== false) {
					$users[] = $user;
				} elseif (($orga = $this->mapOrga($values)) !== false) {
					$users[] = $orga;
				}
			}
		}

		return $users;
	}

	/**
	 * @return Model\User[]
	 */
	public function getStudents()
	{
		$result = array();
		$users = $this->getAll();

		foreach ($users as $user) {
			if ($user instanceof Model\User && $user->getIsStudent()) {
				$result[] = $user;
			}
		}

		return $result;
	}

	/**
	 * @return Model\User[]
	 */
	public function getUsers()
	{
		$result = array();
		$users = $this->getAll();

		foreach ($users as $user) {
			if ($user instanceof Model\User) {
				$result[] = $user;
			}
		}

		return $result;
	}

	/**
	 * @return Model\Organization[]
	 */
	public function getOrgas()
	{
		$result = array();
		$users = $this->getAll();

		foreach ($users as $user) {
			if ($user instanceof Model\Organization) {
				$result[] = $user;
			}
		}

		return $result;
	}

	/**
	 * @param $login
	 * @return bool|Model\User
	 */
	public function getUser($login)
	{
		$infos = ldap_get_entries(
			$this->connection,
			ldap_list(
				$this->connection, 'ou=people,dc=utt,dc=fr', 'uid='.$login
			)
		);

		if (empty($infos[0]) || ! isset($infos[0])) {
			return false;
		}

		return $this->map($infos[0]);
	}

	/**
	 * @param $login
	 * @return bool|Model\Organization
	 */
	public function getOrga($login)
	{
		$infos = ldap_get_entries(
			$this->connection,
			ldap_list(
				$this->connection, 'ou=people,dc=utt,dc=fr', 'uid='.$login
			)
		);

		if (empty($infos[0]) || ! isset($infos[0])) {
			return false;
		}

		return $this->mapOrga($infos[0]);
	}

	/**
	 * @param array $values
	 * @return Model\User
	 */
	private function map(array $values)
	{
		if (
			! isset($values['uid'])
			||  ! isset($values['supannempid'])
			||  ! isset($values['mail'])
			||  ! isset($values['employeetype'])
		) {
			return false;
		}

		if (! isset($values['uv'])) {
			$values['uv'] = array();
		}

		$user = new Model\User();
		$user->setLogin($values['uid'][0]);
		$user->setStudentId($values['supannempid'][0]);
		$user->setMail($values['mail'][0]);
		$user->setFullName($values['displayname'][0]);
		$user->setFirstName($values['givenname'][0]);
		$user->setLastName($values['sn'][0]);
		$user->setFormation($values['formation'][0]);
		$user->setNiveau($values['niveau'][0]);
		$user->setFiliere($values['filiere'][0]);
		$user->setPhoneNumber($values['telephonenumber'][0]);
		$user->setTitle($values['title'][0]);
		$user->setRoom($values['roomnumber'][0]);
		$user->setJpegPhoto($values['jpegphoto'][0]);

		$user->setIsStudent(
			in_array('student', $values['edupersonaffiliation'])
				|| in_array('student', $values['employeetype'])
		);

		$uvs = array();

		foreach ((array) $values['uv'] as $key => $uv) {
			if (is_numeric($key)) {
				$uvs[] = $uv;
			}
		}

		$user->setUvs($uvs);

		return $user;
	}

	/**
	 * @param array $values
	 * @return Model\Organization
	 */
	private function mapOrga(array $values)
	{
		if (
			! isset($values['uid'])
			|| ! isset($values['mail'])
			|| ! isset($values['displayname'])
		) {
			return false;
		}

		$orga = new Model\Organization();
		$orga->setLogin($values['uid'][0]);
		$orga->setMail($values['mail'][0]);
		$orga->setFullName($values['displayname'][0]);
		$orga->setIsStudent(false);

		return $orga;
	}
}
