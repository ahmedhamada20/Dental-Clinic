<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Print View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .card {
                page-break-inside: avoid;
            }
        }
        body {
            padding: 20px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .report-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="no-print mb-3">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <i class="bi bi-x"></i> Close
        </button>
    </div>

    <div class="print-header">
        <h1>Dental Clinic Reports</h1>
        <p class="mb-0">Generated: {{ now()->format('F d, Y h:i A') }}</p>
        <p class="mb-0">Period: {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</p>
    </div>

    <!-- Revenue Report -->
    <div class="report-section">
        <h3 class="mb-3">Revenue Report</h3>
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Revenue</h5>
                        <h2 class="text-success">${{ number_format($revenueData['total_revenue'], 2) }}</h2>
                        <p class="mb-0 small text-muted">{{ $revenueData['invoices_count'] }} invoices</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Paid Amount</h5>
                        <h2 class="text-primary">${{ number_format($revenueData['paid_amount'], 2) }}</h2>
                        <p class="mb-0 small text-muted">
                            {{ $revenueData['total_revenue'] > 0 ? number_format(($revenueData['paid_amount'] / $revenueData['total_revenue']) * 100, 1) : 0 }}% collected
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Remaining</h5>
                        <h2 class="text-warning">${{ number_format($revenueData['remaining_amount'], 2) }}</h2>
                        <p class="mb-0 small text-muted">Pending payment</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Avg Invoice</h5>
                        <h2>${{ $revenueData['invoices_count'] > 0 ? number_format($revenueData['total_revenue'] / $revenueData['invoices_count'], 2) : '0.00' }}</h2>
                        <p class="mb-0 small text-muted">Per invoice</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Report -->
    <div class="report-section">
        <h3 class="mb-3">Appointments Report</h3>
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total</h5>
                        <h2>{{ number_format($appointmentsData['total_appointments']) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Completed</h5>
                        <h2 class="text-success">{{ number_format($appointmentsData['completed_appointments']) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cancelled</h5>
                        <h2 class="text-danger">{{ number_format($appointmentsData['cancelled_appointments']) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Pending</h5>
                        <h2 class="text-warning">{{ number_format($appointmentsData['pending_appointments']) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Services -->
    <div class="report-section">
        <h3 class="mb-3">Top Services by Revenue</h3>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Service Name</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topServices as $index => $service)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $service->service_name }}</strong></td>
                        <td class="text-end">{{ number_format($service->total_quantity) }}</td>
                        <td class="text-end"><strong>${{ number_format($service->total_revenue, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No services data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Daily Revenue -->
    <div class="report-section">
        <h3 class="mb-3">Daily Revenue Breakdown</h3>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-center">Invoices</th>
                    <th class="text-end">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyRevenue as $day)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                        <td class="text-center">{{ $day->invoices_count }}</td>
                        <td class="text-end"><strong>${{ number_format($day->revenue, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No revenue data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Patients Report -->
    <div class="report-section">
        <h3 class="mb-3">Patients Growth Report</h3>
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">New Patients</h5>
                        <h2 class="text-success">{{ number_format($patientsData['new_patients']) }}</h2>
                        <p class="mb-0 small text-muted">In selected period</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Patients</h5>
                        <h2>{{ number_format($patientsData['total_patients']) }}</h2>
                        <p class="mb-0 small text-muted">All time</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Active Patients</h5>
                        <h2 class="text-primary">{{ number_format($patientsData['active_patients']) }}</h2>
                        <p class="mb-0 small text-muted">Currently active</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center text-muted mt-5">
        <small>&copy; {{ now()->year }} Dental Clinic System. All rights reserved.</small>
    </div>

    <script>
        // Auto-print when loaded
        // Uncomment the line below if you want auto-print
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>

