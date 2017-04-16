<?
namespace Dfi\Auth\Adapter\PasswordHasher;

interface PasswordHasherInterface
{

    public function hash($password);

    public function isValid($hash, $password);

}