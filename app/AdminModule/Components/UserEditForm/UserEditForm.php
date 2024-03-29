<?php

namespace App\AdminModule\Components\UserEditForm;

use App\Model\Entities\User;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class UserEditForm
 * @package App\AdminModule\Components\UserEditForm
 *
 * @method onFinished(string $message = '')
 * @method onFailed(string $message = '')
 * @method onCancel()
 */
class UserEditForm extends Form {

    use SmartObject;

    /** @var callable[] $onFinished */
    public $onFinished = [];
    /** @var callable[] $onFailed */
    public $onFailed = [];
    /** @var callable[] $onCancel */
    public $onCancel = [];
    /** @var UsersFacade $usersFacade */
    private $usersFacade;

    /**
     * UserEditForm constructor
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param UsersFacade $usersFacade
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, UsersFacade $usersFacade){
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->usersFacade = $usersFacade;
        $this->createSubcomponents();
    }

    private function createSubcomponents(){
        $userId = $this->addHidden('userId');
        $this->addText('name', 'Jméno uživatele')
            ->setRequired('Uživatel nesmí být bez jména.')
            ->setHtmlAttribute('maxlength',40)
            ->addRule(Form::MAX_LENGTH,'Jméno je příliš dlouhé, může mít maximálně 40 znaků.',40);;
        $this->addText('email', 'E-mail uživatele')
            ->setRequired('Uživatel musí mít zadaný E-mail.')
            ->addRule(Form::EMAIL, 'Zadejte platný email');;
        #region role
        $roles=$this->usersFacade->findRoles();
        $rolesArr=[];
        foreach ($roles as $role){
            if($role->roleId != 'guest' && $role->roleId != 'authenticated'){
                $rolesArr[$role->roleId]=$role->roleId;
            }
        }
        $this->addSelect('roleId','Role',$rolesArr)
            ->setPrompt('--Vyberte roli--')
            ->setRequired(false);
        #endregion role
        $this->addSubmit('ok','uložit')
            ->onClick[]=function (SubmitButton $button){
            $values=$this->getValues('array');
            if(!empty($values['userId'])){
                try {
                    $user=$this->usersFacade->getUser($values['userId']);
                }catch (\Exception $e){
                    $this->onFailed('Daný uživatel nebyl nalezen.');
                    return;
                }
            }else{
                $user = new User();
            }
            if($values['roleId'] != null){
                $user->role=$this->usersFacade->getRole($values['roleId']);
            }else{
                $user->role=null;
            }

            if($this->usersFacade->getBoolForUserByEmail($values['email'])){
                if($user->email == $values['email']){
                    $user->assign($values, ['name', 'email']);
                }else{
                    $this->onFailed('Uživatel s daným emailem již existuje');
                }
            }else{
                $user->assign($values, ['name', 'email']);
            }

            $this->usersFacade->saveUser($user);
            $this->setValues(['userId'=>$user->userId]);
            $this->onFinished('Uživatel byl uložen');
        };
        $this->addSubmit('storno', 'zrušit')
            ->setValidationScope([$userId])
            ->onClick[]=function (SubmitButton $button){
            $this->onCancel();
        };
    }

    /**
     * Metoda pro nastavení výchozích hodnot formuláře
     * @param User|array|object $values
     * @param bool $erase = false
     * @return $this
     */
    public function setDefaults($values, bool $erase = false):self{
        if($values instanceof User){
            $values=[
                'userId'=>$values->userId,
                'name'=>$values->name,
                'email'=>$values->email,
                'roleId'=>$values->role->roleId
            ];
        }
        parent::setDefaults($values, $erase);
        return $this;
    }
}