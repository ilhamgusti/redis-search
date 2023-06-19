<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\RedisSearchService;
use Illuminate\Http\Request;
use DB;
use MacFJA\RediSearch\Redis\Response\ResponseItem;
use Str;
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

    public function search(Request $request){
        $query = $this->queryBuilderRender($request);

        $sortByFields = $request->sortBy;

        if(empty($sortByFields)){
            $sortByFields = [];
        }

        $data = RedisSearchService::make()->search(
            indexName: 'properties-idx',
            query: $query,
            highlights: ['title', 'address', 'location', 'furnish', 'price','description'],
            // returnFields: ['title', 'price', 'location', 'bathroom', 'bedroom', 'landArea', 'buildingSize'],
            limitOffset: $request->offset,
            limitSize: $request->limit,
            sortByFields: [
                ...$sortByFields
            ]
        );

         return response()->json([
                    'originalData' => [],
                    'total'=> $data->getTotalCount(),
                    'data' => collect($data->current())->map(fn($data) => $data->getFields()),
        ]);

    }
    
    public function suggestion(Request $request)
    {
        $startTime = microtime(true); //get time in micro seconds(1 millionth)

        $query = $this->queryBuilderRender($request);

        
        $sortByFields = $request->sortBy;

        if(empty($sortByFields)){
            $sortByFields = [];
        }

        $data = RedisSearchService::make()->search(
            indexName: 'properties-idx',
            query: $query,
            // highlights: ['title', 'address', 'location', 'furnish', 'price','description'],
            returnFields: ['title', 'price', 'location'],
            limitOffset: $request->offset,
            limitSize: $request->limit,
            // max: $request->max,
            sortByFields: [
                ...$sortByFields
            ]
        );

        $location = RedisSearchService::make()->search(
            indexName: 'location-idx',
            query: $query,
            highlights: ['name'],
            limitOffset: $request->offset,
            limitSize: $request->limit,
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

        $endTime = microtime(true);
        
        // echo "milliseconds to execute:". ($endTime-$startTime)*1000;
        dd($data);
        
        return response()->json([
                    'originalData' => [],
                    'cost' =>[
                        'PHP' => ($endTime-$startTime)*1000 . ' ms',
                        'redis' => '',
                    ],
                    'total'=> $data->getTotalCount(),
                    'data' => collect($data->current())->map(function(ResponseItem $item) use ($data) {
                        return [
                        'data'=>$item->getFields(),
                        ];
                }),
                    'location' => collect($location->current())->map(fn(ResponseItem $data) => $data->getFields())
        ]);

    }

    public function seeding(Request $request)
    {
        RedisSearchService::make()->seedingData($request->total);
 
        return [
            "ok" => 'ok',
        ];
    }

    public function wilayah(){
            // $kelurahan = DB::table('reg_provinces')
            // ->selectRaw("reg_provinces.name AS provinsi, reg_regencies.name AS kabupaten_kota, reg_districts.name AS kecamatan, reg_villages.name AS kelurahan, reg_villages.id AS kelid")
            // ->leftJoin('reg_regencies', 'reg_regencies.province_id', '=', 'reg_provinces.id')
            // ->leftJoin('reg_districts', 'reg_districts.regency_id', '=', 'reg_regencies.id')
            // ->leftJoin('reg_villages', 'reg_villages.district_id', '=', 'reg_districts.id')
            // ->get()->map(function ($data)
            // {
            //     return [
            //         'id' => Str::of("kel-{$data->kelid}")->squish(),
            //         'name' => $data->kelurahan,
            //         'type' => 'Area',
            //         'refName' => "{$data->kecamatan}," . Str::of($data->kabupaten_kota)->lower()->studly()->ucsplit()->join(" ") . ",". Str::of($data->provinsi)->lower()->studly()->ucsplit()->join(" ")
            //     ];
            // });

            $kecamatan = DB::table('reg_provinces')
            ->selectRaw("reg_provinces.name AS provinsi, reg_regencies.name AS kabupaten_kota, reg_districts.name AS kecamatan, reg_districts.id AS kecid")
            ->leftJoin('reg_regencies', 'reg_regencies.province_id', '=', 'reg_provinces.id')
            ->leftJoin('reg_districts', 'reg_districts.regency_id', '=', 'reg_regencies.id')
            ->get()->map(function ($data)
            {
                return [
                    'id' => Str::of("kec-{$data->kecid}")->squish(),
                    'name' => $data->kecamatan,
                    'type' => 'Kecamatan',
                    'refName' => Str::of($data->kabupaten_kota)->lower()->studly()->ucsplit()->join(" ") . ",". Str::of($data->provinsi)->lower()->studly()->ucsplit()->join(" ")
                ];
            });


            $kabupaten = DB::table('reg_regencies')
            ->selectRaw("reg_provinces.name AS provinsi, reg_regencies.name AS kabupaten_kota, reg_regencies.id AS kabid")
            ->leftJoin('reg_provinces', 'reg_provinces.id', '=', 'reg_regencies.province_id')
            ->get()->map(function ($data)
            {
                return [
                    'id' => Str::of("kab-{$data->kabid}")->squish(),
                    'name' => $data->kabupaten_kota,
                    'type' => 'Kabupaten / Kota',
                    'refName' => Str::of($data->provinsi)->lower()->studly()->ucsplit()->join(" ")
                ];
            });

            $provinsi = DB::table('reg_provinces')->get()->map(function ($data)
            {
                return [
                    'id' => 'prov-' . Str::of($data->id),
                    'name' => Str::of($data->name)->lower()->studly()->ucsplit()->join(" "),
                    'type' => 'Provinsi',
                    'refName' => 'Indonesia'
                ];
            });

        return [
            // 'kelurahan' => $kelurahan,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
            'provinsi'  => $provinsi,

        ];
    }

    private function queryBuilderRender(Request $request)
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
        $buildingSizeMin = $request->buildingSizeMin;
        $buildingSizeMax = $request->buildingSizeMax;
        $type = $request->type;
        $category = $request->category;
        $certificate = $request->certificate;
        $condition = $request->condition;
        
        $queryBuilder = new \MacFJA\RediSearch\Query\Builder();

        if (!is_null($minPrice) && !is_null($maxPrice)){
            $queryBuilder->addElement(new NumericFacet('price', $minPrice, $maxPrice));
        }

        if($bathroom){
            $queryBuilder->addElement(NumericFacet::greaterThanOrEquals('bathroom', $bathroom));
        }

        if($bedroom){
            $queryBuilder->addElement(NumericFacet::greaterThanOrEquals('bedroom', $bedroom));
        }

        if (!is_null($landAreaMin) && !is_null($landAreaMax)){
            $queryBuilder->addElement(new NumericFacet('landArea', $landAreaMin, $landAreaMax));
        }    
        
        if (!is_null($buildingSizeMin) && !is_null($buildingSizeMax)){
            $queryBuilder->addElement(new NumericFacet('buildingSize', $buildingSizeMin, $buildingSizeMax));
        }    
        
        if (!is_null($landAreaMin) && !is_null($landAreaMax)){
            $queryBuilder->addElement(new NumericFacet('landArea', $landAreaMin, $landAreaMax));
        }

        if($q){
            $queryBuilder->addString($q);
            $queryBuilder->includeSpace();
            $queryBuilder->priority();
        }

        if($location){
            if(is_array($location)){
                $queryBuilder->addTagFacet('location',  ...$location);
            }else{
                $queryBuilder->addTagFacet('location', $location);
            }
        }

        if($type){
            if(is_array($type)){
                $queryBuilder->addTagFacet('type',  ...$type);
            }else{
                $queryBuilder->addTagFacet('type', $type);
            }
        }

        if($category){
            if(is_array($category)){
                $queryBuilder->addTagFacet('category',  ...$category);
            }else{
                $queryBuilder->addTagFacet('category', $category);
            }
        }

        if($condition){
            if(is_array($condition)){
                $queryBuilder->addTagFacet('condition',  ...$condition);
            }else{
                $queryBuilder->addTagFacet('condition', $condition);
            }
        }

        if($certificate){
            if(is_array($certificate)){
                $queryBuilder->addTagFacet('certificate',  ...$certificate);
            }else{
                $queryBuilder->addTagFacet('certificate', $certificate);
            }
        }

        if($furnish){
            if(is_array($furnish)){
                $queryBuilder->addTagFacet('furnish',  ...$furnish);
            }else{
                $queryBuilder->addTagFacet('furnish', $furnish);
            }
        }

        $sortByFields = $request->sortBy;

        if(empty($sortByFields)){
            $sortByFields = [];
        }

        $query = $queryBuilder->render();

        return $query;
    }
}