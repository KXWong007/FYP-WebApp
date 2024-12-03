@extends('layout')

@section('content')
<div class="container">

    <!-- Success and Error Messages -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <hr>

    <!-- Forecast Duration Selector -->
    <form method="GET" action="{{ route('forecast.index') }}">
        <div class="row mb-3">
            <div class="col">
                <label for="duration" class="form-label">Select Forecast Duration:</label>
                <select class="form-select" id="duration" name="duration" onchange="this.form.submit()">
                    <option value="7" {{ $duration == 7 ? 'selected' : '' }}>1 Week (7 days)</option>
                    <option value="30" {{ $duration == 30 ? 'selected' : '' }}>1 Month (30 days)</option>
                </select>
            </div>
        </div>
    </form>

    <!-- List of Items and Their Forecasting Data -->
    <h2>Forecasting Inventory Items</h2>
    <ul class="list-group">
        @foreach ($items as $item)
            <li class="list-group-item" onclick="toggleDetails('{{ $item->inventoryId }}', event)">
                <h5>{{ $item->itemName }} ({{ $item->measurementUnit }})</h5>

                <!-- Button to Trigger Add Usage Data Modal -->
                <button class="btn btn-primary" onclick="openAddUsageModal('{{ $item->inventoryId }}', '{{ $item->itemName }}')">Add Usage Data</button>

                <!-- Hidden section for graph and data -->
                <div id="details-{{ $item->inventoryId }}" style="display: none;">
                    <canvas id="forecastChart-{{ $item->inventoryId }}"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx{{ $item->inventoryId }} = document.getElementById('forecastChart-{{ $item->inventoryId }}').getContext('2d');
                        const data{{ $item->inventoryId }} = @json($item->forecasting);

                        new Chart(ctx{{ $item->inventoryId }}, {
                            type: 'line',
                            data: {
                                labels: data{{ $item->inventoryId }}.map(item => item.date),
                                datasets: [{
                                    label: 'Daily Usage',
                                    data: data{{ $item->inventoryId }}.map(item => item.dailyUsage),
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                label += context.raw + ' {{ $item->measurementUnit }}';
                                                return label;
                                            }
                                        }
                                    },
                                    legend: {
                                        display: true,
                                        labels: {
                                            color: '#333'
                                        }
                                    }
                                },
                                interaction: {
                                    mode: 'nearest',
                                    axis: 'x',
                                    intersect: false
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            color: 'rgba(200, 200, 200, 0.2)'
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(200, 200, 200, 0.2)'
                                        }
                                    }
                                }
                            }
                        });
                    </script>

                    <!-- Next Stock Requirement Section -->
                    <p><strong>Next {{ $duration == 7 ? "Week's" : "Month's" }} Stock Requirement:</strong>
                        @php
                            $totalUsage = $item->forecasting->sum('dailyUsage');
                            $daysCount = $item->forecasting->count();
                            $averageDailyUsage = $daysCount > 0 ? $totalUsage / $daysCount : 0;
                            $totalRequirement = $averageDailyUsage * $duration;
                        @endphp
                        {{ number_format($totalRequirement, 2) }} {{ $item->measurementUnit }}
                    </p>

                    <!-- Forecasting data table for the current item -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Daily Usage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->forecasting as $forecast)
                                    <tr>
                                        <td>{{ $forecast->date }}</td>
                                        <td>{{ $forecast->dailyUsage }} {{ $forecast->measurementUnit }}</td>
                                        <td>
                                            <!-- Edit Button to Trigger Modal -->
                                            <button class="btn btn-warning btn-sm" onclick="openEditModal({{ $forecast->id }}, '{{ $forecast->dailyUsage }}', '{{ $forecast->date }}')">Edit</button>
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('forecast.destroy', $forecast->id) }}" method="POST" style="display:inline;" onclick="event.stopPropagation()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    <!-- Add Usage Data Modal -->
    <div class="modal fade" id="addUsageModal" tabindex="-1" aria-labelledby="addUsageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addUsageForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUsageModalLabel">Add Daily Usage for <span id="modalItemName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="inventoryId" id="modalInventoryId">
                        <div class="mb-3">
                            <label for="dailyUsage" class="form-label">Daily Usage:</label>
                            <input type="number" name="dailyUsage" id="dailyUsage" class="form-control" required step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date:</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Forecast Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Forecasting Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editDailyUsage">Daily Usage:</label>
                            <input type="number" name="dailyUsage" id="editDailyUsage" class="form-control" required step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="editDate">Date:</label>
                            <input type="date" name="date" id="editDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    // Toggle the visibility of the details section for each inventory item
    function toggleDetails(inventoryId, event) {
        if (event.target.closest('button')) {
            return;
        }
        const detailsDiv = document.getElementById('details-' + inventoryId);
        detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
    }

    // Open the Add Usage Modal
    function openAddUsageModal(inventoryId, itemName) {
        document.getElementById('modalInventoryId').value = inventoryId;
        document.getElementById('modalItemName').innerText = itemName;
        document.getElementById('addUsageForm').action = `/forecast/store/${inventoryId}`;
        var addUsageModal = new bootstrap.Modal(document.getElementById('addUsageModal'));
        addUsageModal.show();
    }

    // Open the Edit Modal with pre-filled values
    function openEditModal(id, dailyUsage, date) {
        document.getElementById('editDailyUsage').value = dailyUsage;
        document.getElementById('editDate').value = date;
        document.getElementById('editForm').action = `/forecast/update/${id}`;
        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }

    // Handle low stock alert messages (display in the view)
    function showLowStockAlerts() {
        const lowStockAlerts = @json(session('low_stock_alerts', [])); // Pass session data to JS
        
        if (lowStockAlerts.length > 0) {
            let alertContainer = document.createElement('div');
            alertContainer.classList.add('alert', 'alert-warning');
            alertContainer.innerHTML = '<strong>Low Stock Alerts:</strong><ul>' + 
                                        lowStockAlerts.map(alert => `<li>${alert}</li>`).join('') + 
                                        '</ul>';
            document.querySelector('.container').insertBefore(alertContainer, document.querySelector('.container').firstChild);
            
            // Clear low stock alerts from the session after displaying
            @php session()->forget('low_stock_alerts'); @endphp
        }
    }

    // Initialize low stock alerts when the page is loaded
    document.addEventListener('DOMContentLoaded', function() {
        showLowStockAlerts();
    });
</script>

@endsection
