<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\StripeClient;
use App\Store\ShoppingCart;
use App\Subscription\SubscriptionHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends BaseController
{
    /**
     * @Route("/cart/product/{slug}", name="order_add_product_to_cart")
     * @Method("POST")
     */
    public function addProductToCartAction(ShoppingCart $shoppingCart, Product $product)
    {
        $shoppingCart
            ->addProduct($product);

        $this->addFlash('success', 'Product added!');

        return $this->redirectToRoute('order_checkout');
    }

    /**
     * @Route("/cart/subscription/{planId}", name="order_add_subscription_to_cart")
     */
    public function addSubscriptionToCartAction(ShoppingCart $shoppingCart, SubscriptionHelper $subscriptionHelper, $planId)
    {
        //$this->get('subscription_helper');
        $plan = $subscriptionHelper->findPlan($planId);

        if (!$plan) {
            throw $this->createNotFoundException('Bad plan id!');
        }

        $shoppingCart->addSubscription($planId);

        return $this->redirectToRoute('order_checkout');
    }

    /**
     * @Route("/checkout", name="order_checkout", schemes={"%secure_channel%"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function checkoutAction(Request $request,ShoppingCart $shoppingCart)
    {
        $products = $shoppingCart->getProducts();

        $error = false;
        if ($request->isMethod('POST')) {
            $token = $request->request->get('stripeToken');

           // try {
            //    $this->chargeCustomer($token);
            //} catch (\Stripe\Error\Card $e) {
            //    $error = 'There was a problem charging your card: '.$e->getMessage();
            //}

            if (!$error) {
                $shoppingCart()->emptyCart();
                $this->addFlash('success', 'Order Complete! Yay!');
                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('order/checkout.html.twig', array(
            'products' => $products,
            'cart' => $shoppingCart,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'error' => $error,
        ));

    }

    /**
     * @param $token
     * @throws \Stripe\Error\Card
     */
    private function chargeCustomer($token, StripeClient $stripeClient)
    {
        $stripeClient = $this->get('stripe_client');
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getStripeCustomerId()) {
            $stripeClient->createCustomer($user, $token);
        } else {
            $stripeClient->updateCustomerCard($user, $token);
        }

        $cart = $shoppingCart();

        foreach ($cart->getProducts() as $product) {
            $stripeClient->createInvoiceItem(
                $product->getPrice() * 100,
                $user,
                $product->getName()
            );
        }

        if ($cart->getSubscriptionPlan()) {
            // a subscription creates an invoice
            $stripeClient->createSubscription(
                $user,
                $cart->getSubscriptionPlan()
            );
        } else {
            // charge the invoice!
            $stripeClient->createInvoice($user, true);
        }
    }
}

