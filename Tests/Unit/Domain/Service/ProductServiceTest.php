<?php
namespace Tests\Unit\Domain\Service;

use App\Domain\Entity\Product;
use App\Domain\Entity\Sale;
use App\Domain\Service\ProductService;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\Interfaces\SaleRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepo;
    private SaleRepositoryInterface&MockObject    $saleRepo;
    private ProductService                       $service;

    protected function setUp(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->saleRepo    = $this->createMock(SaleRepositoryInterface::class);

        $this->service = new ProductService(
            $this->productRepo,
            $this->saleRepo
        );
    }

    private function makeProduct(int $id = 1, int $stock = 10): Product
    {
        return new Product(
            $id,
            42,
            'Produit Test',
            'Description',
            20.0,
            $stock,
            new \DateTimeImmutable()
        );
    }

    public function testCreateProductSavesAndReturnsProduct(): void
    {
        $this->productRepo->expects($this->once())->method('save');

        $product = $this->service->createProduct([
            'store_id' => 1,
            'name'     => 'Thé vert',
            'price'    => 8.50
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('Thé vert', $product->getName());
        $this->assertSame(0, $product->getStock());
    }

    public function testSellProductDecreasesStockAndSavesSale(): void
    {
        $product = $this->makeProduct(1, 10);
        $this->productRepo->method('findById')->with(1)->willReturn($product);
        

        $this->productRepo->expects($this->once())->method('update');

        $this->saleRepo->expects($this->once())->method('save');

        $sale = $this->service->sellProduct(1, 3);

        $this->assertSame(7, $product->getStock());
        $this->assertSame(60.0, $sale->getTotalAmount());
    }

    public function testSellProductInsufficientStockThrowsException(): void
    {
        $product = $this->makeProduct(1, 2);
        $this->productRepo->method('findById')->willReturn($product);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Stock insuffisant");

        $this->service->sellProduct(1, 5);
    }

    public function testDeleteProductDelegatesToRepository(): void
    {
        $this->productRepo->expects($this->once())
            ->method('delete')
            ->with(123);

        $this->service->deleteProduct(123);
    }
}