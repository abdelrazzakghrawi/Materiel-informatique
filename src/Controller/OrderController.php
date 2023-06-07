<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class OrderController extends AbstractController
{
    private $orderRepository;
    private $entityManager;

    public function __construct(
        OrderRepository $orderRepository, 
        ManagerRegistry $doctrine
    ) {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/orders', name: 'orders_list')]
     /**
     * @IsGranted("ROLE_ADMIN",statusCode=404, message="You are not allowed to")
     */
    public function index(): Response
    {
        $orders = $this->orderRepository->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders
        ]);
    } 
    
    #[Route('/user/orders', name: 'app_order')]
    public function userOrders(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('order/user.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    
    #[Route('/store/order/{product}', name: 'order_store')]
    public function store(Product $product): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $orderExists = $this->orderRepository->findOneBy([
            'user' => $this->getUser(),
            'pname' => $product->getName()
        ]);

        if ($orderExists) {
            $this->addFlash(
                'warning', 
                'You have already ordered this product'
            );
            
            return $this->redirectToRoute('app_order');
        }

        $order = new Order();
        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('processing...');
        $order->setUser($this->getUser());

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->addFlash(
            'success', 
            'Your order was saved'
        );

        return $this->redirectToRoute('user_order_list');
    }

    #[Route('/update/order/{order}/{status}', name: 'order_status_update')]
     /**
     * @IsGranted("ROLE_ADMIN",statusCode=404, message="You are not allowed to")
     */
    public function updateOrderStatus(Order $order, $status): Response
    {
        $order->setStatus($status);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        
        $this->addFlash(
            'success', 
            'Your order status was updated'
        );

        return $this->redirectToRoute('orders_list');
    }

    #[Route('/update/order/{order}', name: 'order_delete')]
     /**
     * @IsGranted("ROLE_ADMIN",statusCode=404, message="You are not allowed to")
     */
    public function deleteOrder(Order $order): Response
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        
        $this->addFlash(
            'success', 
            'Your order was deleted'
        );

        return $this->redirectToRoute('orders_list');
    }
}

class MailerController extends AbstractController
{
    public function __construct(private MailerInterface $mailer)
    {

    }
    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('a.razzak2002@gmail.com')
            ->to('get_order')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        return new Response('Email sent');
    }
}

