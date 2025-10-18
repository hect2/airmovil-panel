<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseAuthService
{

    protected FirestoreClient $firestore;

    public function __construct()
    {
        $this->firestore = new FirestoreClient([
            'keyFilePath' => storage_path('app/firebase/angular-tienda-dc239-firebase-adminsdk-gok7t-9dba862937.json'),
            'projectId'   => env('FIREBASE_PROJECT_ID'),
        ]);
    }

    public function getAll(string $collection): array
    {
        $documents = $this->firestore->collection($collection)->documents();
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
        $document = $this->firestore->collection($collection)->document($id)->snapshot();

        return $document->exists() ? array_merge(['id' => $document->id()], $document->data()) : null;
    }

    public function create(string $collection, array $data): ?array
    {
        $newDoc = $this->firestore->collection($collection)->add($data);
        $document = $newDoc->snapshot();
        return $document->exists() ? array_merge(['id' => $document->id()], $document->data()) : null;
    }

    public function update(string $collection, string $id, array $data): bool
    {
        $this->firestore->collection($collection)->document($id)->set($data, ['merge' => true]);
        return true;
    }

    public function delete(string $collection, string $id): bool
    {
        $this->firestore->collection($collection)->document($id)->delete();
        return true;
    }

}
