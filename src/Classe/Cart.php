<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart {
    private $stack;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $stack) {
        $this->stack = $stack;
        $this->entityManager = $entityManager;
    }

    public function add($id) {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
    }

    public function get() {
        $methodGet = $this->stack->getSession();
        return $methodGet->get('cart');
    }

    public function getFull() {

        $cartComplete = [];

        foreach ($this->get() as $id => $quantity) {
            $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

            if (!$product_object) {
                $this->delete($id);
                continue;
            }

            $cartComplete[] = [
                'product' => $product_object,
                'quantity' => $quantity
            ];
        }
        return $cartComplete;
    }

    public function remove() {
        $methodRemove = $this->stack->getSession();
        return $methodRemove->remove('cart');
    }

    public function delete($id) {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        unset($cart[$id]);

        return $session->set('cart', $cart);
    }

    public function decrease($id) {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        return $session->set('cart', $cart);
    }
}