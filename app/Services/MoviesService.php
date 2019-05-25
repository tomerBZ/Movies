<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MoviesService
{
    /**
     * @var Client
     */
    private $client, $recommendationsResponse;
    public $errorDescription, $errorCode, $error = false;

    const API_KEY  = '3e1db4c693dbe18cccee70c027c934c3';
    const BASE_URL = 'https://api.themoviedb.org/3/movie/{movie_id}?api_key={api_key}';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $id
     * @param int $depth
     * @return array
     * @throws Exception
     */
    public function getRecommendations(int $id, int $depth = null)
    {
        return $this->fetchMovieRecommendations($id, $depth);
    }

    /**
     * @param int $id
     * @param $depth
     * @return $this|array
     * @throws Exception
     */
    private function fetchMovieRecommendations(int $id, $depth)
    {
        if ($depth === 0) return null;

        try {
            $recommendationsPromise        = $this->client->getAsync($this->buildApiUrl($id, true))->then(
                function ($response) {
                    return $response->getBody();
                }, function ($exception) {
                $this->error            = true;
                $this->errorCode        = $exception->getCode();
                $this->errorDescription = $exception->getMessage();
            }
            );
            $recommendationsArray          = [];
            $recommendationsRes            = $recommendationsPromise->wait();
            if ($this->error) throw new Exception($this->errorDescription, $this->errorCode);
            $this->recommendationsResponse = json_decode($recommendationsRes->getContents());

            foreach ($this->recommendationsResponse->results as $index => $movie) {
                if ($index === 3) break;
                $recommendationsArray[] = (object)[
                    'id'              => $movie->id,
                    'title'           => $movie->title,
                    'release-year'    => $this->getYear($movie->release_date),
                    'recommendations' => $depth === null ? null : $this->fetchMovieRecommendations($movie->id, $depth - 1)
                ];
            }

            return $recommendationsArray;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param string $movieId
     * @param bool $withRecommendations
     * @return mixed
     */
    private function buildApiUrl(string $movieId, bool $withRecommendations = false)
    {
        $moviePath = $withRecommendations ? $movieId . '/' . 'recommendations' : $movieId;

        return str_replace(['{movie_id}', '{api_key}'], [$moviePath, self::API_KEY], self::BASE_URL);
    }

    /**
     * @param string $date
     * @return mixed
     */
    private function getYear(string $date)
    {
        return explode('-', $date)[0];
    }
}