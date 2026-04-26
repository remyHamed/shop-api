<?php
namespace App\Http\Controller;

use App\Domain\Service\StoreService;

class StoreController {
    public function __construct(private StoreService $storeService) {}

    public function list(): array {
        $stores = $this->storeService->listAllStores();
        return array_map(fn($s) => $s->toArray(), $stores);
    }

    public function create(): array {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $store = $this->storeService->createNewStore($data);
            http_response_code(201);
            return [
                'message' => 'Magasin créé avec succès',
                'store' => $store->toArray()
            ];
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => 'Erreur lors de la création'];
        }
    }
}