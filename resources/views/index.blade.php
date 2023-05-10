<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />


    <script src="https://unpkg.com/htmx.org@1.9.2"
        integrity="sha384-L6OqL9pRWyyFU3+/bjdSri+iIphTN/bvYyM37tICVyOJkWZLpP2vGn6VUEXgzg6h" crossorigin="anonymous">
    </script>
</head>

<body class="md:container md:mx-auto py-4">

    <h1 class="mt-0 mb-2 text-3xl font-medium leading-tight">Original Data</h1>
    <div class="item mb-2 md:flex md:flex-wrap md:justify-between">
        <div class="container w-full px-4 sm:px-8">
            <style>
                #table {
                    width: 50%;
                    height: 500px;
                    overflow: scroll;
                }
            </style>
            <div id="table">
                <table>
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="w-20 px-4 py-2">No.</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Furnish</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @fragment('original-data')
                            @foreach ($originalData as $item)
                                    <tr @if($loop->last)
                                        hx-get="{{$next}}"
                                        hx-trigger="intersect once"
                                        hx-swap="afterend"
                                        @endif>
                                        <td>{{$item->id}}</td>
                                        <td>{{$item->title}}</td>
                                        <td>{{$item->furnish}}</td>
                                        <td>{{$item->price}}</td>
                                    </tr>
                            @endforeach
                        @endfragment
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    </div>

    <h1 class="mt-4 mb-2 text-3xl font-medium leading-tight">Search Data</h1>
    <div class="my-2 flex flex-col sm:flex-row">
        <div class="relative block w-1/3">
            <span class="absolute inset-y-0 left-0 flex h-full items-center pl-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current text-gray-500">
                    <path
                        d="M10 4a6 6 0 100 12 6 6 0 000-12zm-8 6a8 8 0 1114.32 4.906l5.387 5.387a1 1 0 01-1.414 1.414l-5.387-5.387A8 8 0 012 10z">
                    </path>
                </svg>
            </span>
            <input autocomplete="off"  name="q" hx-get="/search"
            hx-trigger="keyup[target.value.length > 3] changed delay:500ms" hx-target="#search-results"
            placeholder="Search Data..."
                class="block w-full appearance-none rounded-r rounded-l border border-b border-gray-400 bg-white py-2 pl-8 pr-6 text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:text-gray-700 focus:placeholder-gray-600 focus:outline-none" />
        </div>
    </div>
    <h2 class="my-8 text-xl font-medium leading-tight">Searching Result</h2>
    <div id="search-results">
        @fragment('search-result')
        @foreach ($data as $item)
            <ul class="pl-5 mt-2 space-y-1 list-none list-inside">
                <li>title: {!! $item['title'] !!}</li>
                <li>furnish: {!!  $item['furnish'] !!}</li>
                <li>location: {!!  $item['location'] !!}</li>
                <li>address: {!!  $item['address'] !!}</li>
                <li>price: {!!  $item['price'] !!}</li>
            </ul>
            <hr>
            @endforeach
        @endfragment
        </div>


</body>

</html>
