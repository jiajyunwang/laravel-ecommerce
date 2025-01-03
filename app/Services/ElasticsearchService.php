<?php

namespace App\Services;

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
        return $this->client->index($params);  // 這個是用來索引資料
    }

    public function delete($params)
    {
        return $this->client->delete($params);  // 這個是用來刪除資料
    }

    public function search($index, $query)
    {
        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'match' => [
                        'title' => [
                            'query' => $query,
                            'fuzziness' => 'AUTO'
                        ]
                    ]
                ]
            ]
        ];
        return $this->client->search($params);
    }
}