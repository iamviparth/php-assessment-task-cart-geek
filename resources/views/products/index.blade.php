<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Products</h2>
    <button class="btn btn-primary mb-3" onclick="showCreateForm()">Add Product</button>
    <table id="productTable" class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Images</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->product_price }}</td>
                <td>{{ $product->product_description }}</td>
                <td>
                    @foreach(json_decode($product->product_images) as $image)
                        <img src="{{ asset('storage/' . $image) }}" width="50" height="50">
                    @endforeach
                </td>
                <td>
                    <button class="btn btn-warning" onclick="showEditForm({{ $product->id }})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteProduct({{ $product->id }})">Delete</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Create/Edit Form Modal -->
<div class="modal" id="productModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    @csrf
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="product_price">Product Price</label>
                        <input type="text" id="product_price" name="product_price" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="product_description">Product Description</label>
                        <textarea id="product_description" name="product_description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="product_images">Product Images</label>
                        <input type="file" id="product_images" name="product_images[]" class="form-control" multiple>
                        <div class="mt-2" id="current_images"></div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="formSubmitButton"></button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('#productTable').DataTable();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });

    function showCreateForm() {
        $('#modalTitle').text('Add Product');
        $('#formSubmitButton').text('Add');
        $('#productForm').trigger('reset');
        $('#productModal').modal('show');
        $('#productForm').off('submit').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('products.store') }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#productModal').modal('hide');
                    location.reload();
                },
                error: function(response) {
                    alert('Error: ' + response.responseJSON.message);
                }
            });
        });
    }

    function showEditForm(id) {
        $.get("{{ url('products') }}/" + id + "/edit", function(product) {
            $('#modalTitle').text('Edit Product');
            $('#formSubmitButton').text('Update');
            $('#product_name').val(product.product_name);
            $('#product_price').val(product.product_price);
            $('#product_description').val(product.product_description);

            // Clear previous images
            $('#current_images').empty();
            // Display current images with delete button
            let images = JSON.parse(product.product_images);
            images.forEach(function(image) {
                $('#current_images').append(`
                    <div class="image-container">
                        <img src="{{ asset('storage/') }}/${image}" width="50" height="50">
                        <button class="btn btn-danger btn-sm delete-image" data-id="${product.id}" data-image="${image}">Delete</button>
                    </div>
                `);
            });

            $('#productModal').modal('show');
            $('#productForm').off('submit').on('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                formData.append('_method', 'PUT');
                $.ajax({
                    url: "{{ url('products') }}/" + id,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#productModal').modal('hide');
                        location.reload();
                    },
                    error: function(response) {
                        alert('Error: ' + response.responseJSON.message);
                    }
                });
            });
        });
    }
    $(document).on('click', '.delete-image', function() {
        const productId = $(this).data('id');
        const imageName = $(this).data('image');
        const imageContainer = $(this).parent();

        $.ajax({
            url: "{{ url('products/delete-image') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                image_name: imageName
            },
            success: function(response) {
                imageContainer.remove();
            },
            error: function(response) {
                alert('Error: ' + response.responseJSON.message);
            }
        });
    });


    function deleteProduct(id) {
        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: "{{ url('products') }}/" + id,
                method: 'DELETE',
                success: function(response) {
                    location.reload();
                },
                error: function(response) {
                    alert('Error: ' + response.responseJSON.message);
                }
            });
        }
    }
</script>
</body>
</html>
