<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;

class CategoryController extends Controller
{
 
    public function index(): Response
    {   
        return $this->render('category/index.html.twig');
    } 


    /**
     * @Route("/jsonData", name="jsonData")
     */
    public function categoryJson(): JsonResponse
    {

    
    }
}
