<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\RedisSearchService;
use Illuminate\Http\Request;

class RedisController extends Controller
{
    public function index(Request $request)
    {
        $originalData = Property::paginate(100);
        return view(
            'index',
            [
                'originalData' => $originalData,
                'next' => $originalData->nextPageUrl(),
                'data' => []
            ]
        )->fragmentsIf($request->hasHeader('HX-Request'), ['original-data']);
    }

    public function search(Request $request)
    {
        $data = RedisSearchService::make()->search(
            indexName: 'properties-idx',
            query: $request->q,
            highlights: ['title', 'address', 'location', 'furnish', 'price'],
            returnFields: $request->returnFields,
            limitOffset: $request->offset,
            limitSize: $request->limit
        );

        return view(
            'index',
            [
                'originalData' => [],
                'total'=> $data->getTotalCount(),
                'data' => collect($data->current())->map(fn($data) => $data->getFields())
            ]
        )->fragment('search-result');

    }

    public function seeding(Request $request)
    {
        // $columns = $schema->listTableColumns('properties');
        // RedisSearchService::make()->buildIndex('properties-idx', ['properties:detail:']);
        RedisSearchService::make()->seedingData($request->total);
 
        return [
            "ok" => 'ok',
            // 'result' => RedisSearchService::make()->search(
            //     indexName: $request->indexName,
            //     query: $request->q,
            //     highlights: $request->highlights,
            //     returnFields: $request->returnFields,
            //     limitOffset: $request->offset,
            //     limitSize: $request->limit
            // )
        ];
    }
}