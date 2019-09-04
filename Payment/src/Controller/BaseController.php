<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * Overridden to type-hint *our* User class
     *
     * @return User
     */
    public function getUser()
    {
        return parent::getUser();
    }
}
