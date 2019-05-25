<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Response;
use App\Services\MoviesService;
use App\Http\Controllers\Controller;

class RecommendationsController extends Controller
{
    /**
     * @var MoviesService
     */
    private $moviesService;

    public function __construct(MoviesService $moviesService)
    {
        $this->moviesService = $moviesService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json('ok');
    }

    /**
     * @param int $id
     * @param int $depth
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id, int $depth = null)
    {
        try {
            $recommendations = $this->moviesService->getRecommendations($id, $depth);
        } catch (Exception $exception) {
            return response()->json(['data' => $exception->getMessage() ?: 'Unexpected error'], $exception->getCode() ?: 500);
        }

        return response()->json($recommendations);
    }
}

