<?php

namespace App\Observers;

use App\Services\ElasticsearchService;
use App\Models\Product;

class ProductObserver
{
    protected $elasticsearch;
    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }
    public function created(Product $product)
    {
        $this->elasticsearch->index([
            'index' => 'products',
            'id'    => $product->id,
            'body'  => $product->toArray(),
        ]);
    }
    public function updated(Product $product)
    {
        $this->elasticsearch->index([
            'index' => 'products',
            'id'    => $product->id,
            'body'  => $product->toArray(),
        ]);
    }
    public function deleted(Product $product)
    {
        $this->elasticsearch->delete([
            'index' => 'products',
            'id'    => $product->id,
        ]);
    }
}