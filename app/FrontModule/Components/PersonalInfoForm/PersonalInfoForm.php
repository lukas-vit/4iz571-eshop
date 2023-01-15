<?php

namespace App\FrontModule\Components\PersonalInfoForm;

use App\Model\Entities\User;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class PersonalInfoForm
 * @package App\FrontModule\Components\PersonalInfoForm
 *
 * @method onFinished($message='')
 * @method onFailed($message='')
 * @method onCancel()
 */
class PersonalInfoForm extends Form {

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
     * PersonalInfoForm constructor
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param UsersFacade $usersFacade
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, UsersFacade $usersFacade){
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->usersFacade=$usersFacade;
        $this->createSubComponents();
    }

    private function createSubComponents(){
        $this->addHidden('userId');

        $this->addText('name','Jméno a příjmení:')
            ->setRequired('Zadejte své jméno')
            ->setHtmlAttribute('maxlength',40)
            ->addRule(Form::MAX_LENGTH,'Jméno je příliš dlouhé, může mít maximálně 40 znaků.',40);
        $this->addEmail('email','E-mail')
            ->setRequired('Zadejte platný email')
            ->addRule(Form::EMAIL, 'Zadejte platný email');
        $this->addSubmit('ok', 'uložit údaje')
            ->onClick[]=function (SubmitButton $button){
            $values = $this->getValues('array');

            try{
                $user = $this->usersFacade->getUser($values['userId']);
            }catch (\Exception $e){
                $this->onFailed('Zvolený uživatelský účet nebyl nalezen.');
                return;
            }

            $user->name = $values['name'];
            $user->email = $values['email'];
            $this->usersFacade->saveUser($user);

            $this->onFinished('Osobní údaje byly změněny');
        };
        $this->addSubmit('storno','zrušit')
            ->setValidationScope([])
            ->onClick[]=function(SubmitButton $button){
            $this->onCancel();
        };
    }

    /**
     * Metoda pro nastavení výchozích hodnot
     * @param $values
     * @param bool $erase
     * @return $this|PersonalInfoForm
     */
    public function setDefaults($values, bool $erase = false){
        if($values instanceof User){
            $values=[
                'userId'=>$values->userId,
                'name'=>$values->name,
                'email'=>$values->email,
            ];
        }
        parent::setDefaults($values, $erase);
        return $this;
    }

}