<?php
namespace CTIC\Grh\Employee\Domain;

use Doctrine\ORM\Mapping as ORM;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Employee\Domain\Validation\EmployeeAreaValidation;
use CTIC\App\User\Domain\User;

/**
 * @ORM\Entity(repositoryClass="CTIC\Grh\Employee\Infrastructure\Repository\EmployeeAreaRepository")
 */
class EmployeeArea implements EmployeeAreaInterface
{
    use IdentifiableTrait;
    use EmployeeAreaValidation;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $name;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\App\User\Domain\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @var User
     */
    private $userCreator;

    /**
     * @return string
     */
    public function getName(): string
    {
        return (empty($this->name))? '' : $this->name;
    }

    /**
     * @return User
     */
    public function getUserCreator(): User
    {
        return $this->userCreator;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function setUserCreator(User $user): bool
    {
        if (get_class($user) != User::class) {
            return false;
        }

        $this->userCreator = $user;

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }
}