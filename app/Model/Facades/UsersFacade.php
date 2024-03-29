<?php

namespace App\Model\Facades;

use App\Model\Api\Facebook\FacebookUser;
use App\Model\Authorizator\AuthenticatedRole;
use App\Model\Entities\ForgottenPassword;
use App\Model\Entities\Permission;
use App\Model\Entities\Resource;
use App\Model\Entities\Role;
use App\Model\Entities\User;
use App\Model\Entities\UserAddress;
use App\Model\Repositories\ForgottenPasswordRepository;
use App\Model\Repositories\PermissionRepository;
use App\Model\Repositories\ResourceRepository;
use App\Model\Repositories\RoleRepository;
use App\Model\Repositories\UserAddressRepository;
use App\Model\Repositories\UserRepository;
use LeanMapper\Exception\InvalidStateException;
use Nette\Security\SimpleIdentity;
use Nette\Utils\Random;

/**
 * Class UsersFacade
 * @package App\Model\Facades
 */
class UsersFacade{
  /** @var UserRepository $userRepository */
  private $userRepository;
  /** @var UserAddressRepository $userAddressRepository */
  private $userAddressRepository;
  /** @var PermissionRepository $permissionRepository */
  private $permissionRepository;
  /** @var RoleRepository $roleRepository */
  private $roleRepository;
  /** @var ResourceRepository $resourceRepository */
  private $resourceRepository;
  /** @var ForgottenPasswordRepository $forgottenPasswordRepository */
  private $forgottenPasswordRepository;

  public function __construct(UserRepository $userRepository, PermissionRepository $permissionRepository,
                              RoleRepository $roleRepository, ResourceRepository $resourceRepository,
                              ForgottenPasswordRepository $forgottenPasswordRepository, UserAddressRepository $userAddressRepository){
    $this->userRepository=$userRepository;
    $this->permissionRepository=$permissionRepository;
    $this->roleRepository=$roleRepository;
    $this->resourceRepository=$resourceRepository;
    $this->forgottenPasswordRepository=$forgottenPasswordRepository;
    $this->userAddressRepository=$userAddressRepository;
  }

  /**
   * Metoda pro načtení jednoho uživatele
   * @param int $id
   * @return User
   * @throws \Exception
   */
  public function getUser(int $id):User {
    return $this->userRepository->find($id);
  }

    /**
     * Metoda pro načtení role uživatele
     * @param string $roleId
     * @return Role
     * @throws \Exception
     */
  public function getRole(string $roleId):Role{
      return $this->roleRepository->findBy(['role_id'=>$roleId]);
  }

  /**
   * Metoda pro načtení jednoho uživatele podle e-mailu
   * @param string $email
   * @return User
   * @throws \Exception
   */
  public function getUserByEmail(string $email):User {
    return $this->userRepository->findBy(['email'=>$email]);
  }

    /**
     * Metoda vrátí true, pokud nalezne uživatele dle emailu, jinak vrací false
     * @param string $email
     * @return bool
     */
    public function getBoolForUserByEmail(string $email):bool {
        try {
            return (bool)$this->userRepository->findBy(['email'=>$email]);
        }catch (\Exception $e){
            return false;
        }
    }

  /**
   * Metoda pro uložení uživatele
   * @param User &$user
   * @return bool
   */
  public function saveUser(User &$user) {
    return (bool)$this->userRepository->persist($user);
  }

    /**
     * Metoda pro vyhledání uživatelů
     * @param array|null $params
     * @param int|null $offset
     * @param int|null $limit
     * @return User[]
     */
  public function findUsers(array $params=null,int $offset=null,int $limit=null):array{
      return $this->userRepository->findAllBy($params,$offset,$limit);
  }

    /**
     * Metoda pro vyhledání a seřazení uživatelů
     * @param array|null $params
     * @param string $order
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function findAndOrderUsers(array $params=null,string $order = 'desc',int $offset=null,int $limit=null):array{
        return$this->userRepository->findAllByAndOrder($params,$order,$offset,$limit);
    }

  public function getAddressesByUser(User $user):array{
    return $this->userAddressRepository->findAllBy(['user_id'=>$user->userId]);
  }

  public function saveUserAddress(UserAddress &$address) {
    return (bool)$this->userAddressRepository->persist($address);
  }

    /**
     * Metoda pro nalezení všech adres uživatele
     * @param int $id
     * @return array
     * @throws \Exception
     */
  public function findUserAdresses(int $id):array{
      return $this->userAddressRepository->findAllBy(['user_id'=>$id]);
  }

  /**
   * Metoda pro nalezení či zaregistrování uživatele podle facebookId, která vrací SimpleIdentity použitelnou pro přihlášení uživatele
   * @param FacebookUser $facebookUser
   * @return SimpleIdentity
   */
  public function getFacebookUserIdentity(FacebookUser $facebookUser):SimpleIdentity {
    try{
      $user = $this->userRepository->findBy(['facebook_id'=>$facebookUser->facebookUserId]);
    }catch (\Exception $e){
      //uživatele se nepovedlo najít podle facebookId
      try{
        $user = $this->getUserByEmail($facebookUser->email);
        //uložíme k uživateli facebookId a uložíme ho
        $user->facebookId=$facebookUser->facebookUserId;
        $this->saveUser($user);
      }catch (\Exception $e){
        //uživatel neexistuje
        $user = new User();
        $user->name=$facebookUser->name;
        $user->email=$facebookUser->email;
        $user->role=null;
        $user->facebookId=$facebookUser->facebookUserId;
        $this->saveUser($user);
      }
    }

    return $this->getUserIdentity($user);
  }

  /**
   * Metoda vracející "přihlašovací identitu" pro daného uživatele
   * @param User $user
   * @return SimpleIdentity
   */
  public function getUserIdentity(User $user):SimpleIdentity {
    //příprava pole pro seznam rolí
    $roles=[];
    //přidáme speciální roli pro přihlášené uživatele
    $roles[]=new AuthenticatedRole($user->userId);
    //přidáme další roli přiřazenou uživateli
    if (!empty($user->role)){
      $roles[]=$user->role->roleId;
    }
    //vytvoření a vrácení SimpleIdentity
    return new SimpleIdentity($user->userId,$roles,['name'=>$user->name,'email'=>$user->email]);
  }

  #region metody pro zapomenuté heslo
  /**
   * Metoda pro vygenerování a uložení nového záznamu pro obnovu hesla
   * @param User $user
   * @return ForgottenPassword
   * @throws \LeanMapper\Exception\InvalidArgumentException
   */
  public function saveNewForgottenPasswordCode(User $user):ForgottenPassword {
    $forgottenPassword=new ForgottenPassword();
    $forgottenPassword->user=$user;
    $forgottenPassword->code=Random::generate(10);
    $this->forgottenPasswordRepository->persist($forgottenPassword);
    return $forgottenPassword;
  }

  /**
   * Metoda pro ověření, zda je platný zadaný kód pro obnovu uživatelského účtu
   * @param User|int $user
   * @param string $code
   * @return bool
   */
  public function isValidForgottenPasswordCode($user, string $code):bool {
    if ($user instanceof User){
      $user=$user->userId;
    }
    $this->forgottenPasswordRepository->deleteOldForgottenPasswords();
    try{
      $this->forgottenPasswordRepository->findBy(['user_id'=>$user, 'code'=>$code]);
      return true;
    }catch (\Exception $e){
      return false;
    }
  }

  /**
   * Metoda pro jednoduché smazání kódů pro obnovu hesla pro konkrétního uživatele
   * @param User|int $user
   */
  public function deleteForgottenPasswordsByUser($user){
    try{
      if ($user instanceof User){
        $user=$user->userId;
      }
      $this->forgottenPasswordRepository->delete(['user_id' => $user]);
    }catch (InvalidStateException $e){
      //ignore error
    }
  }
  #endregion metody pro zapomenuté heslo

  #region metody pro authorizator
  /**
   * @return Resource[]
   */
  public function findResources():array {
    return $this->resourceRepository->findAll();
  }

  /**
   * @return Role[]
   */
  public function findRoles():array {
    return $this->roleRepository->findAll();
  }

  /**
   * @return Permission[]
   */
  public function findPermissions():array {
    return $this->permissionRepository->findAll();
  }
  #endregion metody pro authorizator
}