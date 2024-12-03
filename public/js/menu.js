$(document).ready(function() {
    let searchTimeout;

    // Add Menu Form Submit
    $('#addMenu').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '/menu',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance($('#addMenuModal'));
                    modal.hide();
                    
                    // Clean up modal
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    
                    // Show success message
                    showAlert('success', "Menu item added successfully!");
                    
                    // Reset form and reload page to show new item
                    $("#resetButton").click();
                    location.reload();
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON.error || "Error adding menu item");
            }
        });
    });

    // Delete Menu Item
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this menu item?')) {
            const dishId = $(this).data('id');
            
            $.ajax({
                url: `/menu/${dishId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', "Menu item deleted successfully!");
                        // Remove the card from the UI
                        $(`.menu-card[data-id="${dishId}"]`).remove();
                        location.reload();
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON.error || "Error deleting menu item");
                }
            });
        }
    });

    // Edit Menu Item - Fetch Data
    $(document).on('click', '.edit-btn', function() {
        const dishId = $(this).data('id');
        console.log('Fetching menu item:', dishId);
        
        $.ajax({
            url: `/menu/${dishId}/edit`,
            method: 'GET',
            success: function(response) {
                console.log('Received data:', response);
                const menu = response.menu;
                
                // Populate edit modal fields
                $('#edit_dishId').val(menu.dishId);
                $('#edit_dishName').val(menu.dishName);
                $('#edit_category').val(menu.category);
                $('#edit_subcategory').val(menu.subcategory);
                $('#edit_cuisine').val(menu.cuisine);
                $('#edit_price').val(menu.price);
                $('#edit_availableTime').val(menu.availableTime);
                $('#edit_description').val(menu.description);
                
                // Handle available areas
                if (menu.availableArea) {
                    let areas;
                    if (Array.isArray(menu.availableArea)) {
                        areas = menu.availableArea;
                    } else if (typeof menu.availableArea === 'string') {
                        areas = menu.availableArea.split(',').map(area => area.trim());
                    } else {
                        areas = [];
                    }
                    
                    // Reset all checkboxes first
                    $('#edit_hornbill, #edit_badge, #edit_mainhall').prop('checked', false);
                    
                    // Check the appropriate boxes
                    areas.forEach(area => {
                        if (area === 'Hornbill Restaurant') $('#edit_hornbill').prop('checked', true);
                        if (area === 'Badge Bar') $('#edit_badge').prop('checked', true);
                        if (area === 'Mainhall') $('#edit_mainhall').prop('checked', true);
                    });
                }
                
                // Show existing image if available
                const preview = document.getElementById('edit_imagePreview');
                if (menu.image) {
                    preview.src = `/storage/${menu.image}`;
                    preview.style.display = 'block';
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                }
                
                // Show edit modal
                $('#editMenuModal').modal('show');
            },
            error: function(xhr) {
                console.error('Error fetching menu:', xhr);
                showAlert('error', 'Error fetching menu item details');
            }
        });
    });

    // Edit Menu Form Submit
    $('#editMenu').on('submit', function(e) {
        e.preventDefault();
        const dishId = $('#edit_dishId').val();
        const formData = new FormData(this);
        formData.append('_method', 'PUT'); // Laravel method spoofing for PUT request

        $.ajax({
            url: `/menu/${dishId}`,
            method: 'POST', // Actually sends as PUT due to _method field
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#editMenuModal').modal('hide');
                    
                    // Show success message
                    showAlert('success', "Menu item updated successfully!");
                    
                    // Reload page to show updated item
                    location.reload();
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON.error || "Error updating menu item");
            }
        });
    });

    // Image preview functionality for add form
    $('#image').on('change', function(e) {
        handleImagePreview(this, 'imagePreview');
    });

    // Image preview functionality for edit form
    $('#edit_image').on('change', function(e) {
        handleImagePreview(this, 'edit_imagePreview');
    });

    // Helper function for image preview
    function handleImagePreview(input, previewId) {
        const file = input.files[0];
        const preview = document.getElementById(previewId);
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }

    // Reset form when modal is hidden
    $('#addMenuModal').on('hidden.bs.modal', function() {
        $('#addMenu')[0].reset();
        $('#imagePreview').attr('src', '').hide();
    });

    // Reset button functionality
    $('#resetButton').click(function() {
        $('#addMenu')[0].reset();
        $('#imagePreview').attr('src', '').hide();
    });

    // Helper function for alerts
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('#alert-container').children().remove();
        $('#alert-container').append(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Filter functionality
    function filterMenuItems() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const selectedCategory = $('#categoryFilter').val();

        $('.menu-card').each(function() {
            const card = $(this);
            const dishName = card.find('.menu-title').text().toLowerCase();
            const category = card.find('.menu-details').text().match(/Category:\s*([^\n]+)/)[1].trim();
            
            console.log('Card category:', category, 'Selected category:', selectedCategory); // Debug line
            
            const matchesSearch = dishName.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;

            if (matchesSearch && matchesCategory) {
                card.show();
            } else {
                card.hide();
            }
        });

        // Show "no results" message if all cards are hidden
        const visibleCards = $('.menu-card:visible').length;
        if (visibleCards === 0) {
            if (!$('#noResults').length) {
                $('.menu-grid').append('<div id="noResults" class="text-center w-100 py-4">No menu items found</div>');
            }
        } else {
            $('#noResults').remove();
        }
    }

    // Event listeners for search and filter
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterMenuItems, 300);
    });

    $('#categoryFilter').on('change', filterMenuItems);

    // Clear filters button (optional)
    $('#clearFilters').on('click', function() {
        $('#searchInput').val('');
        $('#categoryFilter').val('');
        filterMenuItems();
    });

    // DishId availability checker
    let dishIdTimeout;
    $('#dishId').on('input', function() {
        const dishId = $(this).val().trim();
        const submitBtn = $('#addMenu button[type="submit"]');
        
        // Clear previous timeout
        clearTimeout(dishIdTimeout);
        
        // Clear message if input is empty
        if (!dishId) {
            $('#check-dishId').text('');
            submitBtn.prop('disabled', false);
            return;
        }
        
        // Set new timeout for checking
        dishIdTimeout = setTimeout(() => {
            $.ajax({
                url: `/check-dishid/${dishId}`,
                method: 'GET',
                success: function(response) {
                    if (response.exists) {
                        $('#check-dishId')
                            .text(response.message)
                            .removeClass('text-success')
                            .addClass('text-danger');
                        submitBtn.prop('disabled', true);
                    } else {
                        $('#check-dishId')
                            .text(response.message)
                            .removeClass('text-danger')
                            .addClass('text-success');
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function() {
                    $('#check-dishId')
                        .text('Error checking Dish ID availability')
                        .removeClass('text-success')
                        .addClass('text-danger');
                    submitBtn.prop('disabled', true);
                }
            });
        }, 500); // 500ms delay to prevent too many requests
    });
});
