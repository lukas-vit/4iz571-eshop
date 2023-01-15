<?php

namespace App\FrontModule\Components\PersonalInfoForm;

/**
 * Interface PersonalInfoFormFactory
 * @package App\FrontModule\Components\PersonalInfoForm
 */
interface PersonalInfoFormFactory{

    public function create():PersonalInfoForm;

}