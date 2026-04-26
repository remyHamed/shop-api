<?php

namespace Tests\Unit\Domain\Service;

use App\Domain\Entity\Product;
use App\Domain\Entity\Sale;
use App\Domain\Entity\StockMovement;
use App\Domain\Service\ProductService;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\Interfaces\SaleRepositoryInterface;
use App\Repository\Interfaces\StockMovementRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockObject       $productRepo;
    private StockMovementRepositoryInterface&MockObject $movementRepo;
    private SaleRepositoryInterface&MockObject          $saleRepo;
    private ProductService                              $service;

    protected function setUp(): void
    {
        $this->productRepo  = $this->createMock(ProductRepositoryInterface::class);
        $this->movementRepo = $this->createMock(StockMovementRepositoryInterface::class);
        $this->saleRepo     = $this->createMock(SaleRepositoryInterface::class);

        $this->service = new ProductService(
            $this->productRepo,
            $this->movementRepo,
            $this->saleRepo
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

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

    // ═════════════════════════ createProduct ═════════════════════════════════

    public function testCreateProductCallsSaveAndReturnsProduct(): void
    {
        $this->productRepo
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Product::class));

        $product = $this->service->createProduct([
            'store_id'    => 1,
            'name'        => 'Thé vert',
            'description' => 'Bio',
            'price'       => 8.50,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('Thé vert', $product->getName());
        $this->assertSame(8.50, $product->getPrice());
        $this->assertSame(0, $product->getStock()); // stock initial = 0
    }

    public function testCreateProductTrimsName(): void
    {
        $this->productRepo->expects($this->once())->method('save');

        $product = $this->service->createProduct([
            'store_id' => 1,
            'name'     => '  Café  ',
            'price'    => 5.0,
        ]);

        $this->assertSame('Café', $product->getName());
    }

    public function testCreateProductMissingNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/name/");

        $this->service->createProduct(['store_id' => 1, 'price' => 5.0]);
    }

    public function testCreateProductMissingPriceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/price/");

        $this->service->createProduct(['store_id' => 1, 'name' => 'Test']);
    }

    public function testCreateProductMissingStoreIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/store_id/");

        $this->service->createProduct(['name' => 'Test', 'price' => 5.0]);
    }

    public function testCreateProductNegativePriceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createProduct(['store_id' => 1, 'name' => 'Test', 'price' => -1.0]);
    }

    // ═════════════════════════ getProductsByStore ═════════════════════════════

    public function testGetProductsByStoreDelegatesToRepository(): void
    {
        $products = [$this->makeProduct(1), $this->makeProduct(2)];

        $this->productRepo
            ->expects($this->once())
            ->method('findByStoreId')
            ->with(42)
            ->willReturn($products);

        $result = $this->service->getProductsByStore(42);
        $this->assertCount(2, $result);
    }

    // ═════════════════════════ getProductById ════════════════════════════════

    public function testGetProductByIdReturnsProductWhenFound(): void
    {
        $product = $this->makeProduct(7);
        $this->productRepo->method('findById')->with(7)->willReturn($product);

        $result = $this->service->getProductById(7);
        $this->assertSame($product, $result);
    }

    public function testGetProductByIdThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->productRepo->method('findById')->willReturn(null);

        $this->service->getProductById(999);
    }

    // ═════════════════════════ addStock ══════════════════════════════════════

    public function testAddStockIncreasesProductStockAndSavesMovement(): void
    {
        $product = $this->makeProduct(1, 10);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->productRepo->expects($this->once())->method('update')->with($product);
        $this->movementRepo->expects($this->once())->method('save')
            ->with($this->callback(function (StockMovement $m) {
                return $m->getType() === StockMovement::TYPE_IN
                    && $m->getQuantity() === 5;
            }));

        $updated = $this->service->addStock(1, 5, 'Livraison fournisseur');

        $this->assertSame(15, $updated->getStock());
    }

    public function testAddStockWithCustomReason(): void
    {
        $product = $this->makeProduct(1, 0);
        $this->productRepo->method('findById')->willReturn($product);
        $this->productRepo->expects($this->once())->method('update');

        $this->movementRepo->expects($this->once())->method('save')
            ->with($this->callback(fn(StockMovement $m) => $m->getReason() === 'Retour client'));

        $this->service->addStock(1, 3, 'Retour client');
    }

    public function testAddStockWithZeroQuantityThrowsException(): void
    {
        $product = $this->makeProduct(1, 10);
        $this->productRepo->method('findById')->willReturn($product);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->addStock(1, 0);
    }

    public function testAddStockProductNotFoundThrowsException(): void
    {
        $this->productRepo->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->addStock(999, 5);
    }

    // ═════════════════════════ removeStock ═══════════════════════════════════

    public function testRemoveStockDecreasesStockAndSavesMovement(): void
    {
        $product = $this->makeProduct(1, 20);
        $this->productRepo->method('findById')->willReturn($product);

        $this->productRepo->expects($this->once())->method('update');
        $this->movementRepo->expects($this->once())->method('save')
            ->with($this->callback(function (StockMovement $m) {
                return $m->getType() === StockMovement::TYPE_OUT
                    && $m->getQuantity() === 7;
            }));

        $updated = $this->service->removeStock(1, 7, 'Casse');
        $this->assertSame(13, $updated->getStock());
    }

    public function testRemoveStockBeyondAvailableThrowsException(): void
    {
        $product = $this->makeProduct(1, 2);
        $this->productRepo->method('findById')->willReturn($product);

        $this->productRepo->expects($this->never())->method('update');
        $this->movementRepo->expects($this->never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->service->removeStock(1, 10);
    }

    // ═════════════════════════ sellProduct ═══════════════════════════════════

    public function testSellProductDecreasesStockSavesMovementAndSale(): void
    {
        $product = $this->makeProduct(1, 10);
        $this->productRepo->method('findById')->willReturn($product);

        $this->productRepo->expects($this->once())->method('update');

        $this->movementRepo->expects($this->once())->method('save')
            ->with($this->callback(fn(StockMovement $m) =>
                $m->getType() === StockMovement::TYPE_OUT
                && $m->getReason() === 'Vente'
                && $m->getQuantity() === 3
            ));

        $this->saleRepo->expects($this->once())->method('save')
            ->with($this->isInstanceOf(Sale::class));

        $sale = $this->service->sellProduct(1, 3);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertSame(3, $sale->getQuantity());
        $this->assertSame(20.0, $sale->getUnitPrice());
        $this->assertSame(60.0, $sale->getTotalAmount());
        $this->assertSame(7, $product->getStock());
    }

    public function testSellProductComputesTotalAmountCorrectly(): void
    {
        $product = new Product(1, 1, 'Item', '', 7.50, 100, new \DateTimeImmutable());
        $this->productRepo->method('findById')->willReturn($product);
        $this->productRepo->method('update');
        $this->movementRepo->method('save');
        $this->saleRepo->method('save');

        $sale = $this->service->sellProduct(1, 4);
        $this->assertEqualsWithDelta(30.0, $sale->getTotalAmount(), 0.001);
    }

    public function testSellProductWithInsufficientStockThrowsException(): void
    {
        $product = $this->makeProduct(1, 2);
        $this->productRepo->method('findById')->willReturn($product);

        $this->saleRepo->expects($this->never())->method('save');
        $this->movementRepo->expects($this->never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->service->sellProduct(1, 5);
    }

    public function testSellProductNotFoundThrowsException(): void
    {
        $this->productRepo->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->sellProduct(999, 1);
    }

    // ═════════════════════════ getSalesByStore ════════════════════════════════

    public function testGetSalesByStoreDelegatesToRepository(): void
    {
        $sales = [
            new Sale(1, 1, 42, 2, 10.0, 20.0, new \DateTimeImmutable()),
        ];
        $this->saleRepo->expects($this->once())
            ->method('findByStoreId')
            ->with(42)
            ->willReturn($sales);

        $result = $this->service->getSalesByStore(42);
        $this->assertCount(1, $result);
    }

    // ═════════════════════════ getTotalRevenueByStore ════════════════════════

    public function testGetTotalRevenueByStoreDelegatesToRepository(): void
    {
        $this->saleRepo->expects($this->once())
            ->method('getTotalRevenueByStore')
            ->with(42)
            ->willReturn(350.75);

        $result = $this->service->getTotalRevenueByStore(42);
        $this->assertSame(350.75, $result);
    }

    // ═════════════════════════ getStockMovements ══════════════════════════════

    public function testGetStockMovementsDelegatesToRepository(): void
    {
        $movements = [
            new StockMovement(1, 5, 42, StockMovement::TYPE_IN, 10, 'Livraison', new \DateTimeImmutable()),
        ];
        $this->movementRepo->expects($this->once())
            ->method('findByProductId')
            ->with(5)
            ->willReturn($movements);

        $result = $this->service->getStockMovements(5);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(StockMovement::class, $result[0]);
    }

    // ═════════════════════════ Scénarios intégrés ════════════════════════════

    public function testFullCycleAddThenSell(): void
    {
        // Produit avec stock = 0
        $product = $this->makeProduct(1, 0);
        $this->productRepo->method('findById')->willReturn($product);
        $this->productRepo->method('update');
        $this->movementRepo->method('save');
        $this->saleRepo->method('save');

        // Réapprovisionnement
        $this->service->addStock(1, 50);
        $this->assertSame(50, $product->getStock());

        // Vente
        $sale = $this->service->sellProduct(1, 12);
        $this->assertSame(38, $product->getStock());
        $this->assertSame(12, $sale->getQuantity());
    }

    public function testSellEntireStockLeavesZero(): void
    {
        $product = $this->makeProduct(1, 5);
        $this->productRepo->method('findById')->willReturn($product);
        $this->productRepo->method('update');
        $this->movementRepo->method('save');
        $this->saleRepo->method('save');

        $this->service->sellProduct(1, 5);
        $this->assertSame(0, $product->getStock());
    }
}