<style>
     /* CSS untuk Sortir Harga */
     #sort-by {
        margin-bottom: 20px;
    }

    #sort-by h4 {
        margin-bottom: 5px;
    }

    #sort-select {
        padding: 5px;
        font-size: 14px;
    }

    #sort-button {
        padding: 5px 10px;
        font-size: 14px;
        background-color: #4CAF50;
        color: #fff;
        border: none;
        cursor: pointer;
    }

    #sort-button:hover {
        background-color: #45a049;
    }
    .product {
        background-color: #f2f2f2;
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 10px;
    }

    .product h3 {
        font-size: 16px;
        margin: 0;
    }

    .product p {
        font-size: 14px;
        margin: 5px 0;
    }

    #category-filter {
        margin-bottom: 20px;
    }

    .category-list {
        list-style-type: none;
        padding: 0;
    }

    .category-list li {
        margin-bottom: 10px;
    }

    .category-list label {
        margin-left: 5px;
    }
</style>

<div id="category-filter">
    <h4>Filter Berdasarkan Kategori</h4>
    <form id="filter-form" action="{{ route('filter.products') }}" method="GET">
    <ul class="category-list">
        @for ($i = 1; $i <= 10; $i++)
            <li>
                <input type="checkbox" class="category-checkbox" name="category[]" id="category{{$i}}" name="category" value="{{$i}}" {{ isset($selectedCategories) && in_array($i,$selectedCategories) ? 'checked' : '' }}>
                <label for="category{{$i}}">Kategori {{$i}}</label>
            </li>
        @endfor
    </ul>
    <button type="submit">Filter</button>
</div>

<!-- opsi untuk sortir -->
<div id="sort-by">
    <h4>Sortir Berdasarkan Harga</h4>
    <select id="sort-select" name="sort">
        <option value="ASC">Harga Terendah</option>
        <option value="DESC">Harga Tertinggi</option>
    </select>
    <button id="sort-button" type="submit">Sortir</button>
    </form>
</div>

<!-- Tampilkan data produk -->
<div id="product-list">
    @isset($products)
        @foreach ($products as $product)
            <div class="product">
                <h3>{{ $product['nama'] }}</h3>
                <p class="price">Price: ${{ $product['harga'] }}</p>
                <p class="idkategori">Category ID: {{ $product['idkategori'] }}</p>
            </div>
        @endforeach
    @endisset
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- <script type="text/javascript">
    var csrfToken = $('meta[name="csrf-token"]').attr('content')

    function loadProductsWithFilter() {
        var activePage = $('li.pagination-item.active').find('a.pagination-link').text();
        if (!activePage) {
            activePage =1;
        }
        var categories = [];
        
        $('input[name="category[]"]:checked').each(function() {
			categories.push($(this).val());
   		});
        // Memuat data produk dengan filter kategori
		$.ajax({
            url: '{{ route("filter.products") }}',
            type: 'POST',
            dataType: 'json',
            data: {
                categories: categories,
                page: activePage,
                _token: csrfToken
            },
            success: function(response) {
            // Memperbarui tampilan daftar produk
            $('#product-list').html(response.products);

            // Memperbarui tautan pagination
            $('#pagination-links').html(response.pagination);
        },
        error: function(xhr, status, error) {
            console.log(error);
        }
    });
  }
	

    // Event listener untuk checkbox kategori
    $('.category-checkbox').change(function() {
        loadProductsWithFilter();
    });
</script> --}}