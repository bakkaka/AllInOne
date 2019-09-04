<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {
        $products = $this->getDoctrine()
            ->getRepository('App:Product')
            ->findAll();

        return $this->render('default/homepage.html.twig', array(
            'products' => $products,
        ));
    }
}
