<?php

namespace Etu\Module\SIABundle\Ldap;

use Etu\Module\SIABundle\Ldap\Model\User;
use FreeIPA\APIAccess\Main;

class LdapManager
{
    /**
     * @var resource
     */
    protected $ipa;
    protected $host;
    protected $certificate;
    protected $kernel;
    protected $user;
    protected $user_password;
    public $logs;

    /**
     * @param $host
     * @param $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $certificate
     *
     * @throws \RuntimeException
     */
    public function __construct($host, $user, $pass, $certificate)
    {
        $this->connection = null;
        $this->entityManager = null;
        $this->host = $host;
        $this->certificate = $certificate;
        $this->logs = [];
        $this->user = $user;
        $this->user_password = $pass;
    }

    /**
     * @param $uid
     *
     * @return string
     */
    public function findUidFree($uid)
    {
        $this->connect();
        $infos = $this->connection->user()->find([$uid]);

        if (empty($infos[0]) || !isset($infos[0])) {
            return $uid;
        }

        $uid_inuse = [];
        foreach ($infos as $info) {
            $uid_inuse[] = $info->uid[0];
        }

        $i = 0;
        $freeUid = $uid;
        while (in_array($freeUid, $uid_inuse)) {
            ++$i;
            $freeUid = $uid.$i;
        }

        return $freeUid;
    }

    /**
     * @param $userId
     *
     * @return bool|Model\User
     */
    public function getUserByEtuId($userId)
    {
        $this->connect();
        $infos = $user_info = $this->connection->user()->findBy('carlicense', 'etu:'.(int) $userId);

        if (empty($infos[0]) || !isset($infos[0])) {
            return false;
        }

        return $this->map(get_object_vars($infos[0]));
    }

    public function deleteGroup($slug) {
        $this->connect();
        $this->connection->group()->del("cn=".$slug.",cn=groups,cn=accounts,dc=uttnetgroup,dc=net");
    }

    /**
     * @param $studentId
     *
     * @return bool|Model\User
     */
    public function getUserByStudentId($studentId)
    {
        $this->connect();
        $infos = $user_info = $this->connection->user()->findBy('employeenumber', (int) $studentId);

        if (empty($infos[0]) || !isset($infos[0])) {
            return false;
        }

        return $this->map(get_object_vars($infos[0]));
    }

    /**
     * @return User
     */
    private function map(array $values)
    {
        $user = new User();
        $user->setLogin($values['uid'][0]);
        $user->setMail($values['mail'][0]);
        $user->setFirstName($values['givenname'][0]);
        $user->setLastName($values['sn'][0]);

        if (isset($values['employeenumber'][0])) {
            $user->setStudentId($values['employeenumber'][0]);
        }

        if (isset($values['carlicense'][0])) {
            $ids = explode(':', $values['carlicense'][0]);
            $user->setEtuUttId($ids[1]);
        }

        return $user;
    }

    private function connect()
    {
        if ($this->connection) {
            return;
        }
        if (!file_exists($this->certificate)) {
            $context = stream_context_create(['ssl' => [
                'verify_peer' => false,
            ]]);
            file_put_contents($this->certificate, fopen('https://'.$this->host.'/ipa/config/ca.crt', 'r', false, $context));
        }

        $this->connection = new Main($this->host, $this->certificate);
        $this->connection->connection()->authenticate($this->user, $this->user_password);
    }

    private function mapToIpa(User $user)
    {
        $data = [
            'givenname' => $user->getFirstName(),
            'sn' => $user->getLastName(),
            'uid' => $user->getLogin(),
            'mail' => $user->getMail(),
            'carlicense' => 'etu:'.$user->getEtuUttId(),
        ];
        if ($user->getStudentId()) {
            $data['employeenumber'] = $user->getStudentId();
        }
        if ($user->getUserPassword()) {
            $data['userpassword'] = $user->getUserPassword();
        }

        return $data;
    }

    public function create(User $user)
    {
        $this->connect();

        $data = $this->mapToIpa($user);

        return $this->connection->user()->add($data);
    }

    public function modify(User $user)
    {
        $this->connect();

        $data = $this->mapToIpa($user);
        $uid = $data['uid'];
        unset($data['uid']);

        return $this->connection->user()->modify($uid, $data);
    }

    public function getConnection(): Main
    {
        $this->connect();

        return $this->connection;
    }
}
