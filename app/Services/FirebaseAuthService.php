<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseAuthService
{

    protected ?FirestoreClient $firestore = null;

    protected function getFirestore(): FirestoreClient
    {
        if ($this->firestore === null) {
            $this->firestore = new FirestoreClient([
                'keyFilePath' => storage_path(env('FIREBASE_CREDENTIALS_PATH')),
                'projectId'   => env('FIREBASE_PROJECT_ID'),
            ]);
        }

        return $this->firestore;
    }

    public function getAll(string $collection): array
    {
        $documents = $this->getFirestore()->collection($collection)->documents();
        $results = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $results[] = array_merge(['id' => $document->id()], $document->data());
            }
        }

        return $results;
    }

    public function getById(string $collection, string $id): ?array
    {
        $document = $this->getFirestore()->collection($collection)->document($id)->snapshot();

        return $document->exists() ? array_merge(['id' => $document->id()], $document->data()) : null;
    }

    public function create(string $collection, array $data): ?array
    {
        $newDoc = $this->getFirestore()->collection($collection)->add($data);
        $document = $newDoc->snapshot();
        return $document->exists() ? array_merge(['id' => $document->id()], $document->data()) : null;
    }

    public function update(string $collection, string $id, array $data): bool
    {
        $this->getFirestore()->collection($collection)->document($id)->set($data, ['merge' => true]);
        return true;
    }

    public function delete(string $collection, string $id): bool
    {
        $this->getFirestore()->collection($collection)->document($id)->delete();
        return true;
    }

}
