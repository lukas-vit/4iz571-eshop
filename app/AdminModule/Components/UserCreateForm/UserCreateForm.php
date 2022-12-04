<?php

namespace App\AdminModule\Components\UserCreateForm;

use App\Model\Entities\User;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\Passwords;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class UserCreateForm
 * @package App\AdminModule\Components\UserCreateForm
 *
 * @method onFinished(string $message = '')
 * @method onCancel()
 */
class UserCreateForm extends Form {

    use SmartObject;

    /** @var callable[] $onFinished */
    public $onFinished = [];
    /** @var callable[] $onCancel */
    public $onCancel = [];
    /** @var UsersFacade $usersFacade */
    private $usersFacade;
    /** @var Passwords $passwords */
    private $passwords;

    /**
     * UserCreateForm constructor
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param UsersFacade $usersFacade
     * @param Passwords $passwords
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, UsersFacade $usersFacade, Passwords $passwords){
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->usersFacade=$usersFacade;
        $this->passwords=$passwords;
        $this->createSubcomponents();
    }

    private function createSubcomponents(){
        $this->addText('name','Jméno a příjmení:')
            ->setRequired('Zadejte své jméno')
            ->setHtmlAttribute('maxlength',40)
            ->addRule(Form::MAX_LENGTH,'Jméno je příliš dlouhé, může mít maximálně 40 znaků.',40);
        $this->addEmail('email','E-mail')
            ->setRequired('Zadejte platný email')
            ->addRule(function(Nette\Forms\Controls\TextInput $input){
                try{
                    $this->usersFacade->getUserByEmail($input->value);
                }catch (\Exception $e){
                    //pokud nebyl uživatel nalezen (tj. je vyhozena výjimka), je to z hlediska registrace v pořádku
                    return true;
                }
                return false;
            },'Uživatel s tímto e-mailem je již registrován.');
        #region role
        $roles=$this->usersFacade->findRoles();
        $rolesArr=[];
        foreach ($roles as $role){
            $rolesArr[$role->roleId]=$role->roleId;
        }
        $this->addSelect('roleId','Role',$rolesArr)
            ->setPrompt('--Vyberte roli--')
            ->setRequired(false);
        #endregion role
        $password=$this->addPassword('password','Heslo');
        $password
            ->setRequired('Zadejte požadované heslo')
            ->addRule(Form::MIN_LENGTH,'Heslo musí obsahovat minimálně 5 znaků.',5);
        $this->addPassword('password2','Heslo znovu:')
            ->addRule(Form::EQUAL,'Hesla se neshodují',$password);

        $this->addSubmit('ok','registrovat se')
            ->onClick[]=function(SubmitButton $button){

            //uložení uživatele
            $values=$this->getValues('array');
            $user = new User();
            $user->name=$values['name'];
            $user->email=$values['email'];
            //$user->role->roleId=$values['roleId']; <-------------------Nejde uložit
            $user->password=$this->passwords->hash($values['password']); //heslo samozřejmě rovnou hashujeme :)
            $this->usersFacade->saveUser($user);

            $this->onFinished('Uživatel byl uložen.');
        };
        $this->addSubmit('storno','zrušit')
            ->setValidationScope([])
            ->onClick[]=function(SubmitButton $button){
            $this->onCancel();
        };
    }
}