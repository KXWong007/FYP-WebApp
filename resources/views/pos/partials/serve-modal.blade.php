<div class="modal fade" id="serveDishModal" tabindex="-1" aria-labelledby="serveDishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Serve Dish: <span id="modalDishName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="serveStaffId" class="form-label">Staff ID</label>
                        <input type="text" class="form-control" id="serveStaffId">
                    </div>
                    <div class="col-md-6">
                        <label for="staffName" class="form-label">Staff Name</label>
                        <input type="text" class="form-control" id="staffName" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="finishTime" class="form-label">Finish Time</label>
                        <input type="text" class="form-control" id="finishTime" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="currentTime" class="form-label">Current Time</label>
                        <input type="text" class="form-control" id="currentTime" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="serveDishBtn" disabled>Serve Dish</button>
            </div>
        </div>
    </div>
</div>