<?php

namespace App\Services;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use mysql_xdevapi\Exception;

class MoviesService
{
    /**
     * @var Client
     */
    private $client;

    const API_KEY = '3e1db4c693dbe18cccee70c027c934c3';
    const BASE_URL = 'https://api.themoviedb.org/3/movie/{movie_id}?api_key=${api_key}';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getRecommendations(int $id)
    {
        return $this->buildApiUrl($id);
    }

    private function fetchRecommendations(int $id)
    {
            $promise = $this->client->getAsync($this->buildApiUrl($id))->then(
                function ($response) {
                    return $response->getBody();
                }, function ($exception) {
                return $exception->getMessage();
            }
            );
        $response = $promise->wait();

        return $response;
    }

    private function buildRecommendations(array $recommendations)
    {
        // TODO Recursion
        return $recommendations;
    }

    private function buildApiUrl(string $movieId)
    {
        return str_replace(['{movie_id}', '{api_key}'], [$movieId, self::API_KEY], self::BASE_URL);
    }
}