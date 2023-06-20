<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .pagination-list {
        list-style-type: none;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 0;
    }

    .pagination-item {
        margin: 0 5px;
    }

    .pagination-link {
        text-decoration: none;
        padding: 5px 10px;
        border: 1px solid #ccc;
        color: #333;
    }

    .pagination-link.active {
        background-color: #ccc;
        color: #fff;
    }

    .pagination-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        display: none;
    }

	.pagination-item.active a {
		background-color: #337ab7;
		color: #fff;
	}

</style>

<!-- Tampilkan data produk -->
<div id="product-list">
    @include('partials.product_list')
</div>

<!-- Tampilkan tautan pagination -->
<div id="pagination-links">
    @include('partials.pagination')
</div>

<!-- Load jQuery dan script Ajax -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- <script>
    function loadProducts(page) {
        $.ajax({
            url: '/showalldatas?page=' + page,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Memperbarui tampilan daftar produk
                $('#product-list').html(response.products);

                // Memperbarui tautan pagination
                $('#pagination-links').html(response.pagination);
                //window.history.pushState('', '', '/showalldata?page=' + page);

            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }

    // Meng-handle klik pada tautan paginasi menggunakan event delegation
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();

        // Mendapatkan nomor halaman dari tautan yang diklik
        var page = $(this).attr('data-page');
        // Memuat data produk untuk halaman yang diklik
        loadProducts(page);
    });

    // Memuat data produk saat halaman pertama di-load
    $(document).ready(function() {
        loadProducts(1);
    }); 
</script> --}}
