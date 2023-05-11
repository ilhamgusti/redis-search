<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\RedisSearchService;
use Illuminate\Http\Request;
use MacFJA\RediSearch\Query\Builder\NumericFacet;

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
        $minPrice = $request->minPrice;
        $maxPrice = $request->maxPrice;
        $q = $request->q;
        $location = $request->location;
        $furnish = $request->furnish;
        $bathroom = $request->bathroom;
        $bedroom = $request->bedroom;
        $landAreaMin = $request->landAreaMin;
        $landAreaMax = $request->landAreaMax;
        
        $queryBuilder = new \MacFJA\RediSearch\Query\Builder();

        if ($minPrice && $maxPrice){
            $queryBuilder->addElement(new NumericFacet('price', $minPrice, $maxPrice));
        }

        if($bathroom){
            $queryBuilder->addElement(NumericFacet::greaterThanOrEquals('bathroom', $bathroom));
        }

        if($bedroom){
            $queryBuilder->addElement(NumericFacet::greaterThanOrEquals('bedroom', $bedroom));
        }

        if ($landAreaMin && $landAreaMax){
            $queryBuilder->addElement(new NumericFacet('landArea', $landAreaMin, $landAreaMax));
        }

        if($q){
            $queryBuilder->addString($q);
        }

        if($location){
            if(is_array($location)){
                $queryBuilder->addTagFacet('location',  ...$location);
            }else{
                $queryBuilder->addTagFacet('location', $location);
            }
        }

        if($furnish){
            $queryBuilder->addTagFacet('furnish', $furnish);
        }

        $sortByFields = $request->sortBy;

        if(empty($sortByFields)){
            $sortByFields = [];
        }

        $query = $queryBuilder->render();

        $data = RedisSearchService::make()->search(
            indexName: 'properties-idx',
            query: $query,
            highlights: ['title', 'address', 'location', 'furnish', 'price'],
            returnFields: $request->returnFields,
            limitOffset: $request->offset,
            limitSize: $request->limit,
            sortByFields: [
                ...$sortByFields
            ]
        );

        // $content = view(
        //     'index',
        //     [
        //         'originalData' => [],
        //         'total'=> $data->getTotalCount(),
        //         'data' => collect($data->current())->map(fn($data) => $data->getFields())
        //     ]
        // )->fragment('search-result');

        // return response($content)->header('HX-Replace-Url',"/?q={$request->q}");

        return response()->json([
                    'originalData' => [],
                    'total'=> $data->getTotalCount(),
                    'data' => collect($data->current())->map(fn($data) => $data->getFields())
        ]);

    }

    public function seeding(Request $request)
    {
        RedisSearchService::make()->seedingData($request->total);
 
        return [
            "ok" => 'ok',
        ];
    }
}