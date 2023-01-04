<?php

namespace App\AdminModule\Components\UserCreateForm;

/**
 * Interface UserCreateFormFactory
 * @package App\AdminModule\Components\UserCreateForm
 */
interface UserCreateFormFactory {

    public function create():UserCreateForm;

}