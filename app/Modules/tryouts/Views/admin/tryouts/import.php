<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Import Tryouts from CSV</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryouts/import" enctype="multipart/form-data">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>CSV Format:</strong> Your file must include these columns in order:
                        <code>location_name, age_group, tryout_date, start_time, end_time, cost, max_participants, status</code>
                    </div>

                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" required accept=".csv">
                        <small class="text-muted">Maximum file size: 5MB</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Important Notes:</strong>
                        <ul class="mb-0">
                            <li>Duplicate tryouts (same location, age group, date, and time) will be skipped</li>
                            <li>If a location doesn't exist, it will be created with placeholder address data</li>
                            <li>All dates should be in YYYY-MM-DD format (e.g., 2025-03-15)</li>
                            <li>Times should be in HH:MM format (e.g., 09:00, 14:30)</li>
                            <li>Status values: scheduled, open, closed, cancelled, completed</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import CSV
                        </button>
                        <a href="/admin/tryouts" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-file-earmark-text"></i> Sample CSV Format</h5>
                <pre class="small mb-0" style="background: white; padding: 10px; border-radius: 4px;">location_name,age_group,tryout_date,start_time,end_time,cost,max_participants,status
"Smith Park","10U","2025-03-15","09:00","11:00","35.00","30","open"
"Smith Park","12U","2025-03-15","11:30","13:30","35.00","30","open"
"Central High","14U","2025-03-22","14:00","16:00","40.00","25","scheduled"
"Riverfront Complex","8U","2025-03-29","10:00","12:00","0.00","","open"</pre>
            </div>
        </div>

        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-download"></i> Download Template</h5>
                <p class="small">Need a template to get started?</p>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                    <i class="bi bi-download"></i> Download CSV Template
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function downloadTemplate() {
    const csvContent = "data:text/csv;charset=utf-8," +
        "location_name,age_group,tryout_date,start_time,end_time,cost,max_participants,status\n" +
        "\"Sample Location\",\"10U\",\"2025-03-15\",\"09:00\",\"11:00\",\"35.00\",\"30\",\"open\"\n" +
        "\"Sample Location\",\"12U\",\"2025-03-15\",\"11:30\",\"13:30\",\"35.00\",\"30\",\"scheduled\"";

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "tryouts_import_template.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
