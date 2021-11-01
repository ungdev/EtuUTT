<?php

namespace Etu\Core\UserBundle\Ldap;

class LdapManager
{
    /**
     * @var resource
     */
    protected $connection;
    protected $host;
    protected $port;

    public $logs;

    /**
     * @param $host
     * @param $port
     *
     * @throws \RuntimeException
     */
    public function __construct($host, $port)
    {
        $this->connection = null;
        $this->host = $host;
        $this->port = $port;
        $this->logs = [];
    }

    /**
     * @return Model\User[]|Model\Organization[]
     */
    public function getAll()
    {
        $this->connect();
        $infos = ldap_get_entries(
            $this->connection,
            ldap_list(
                $this->connection, 'ou=people,dc=utt,dc=fr', 'uid=*'
            )
        );

        $users = [];

        foreach ($infos as $values) {
            if (!is_numeric($values)) {
                if (false !== ($user = $this->map($values))) {
                    $users[] = $user;
                } elseif (false !== ($orga = $this->mapOrga($values))) {
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
        $this->connect();
        $result = [];
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
        $this->connect();
        $result = [];
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
        $this->connect();
        $result = [];
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
     *
     * @return bool|Model\User
     */
    public function getUser($login)
    {
        $this->connect();
        $infos = ldap_get_entries(
            $this->connection,
            ldap_list(
                $this->connection, 'ou=people,dc=utt,dc=fr', 'uid='.$login
            )
        );

        if (empty($infos[0]) || !isset($infos[0])) {
            return false;
        }

        return $this->map($infos[0]);
    }

    /**
     * @param $login
     *
     * @return bool|Model\Organization
     */
    public function getOrga($login)
    {
        $this->connect();
        $infos = ldap_get_entries(
            $this->connection,
            ldap_list(
                $this->connection, 'ou=people,dc=utt,dc=fr', 'uid='.$login
            )
        );

        if (empty($infos[0]) || !isset($infos[0])) {
            return false;
        }

        return $this->mapOrga($infos[0]);
    }

    /**
     * @return Model\User
     */
    private function map(array $values)
    {
        if (
            !isset($values['uid'])
            || !isset($values['supannempid'])
            || !isset($values['mail'])
        ) {
            $log = 'uid => '.$values['uid'][0]."\n";

            if (isset($values['displayname'])) {
                $log .= 'displayname => '.$values['displayname'][0]."\n";
            }
            if (isset($values['supannempid'])) {
                $log .= 'supannempid => '.$values['supannempid'][0]."\n";
            }
            if (isset($values['mail'])) {
                $log .= 'mail => '.$values['mail'][0]."\n";
            }
            if (isset($values['employeetype'])) {
                $log .= 'employeetype => '.$values['employeetype'][0]."\n";
            }

            $this->logs[] = $log;

            return false;
        }

        if (!isset($values['employeetype'])) {
            $values['employeetype'] = [];
        }

        if (!isset($values['uv'])) {
            $values['uv'] = [];
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
            || in_array('epf', $values['edupersonaffiliation']) // EPF students but not EPF staff
            || in_array('student', $values['employeetype'])
        );

        $uvs = [];

        foreach ((array) $values['uv'] as $key => $uv) {
            if (is_numeric($key)) {
                $uvs[] = $uv;
            }
        }

        $user->setUvs($uvs);

        return $user;
    }

    /**
     * @return Model\Organization
     */
    private function mapOrga(array $values)
    {
        if (
            !isset($values['uid'])
            || !isset($values['mail'])
            || !isset($values['displayname'])
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

    private function connect()
    {
        if ($this->connection) {
            return;
        }

        $this->connection = ldap_connect($this->host, $this->port);
        if (!$this->connection) {
            throw new \RuntimeException(sprintf('LDAP connection to %s:%s failed.', $this->host, $this->port));
        }
    }
}
