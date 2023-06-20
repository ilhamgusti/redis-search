<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;
use MacFJA\RediSearch\Query\Builder;
use MacFJA\RediSearch\Redis\Client\ClientFacade;
use MacFJA\RediSearch\Query\Builder\TagFacet;

class ShowallController extends Controller
{
    public function index(Request $request)
    {
        $productKeys = Redis::keys('product:*');
        $perPage = $request->query('per_page', 3);
        $page = $request->query('page', 1);
       
        $total = count($productKeys);

        // Menggunakan LengthAwarePaginator untuk mempermudah pagination
        $paginator = new LengthAwarePaginator(
            array_slice($productKeys, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
      
        // Mendapatkan data produk pada halaman yang diminta
        $productData = $paginator->items();
        $data = [];
        
        foreach ($productData as $productKey) {
            $productKey = str_replace("laravel_database_", "", $productKey);
            // Mengambil semua field dan nilainya dari setiap kunci produk
            $product = Redis::hgetall($productKey);

            $data[] = $product;
        }
       
        $totalPages = $paginator->lastPage();
        $prevPageUrl = $paginator->previousPageUrl();
        $nextPageUrl =$paginator->nextPageUrl();
        if (!$prevPageUrl) {
            $prevPageUrl = null;
        }

        $range = range(1, $totalPages);
       

        $links = [];
        foreach ($range as $pageNumber) {
            $links[] = $request->url() . '?page=' . $pageNumber;
        }
        return view('showall', [
            'products' => $data,
            'paginator' => $paginator,
            'prevPageUrl' => $prevPageUrl,
            'nextPageUrl' => $paginator->nextPageUrl(),
        ]);
        //return view('showall');
        // return response()->json($response);
    }

    public function loadData(Request $request) {
        // Mendapatkan semua kunci produk dari Redis
        $productKeys = Redis::keys('product:*');
        $perPage = $request->query('per_page', 3);
        $page = $request->query('page', 1);
       
        $total = count($productKeys);

        // Menggunakan LengthAwarePaginator untuk mempermudah pagination
        $paginator = new LengthAwarePaginator(
            array_slice($productKeys, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
      
        // Mendapatkan data produk pada halaman yang diminta
        $productData = $paginator->items();
        $data = [];
        
        foreach ($productData as $productKey) {
            $productKey = str_replace("laravel_database_", "", $productKey);
            // Mengambil semua field dan nilainya dari setiap kunci produk
            $product = Redis::hgetall($productKey);

            $data[] = $product;
        }
       
        $totalPages = $paginator->lastPage();
        $prevPageUrl = $paginator->previousPageUrl();
        $nextPageUrl =$paginator->nextPageUrl();
        if (!$prevPageUrl) {
            $prevPageUrl = null;
        }

        $range = range(1, $totalPages);
       

        $links = [];
        foreach ($range as $pageNumber) {
            $links[] = $request->url() . '?page=' . $pageNumber;
        }
        return view('showall', [
            'products' => $data,
            'paginator' => $paginator,
            'prevPageUrl' => $prevPageUrl,
            'nextPageUrl' => $paginator->nextPageUrl(),
        ]);


        // Mengambil tampilan produk dan tautan pagination sebagai string
        // $productsHtml = view('partials.product_list', ['products' => $data])->render();
        // $paginationHtml = view('partials.pagination', compact('paginator', 'prevPageUrl', 'nextPageUrl'))->render();

        // return response()->json([
        //     'products' => $productsHtml,
        //     'pagination' => $paginationHtml
        // ]);

    }


    public function filterProducts(Request $request)  {
        
        // Mendapatkan kategori yang dipilih
        $selectedCategories =  $request->category;
        
        $query = '';
        if (!empty($selectedCategories)) {
            $categoryQuery = implode('|', $selectedCategories);
            $query = "$categoryQuery";
        } 
        // else {
        //     return $this->loadData($request);
        // }
       
         // Mengatur jumlah item per halaman
        $perPage = $request->input('per_page', 3);

        // Mendapatkan nomor halaman yang diminta
        $page = $request->input('page',1);
        $clientFacade = new ClientFacade();
        $client = $clientFacade->getClient(Redis::client());
        $queryBuilder = new Builder();
        $queries = $queryBuilder
                    ->addElement(new TagFacet(['idkategori'], $query))
                    ->render();
        
       
        $search = new \MacFJA\RediSearch\Redis\Command\Search();
        $search
            ->setIndex('product-idx')
            ->setSortBy('harga',$request->sort ?? 'ASC')
            ->setLimit($page, $perPage)
            ->setQuery($queries);
        $results = $client->execute($search);
        $items = $results->current();
        // Menjalankan Redis Search untuk mencari produk berdasarkan kategori dengan paginasi
        
        $products= [];
        foreach ($items as $key => $value) {
            array_push($products, $value->getFields());
        }
      
         // Menggunakan LengthAwarePaginator untuk mempermudah pagination
         $paginator = new LengthAwarePaginator(
            $products,
            $results->getTotalCount(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $totalPages = $paginator->lastPage();
        $prevPageUrl = $paginator->previousPageUrl();
        $nextPageUrl =$paginator->nextPageUrl();
        if (!$prevPageUrl) {
            $prevPageUrl = null;
        }
        // Mengembalikan tampilan produk dan tautan pagination sebagai respons JSON
        // $productsHtml = view('partials.product_list', ['products' => $products,'selectedCategories'=>$selectedCategories])->render();
        // $paginationHtml = view('partials.pagination', compact('paginator', 'prevPageUrl', 'nextPageUrl'))->render();

        return view('showall', [
            'products' => $products,
            'paginator' => $paginator,
            'selectedCategories' => $selectedCategories,
            'prevPageUrl' => $prevPageUrl,
            'nextPageUrl' => $paginator->nextPageUrl(),
        ]);
        // return response()->json([
        //     'products' => $productsHtml,
        //     'pagination' => $paginationHtml,
        // ]);
    }

 
    public function bycategori(Request $request, $idKategori = null)
    {

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        // Mendapatkan semua data produk dari Redis Hash
        $products[] = Redis::hgetall("product:$idKategori");
        // Menghitung total data produk
        $total = count($products);


        // Menggunakan LengthAwarePaginator untuk mempermudah pagination
        $paginator = new LengthAwarePaginator(
            array_slice($products, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Mendapatkan data produk pada halaman yang diminta
        $data = $paginator->items();

        // Mengambil link pagination
        $urlRange = $paginator->getUrlRange($page - 1, $page + 1);

        // Membuat link prev dan next
        $prevPageUrl = $paginator->previousPageUrl();
        $nextPageUrl = $paginator->nextPageUrl();

        // Mengembalikan data dalam bentuk JSON
        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
            'prev_page_url' => $prevPageUrl,
            'next_page_url' => $nextPageUrl,

        ]);
    }
}
