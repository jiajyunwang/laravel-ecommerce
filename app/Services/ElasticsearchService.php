<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    protected $client;
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('services.elasticsearch.hosts'))
            ->build();
    }
    public function index($params)
    {
        return $this->client->index($params); 
    }

    public function delete($params)
    {
        return $this->client->delete($params); 
    }

    public function search($index, $query, $page, $perPage, $sortBy, $sortOrder)
    {
        $params = [
            'index' => $index,
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'sort' => [
                    $sortBy => [
                        'order' => $sortOrder
                    ]
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'title' => [
                                        'query' => $query,
                                        'fuzziness' => 'AUTO'
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'status' => 'active'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $this->client->search($params);
    }

    public function searchProducts($request, $perPage){
        $term = $request->query('search');
        $page = $request->query('page', 1);
        $sortBy = $request->query('sortBy', '_score');
        $sortOrder = $request->query('sortOrder', 'desc');

        $response = $this->search('products', $term, $page, $perPage, $sortBy, $sortOrder);
        $total = $response['hits']['total']['value'];
        $products = $response['hits']['hits'];
        foreach($products as &$product){
            $product = (object) $product['_source'];
        }

        return new LengthAwarePaginator($products, $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
    }
}