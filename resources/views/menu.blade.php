@extends('layout')

@section('title', 'Menu Management')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .menu-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .menu-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .menu-content {
            padding: 15px;
        }

        .dish-id {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #ff4444;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .menu-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .menu-details {
            margin-bottom: 15px;
        }

        .menu-details p {
            margin: 5px 0;
            color: #666;
        }

        .menu-actions {
            display: flex;
            gap: 10px;
        }

        .menu-actions69 button {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .edit-btn {
            background: #ffc107;
            color: white;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .edit-btn:hover {
            background: #e0a800;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .available {
            background: #28a745;
            color: white;
        }

        .unavailable {
            background: #dc3545;
            color: white;
        }

        .area-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .area-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .area-checkbox {
            margin-right: 8px;
        }

        .area-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Custom Toastr styling */
        .toast-success {
            background-color: #28a745 !important;
        }

        .toast-error {
            background-color: #dc3545 !important;
        }

        #toast-container > div {
            opacity: 1;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.25rem;
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-box {
            flex: 1;
            max-width: 300px;
        }

        .category-filter {
            width: 200px;
        }

        /* Optional: Add transition for smooth filtering */
        .menu-card {
            transition: all 0.3s ease;
        }

        .menu-card.hidden {
            display: none;
        }

        /* Add to your existing styles */
        #check-dishId {
            display: block;
            margin-top: 5px;
            font-size: 0.875rem;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-success {
            color: #28a745;
        }
    </style>

    <!-- Make sure this is included before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection

@section('content')
    <h1 class="dashboard-title">MENU MANAGEMENT</h1>

    <!-- Menu Actions -->

    <div class="menu-actions">
        <button type="button" class="btn btn-custom btn-success" id="addMenuBtn" data-bs-toggle="modal" data-bs-target="#addMenuModal">
            <i class="fas fa-plus"></i> Add Menu Item
        </button>

        <!-- Add these filter controls -->
        <div class="filter-controls">
            <div class="search-box bg-light">
                <input type="text" id="searchInput" class="form-control" placeholder="Search menu here">
            </div>
            <div class="category-filter">
                <select id="categoryFilter" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($menus->pluck('category')->unique() as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Menu Grid -->
    <div class="menu-grid">
        @foreach($menus as $menu)
            <div class="menu-card position-relative" data-id="{{ $menu->dishId }}">
                <div class="dish-id">{{ $menu->dishId }}</div>
                <span class="availability-badge {{ $menu->availability ? 'available' : 'unavailable' }}">
                    {{ $menu->availability ? 'Available' : 'Not Available' }}
                </span>
                <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->dishName }}" class="menu-image">
                <div class="menu-content">
                    <div class="menu-title">{{ $menu->dishName }}</div>
                    <div class="menu-details">
                        <p><strong>Price:</strong> RM{{ number_format($menu->price, 2) }}</p>
                        <p><strong>Category:</strong> {{ ucfirst($menu->category) }}</p>
                        @if($menu->availableTime)
                            <p><strong>Time:</strong> {{ $menu->availableTime }}</p>
                        @endif
                    </div>
                    <div class="menu-actions69">
                        <button class="edit-btn" data-id="{{ $menu->dishId }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="delete-btn" data-id="{{ $menu->dishId }}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Menu Modal -->
    <div class="modal fade" id="addMenuModal" tabindex="-1" aria-labelledby="addMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMenuModalLabel">Add Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addMenu" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="dishId">Dish ID</label>
                                <input type="text" id="dishId" name="dishId" class="form-control" required>
                                <span id="check-dishId" class="text-danger"></span>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="dishName">Dish Name</label>
                                <input type="text" id="dishName" name="dishName" class="form-control" required>
                            </div>
                        </div>

                        <div class ="single-row">
                            <div class="mb-3 single-column">
                                <label for="category">Category</label>
                                <select name="category" id="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Appetizer">Appetizer</option>
                                    <option value="MainCourse">Main Course</option>
                                    <option value="Dessert">Dessert</option>
                                    <option value="Beverage">Beverage</option>
                                    <option value="Soups">Soups</option>
                                    <option value="Salads">Salads</option>
                                    <option value="Sides">Sides</option>
                                    <option value="Snacks">Snacks</option>
                                    <option value="Specials">Specials</option>

                                </select>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="category">Subcategory</label>
                                <select name="subcategory" id="subcategory" class="form-control" required>
                                <option value="">None Selected</option>
                                <option value="Hot">Hot</option>
                                <option value="Cold">Cold</option>
                                <option value="Vegetarian">Vegetarian</option>
                                <option value="Non-Vegetarian">Non-Vegetarian</option>
                                <option value="Spicy">Spicy</option>
                                <option value="Sweet">Sweet</option>
                                <option value="Alcoholic">Alcoholic</option>
                                <option value="Non-Alcoholic">Non-Alcoholic</option>
                                <option value="Small Bites">Small Bites</option>
                                <option value="Burgers & Sandwiches">Burgers & Sandwiches</option>

                                </select>
                            </div>

                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="category">Cuisine</label>
                                <select name="cuisine" id="cuisine" class="form-control" required>
                                <option value="">None Selected</option>
                                <option value="Malaysian">Malaysian</option>
                                <option value="Chinese">Chinese</option>
                                <option value="Indian">Indian</option>
                                <option value="Western">Western</option>
                                <option value="Japanese">Japanese</option>
                                <option value="Korean">Korean</option>
                                <option value="Thai">Thai</option>
                                <option value="Italian">Italian</option>
                                <option value="Mexican">Mexican</option>
                                </select>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="price">Price (RM)</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                            </div>


                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="availableTime">Available Time</label>
                                <input type="time" id="availableTime" name="availableTime" class="form-control">
                            </div>

                            <div class="mb-3 single-column">
                                <label for="availability">Availability</label>
                                <select name="availability" id="availability" class="form-control" required>
                                    <option value="1">Available</option>
                                    <option value="0">Not Available</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="image">Dish Image</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="form-group mb-3">
                                    <label for="availableArea" class="form-label">Available Area</label>
                                    <div class="checkbox-group">
                                        <div class="area-options">
                                            <div class="area-option">
                                                <input type="checkbox" id="hornbill" name="availableArea[]" value="Hornbill Restaurant" class="area-checkbox">
                                                <label for="hornbill" class="area-label">
                                                    <i class="fas fa-utensils"></i>
                                                    <span>Hornbill Restaurant</span>
                                                </label>
                                            </div>
                                            
                                            <div class="area-option">
                                                <input type="checkbox" id="badge" name="availableArea[]" value="Badge Bar" class="area-checkbox">
                                                <label for="badge" class="area-label">
                                                    <i class="fas fa-glass-martini-alt"></i>
                                                    <span>Badge Bar</span>
                                                </label>
                                            </div>
                                            
                                            <div class="area-option">
                                                <input type="checkbox" id="mainhall" name="availableArea[]" value="Mainhall" class="area-checkbox">
                                                <label for="mainhall" class="area-label">
                                                    <i class="fas fa-door-open"></i>
                                                    <span>Mainhall</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 single-column">
                                <div class="mt-2">
                                    <img id="imagePreview" src="" alt="Dish Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                                </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" id="resetButton">Clear</button>
                        <button type="submit" class="btn btn-dark">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Menu Modal -->
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMenuModalLabel">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editMenu" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_dishId" name="dishId">
                    <div class="modal-body">
                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_dishName">Dish Name</label>
                                <input type="text" id="edit_dishName" name="dishName" class="form-control" required>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="edit_availableTime">Available Time</label>
                                <input type="time" id="edit_availableTime" name="availableTime" class="form-control">
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_category">Category</label>
                                <select name="category" id="edit_category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Appetizer">Appetizer</option>
                                    <option value="MainCourse">Main Course</option>
                                    <option value="Dessert">Dessert</option>
                                    <option value="Beverage">Beverage</option>
                                    <option value="Soups">Soups</option>
                                    <option value="Salads">Salads</option>
                                    <option value="Sides">Sides</option>
                                    <option value="Snacks">Snacks</option>
                                    <option value="Specials">Specials</option>
                                </select>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="edit_subcategory">Subcategory</label>
                                <select name="subcategory" id="edit_subcategory" class="form-control" required>
                                    <option value="">None Selected</option>
                                    <option value="Hot">Hot</option>
                                    <option value="Cold">Cold</option>
                                    <option value="Vegetarian">Vegetarian</option>
                                    <option value="Non-Vegetarian">Non-Vegetarian</option>
                                    <option value="Spicy">Spicy</option>
                                    <option value="Sweet">Sweet</option>
                                    <option value="Alcoholic">Alcoholic</option>
                                    <option value="Non-Alcoholic">Non-Alcoholic</option>
                                    <option value="Small Bites">Small Bites</option>
                                    <option value="Burgers & Sandwiches">Burgers & Sandwiches</option>
                                </select>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_cuisine">Cuisine</label>
                                <select name="cuisine" id="edit_cuisine" class="form-control" required>
                                    <option value="">None Selected</option>
                                    <option value="Malaysian">Malaysian</option>
                                    <option value="Chinese">Chinese</option>
                                    <option value="Indian">Indian</option>
                                    <option value="Western">Western</option>
                                    <option value="Japanese">Japanese</option>
                                    <option value="Korean">Korean</option>
                                    <option value="Thai">Thai</option>
                                    <option value="Italian">Italian</option>
                                    <option value="Mexican">Mexican</option>
                                </select>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="edit_price">Price (RM)</label>
                                <input type="number" id="edit_price" name="price" class="form-control" step="0.01" required>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="mb-3 single-column">
                                <label for="edit_description">Description</label>
                                <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="mb-3 single-column">
                                <label for="edit_image">Dish Image</label>
                                <input type="file" id="edit_image" name="image" class="form-control" accept="image/*">
                                <div class="mt-2">
                                    <img id="edit_imagePreview" src="" alt="Menu Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                        </div>

                        <div class="single-row">
                            <div class="form-group mb-3">
                                <label for="edit_availableArea" class="form-label">Available Area</label>
                                <div class="checkbox-group">
                                    <div class="area-options">
                                        <div class="area-option">
                                            <input type="checkbox" id="edit_hornbill" name="availableArea[]" value="Hornbill Restaurant" class="area-checkbox">
                                            <label for="edit_hornbill" class="area-label">
                                                <i class="fas fa-utensils"></i>
                                                <span>Hornbill Restaurant</span>
                                            </label>
                                        </div>
                                        
                                        <div class="area-option">
                                            <input type="checkbox" id="edit_badge" name="availableArea[]" value="Badge Bar" class="area-checkbox">
                                            <label for="edit_badge" class="area-label">
                                                <i class="fas fa-glass-martini-alt"></i>
                                                <span>Badge Bar</span>
                                            </label>
                                        </div>
                                        
                                        <div class="area-option">
                                            <input type="checkbox" id="edit_mainhall" name="availableArea[]" value="Mainhall" class="area-checkbox">
                                            <label for="edit_mainhall" class="area-label">
                                                <i class="fas fa-door-open"></i>
                                                <span>Mainhall</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-dark">Update Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Keep the same script imports as payment.blade.php -->
    <script>
        $(document).ready(function() {
            // Edit Menu
            $('.edit-btn').click(function() {
                // Add your edit functionality here
            });

            // Delete Menu
            $('.delete-btn').click(function() {
                // Add your delete functionality here
            });

            // Image preview functionality
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Add other necessary JavaScript functionality
        });
    </script>
    <script src="{{ asset('js/menu.js') }}"></script>
@endsection
