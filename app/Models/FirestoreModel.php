<?php

namespace App\Models;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreModel
{
    protected $collection;
    protected $firestore;

    public function __construct()
    {
        $this->firestore = app('firebase.firestore')->database();
    }

    public function all()
    {
        $documents = $this->firestore->collection($this->collection)->documents();
        $data = [];
        foreach ($documents as $document) {
            if ($document->exists()) {
                $retrievedId = array("id" => $document->id());  // Add the document ID to the data array
                $doc_data = $document->data();
                $doc_data = array_merge($retrievedId, $doc_data);
                $data[] = $doc_data;
            }
        }
        return $data;
    }

    public function find($id)
    {
        $document = $this->firestore->collection($this->collection)->document($id)->snapshot();
        if ($document->exists()) {
            $retrievedId = array("id" => $document->id());  // Add the document ID to the data array
            $data = $document->data();
            $data = array_merge($retrievedId, $data);
            return $data;
        }
        return null;
    }

    public function create(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('The id field is required.');
        }

        $id = $data['id'];
        unset($data['id']); // Remove id from the data array

        $document = $this->firestore->collection($this->collection)->document($id);
        $document->set($data);

        return $this->find($id);
    }

    public function update($id, array $data)
    {
        // Query the document by id
        $document = $this->find($id);
        if (!$document) {
            return false;
        }

        // Filter the allowed fields
        $allowedFields = array_flip($this->fillable);
        $filteredData = array_intersect_key($data, $allowedFields);

        // Merge current data with new data and unset the id label
        $updatedData = array_merge($document, $filteredData);
        unset($updatedData['id']);

        $this->firestore->collection($this->collection)->document($id)->set($updatedData, ['merge' => true]);
        return true;
    }

    public function delete($id)
    {
        // Query the document by id
        $document = $this->find($id);
        if (!$document) {
            return false;
        }

        $this->firestore->collection($this->collection)->document($id)->delete();
        return true;
    }

    public function getSubcollection($documentId)
    {
        $subcollection = $this->subcollection;
        $subcollectionRef = $this->firestore->collection($this->collection)->document($documentId)->collection($subcollection);
        $documents = $subcollectionRef->documents();
        $data = [];
        foreach ($documents as $document) {
            if ($document->exists()) {
                $retrievedId = array("id" => $document->id());  // Add the document ID to the data array
                $doc_data = $document->data();
                $doc_data = array_merge($retrievedId, $doc_data);
                $data[] = $doc_data;
            }
        }
        return $data;
    }
}
