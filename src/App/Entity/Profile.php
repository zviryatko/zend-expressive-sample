<?php
/**
 * @file
 * Contains App\Entity\Profile.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Used for user profile.
 *
 * @ORM\Entity
 */
class Profile
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=false, unique=true)
     * @var string
     */
    protected $nickname;

    /**
     * @ORM\Column(type="string", length=60, nullable=false, unique=true)
     * @var string
     */
    protected $mail;

    /**
     * @ORM\Column(type="string", length=60, nullable=false, unique=true)
     * @var string
     */
    protected $pass;

    public function __construct($id, $nickname, $mail, $pass)
    {
        $this->id = $id;
        $this->nickname = $nickname;
        $this->mail = $mail;
        $this->pass = $this->hashPassword($pass);
    }

    /**
     * Hash profile password.
     *
     * @param $pass
     * @return string
     * @throws \Exception
     */
    protected function hashPassword($pass)
    {
        $hash_str = password_hash($pass, PASSWORD_BCRYPT);
        if (!$hash_str) {
            throw new \Exception('Shit happens.');
        }
        return $hash_str;
    }

    /**
     * Verify profile password.
     *
     * @param $pass
     * @return bool
     */
    public function verifyPassword($pass)
    {
        return password_verify($pass, $this->pass);
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function nickname()
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function mail()
    {
        return $this->mail;
    }
}