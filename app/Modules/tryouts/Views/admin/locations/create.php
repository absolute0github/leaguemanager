<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Create Tryout Location</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryout-locations/create">
                    <div class="mb-3">
                        <label for="name" class="form-label">Location Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               placeholder="e.g., Smith Park, Central High School Field">
                        <small class="text-muted">A descriptive name for this location</small>
                    </div>

                    <div class="mb-3">
                        <label for="street_address" class="form-label">Street Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="street_address" name="street_address" required
                               placeholder="123 Main Street">
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="state" name="state" required
                                   placeholder="CA" maxlength="2">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="zip_code" class="form-label">ZIP Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" required
                                   placeholder="90210" maxlength="10">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="map_link" class="form-label">Map Link</label>
                        <input type="url" class="form-control" id="map_link" name="map_link"
                               placeholder="https://maps.google.com/...">
                        <small class="text-muted">Google Maps link for easy directions</small>
                    </div>

                    <div class="mb-3">
                        <label for="special_instructions" class="form-label">Special Instructions</label>
                        <textarea class="form-control" id="special_instructions" name="special_instructions" rows="4"
                                  placeholder="Parking information, gate codes, check-in location, etc."></textarea>
                        <small class="text-muted">Additional details for participants (parking, check-in, etc.)</small>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="active" name="active" checked>
                        <label class="form-check-label" for="active">
                            Active <small class="text-muted">(Available for new tryouts)</small>
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Location
                        </button>
                        <a href="/admin/tryout-locations" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Location Tips</h5>
                <ul class="small">
                    <li>Use descriptive names that players will recognize</li>
                    <li>Include the full street address for GPS accuracy</li>
                    <li>Add a Google Maps link for easy navigation</li>
                    <li>Use special instructions for:
                        <ul>
                            <li>Parking details</li>
                            <li>Gate codes or entry instructions</li>
                            <li>Check-in location</li>
                            <li>Facility-specific rules</li>
                        </ul>
                    </li>
                    <li>Inactive locations won't appear in tryout creation dropdown</li>
                </ul>
            </div>
        </div>
    </div>
</div>
