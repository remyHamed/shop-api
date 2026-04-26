<?php
namespace App\Http\Controller;

use App\Domain\Service\ProductService;

class ProductController
{
    public function __construct(private ProductService $productService) {}

    public function listByStore(array $vars): array
    {
        return array_map(fn($p) => $p->toArray(), $this->productService->getProductsByStore((int)$vars['storeId']));
    }

    public function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $product = $this->productService->createProduct($data);
        http_response_code(201);
        return $product->toArray();
    }

    public function sell(array $vars): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $sale = $this->productService->sellProduct((int)$vars['productId'], (int)$data['quantity']);
            return $sale->toArray();
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function update(array $vars): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $product = $this->productService->updateProduct((int)$vars['id'], $data);
            return $product->toArray();
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function delete(array $vars): array
    {
        try {
            $this->productService->deleteProduct((int)$vars['id']);
            http_response_code(204); // Succès sans contenu
            return [];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
}