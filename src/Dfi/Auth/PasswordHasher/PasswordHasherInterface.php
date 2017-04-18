<?
namespace Dfi\Auth\PasswordHasher;

interface PasswordHasherInterface
{

    public function hash($password);

    public function isValid($hash, $password);

}