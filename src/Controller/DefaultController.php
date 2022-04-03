<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="anonyme_index")
     */
    public function index(): Response
    {
        return $this->render('Anonyme/index.html.twig');
    }

}
