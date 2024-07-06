<!-- resources/views/products/edit.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Edit Product</h2>
    <form id="editProductForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" id="product_name" name="product_name" class="form-control" value="{{ $product->product_name }}">
        </div>
        <div class="form-group">
            <label for="product_price">Product Price</label>
            <input type="text" id="product_price" name="product_price" class="form-control" value="{{ $product->product_price }}">
        </div>
        <div class="form-group">
            <label for="product_description">Product Description</label>
            <textarea id="product_description" name="product_description" class="form-control">{{ $product->product_description }}</textarea>
        </div>
        <div class="form-group">
            <label for="product_images">Product Images</label>
            <input type="file" id="product_images" name="product_images[]" class="form-control" multiple>
            <div class="mt-2">
                @foreach(json_decode($product->product_images) as $image)
                    <img src="{{ asset('storage/' . $image) }}" width="50" height="50">
                @endforeach
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#editProductForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            formData.append('_method', 'PUT');
            $.ajax({
                url: "{{ url('products') }}/" + "{{ $product->id }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert('Product updated successfully.');
                    window.location.href = "{{ url('products') }}";
                },
                error: function(response) {
                    alert('Error: ' + response.responseJSON.message);
                }
            });
        });
    });
</script>
</body>
</html>
