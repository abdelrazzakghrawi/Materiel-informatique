<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
class HomeController extends AbstractController
{
    private $productRepository;
    private $entityManager;
    private $categoryRepository;

    public function __construct(ProductRepository $productRepository, 
    CategoryRepository $categoryRepository,
    ManagerRegistry $doctrine)
    {
        $this->productRepository = $productRepository;
        $this ->categoryRepository = $categoryRepository;
        $this->entityManager = $doctrine->getManager();
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
    #[Route('/product/{category}', name: 'product_category')]
    public function show(Category $category): Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('home/index.html.twig', [
            
            'products' => $category->getProducts(),
            'categories' => $categories
            
        ]);
    }

}
