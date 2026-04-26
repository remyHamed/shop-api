<?php
namespace App\Domain\Service;

use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\Interfaces\SaleRepositoryInterface;
use App\Domain\Entity\Product;
use App\Domain\Entity\Sale;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private SaleRepositoryInterface $saleRepository
    ) {}

    public function createProduct(array $data): Product
    {
        $product = new Product(
            null,
            (int)$data['store_id'],
            $data['name'],
            $data['description'] ?? '',
            (float)$data['price'],
            (int)($data['stock'] ?? 0),
            new \DateTimeImmutable()
        );

        $this->productRepository->save($product);
        return $product;
    }

    public function getProductsByStore(int $storeId): array
    {
        return $this->productRepository->findByStoreId($storeId);
    }

    public function sellProduct(int $productId, int $quantity): Sale
    {
        $product = $this->productRepository->findById($productId);
        
        if (!$product || $product->getStock() < $quantity) {
            throw new \Exception("Stock insuffisant");
        }

        $product->removeStock($quantity);
        $this->productRepository->update($product);

        $sale = new Sale(
            null,
            $productId,
            $product->getStoreId(),
            $quantity,
            $product->getPrice(),
            $product->getPrice() * $quantity,
            new \DateTimeImmutable()
        );
        
        $this->saleRepository->save($sale);
        return $sale;
    }

    public function deleteProduct(int $id): void
    {
        $this->productRepository->delete($id);
    }
}