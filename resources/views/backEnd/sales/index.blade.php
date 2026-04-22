@extends('backEnd.layouts.master')
@section('title', 'Sales Management')

@push('css')
    <link rel="stylesheet" href="{{ asset('backEnd/plugins/select2/select2.min.css') }}">
    <style>
        .select2-container--open {
            z-index: 100000 !important;
        }
    </style>
@endpush

@section('content')

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">Sales Management</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Sales</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header justify-content-between d-flex align-items-center">
                    <div class="card-title">Sales List</div>
                    <button class="btn btn-primary btn-sm" id="createSaleBtn">Create Sale</button>
                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th style="width:1%">SL</th>
                                    <th style="width:1%">Invoice</th>
                                    <th>Customer Info</th>
                                    <th>Package Info</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($sales as $key => $sale)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <span
                                                class="badge bg-success-transparent mb-2">{{ ucfirst($sale->plan->billing_cycle) }}</span><br>
                                            <strong>{{ $sale->invoice_number }}</strong>
                                        </td>
                                        <td>
                                            <strong>Name:</strong> {{ $sale->user->name }}<br>
                                            <strong>Email:</strong> {{ $sale->user->email }}<br>
                                            <strong>Phone:</strong> {{ $sale->user->phone }}
                                        </td>
                                        <td>
                                            <strong>Package:</strong> {{ $sale->plan->name }}<br>
                                            <strong>Start Date:</strong> {{ date('d-m-Y', strtotime($sale->start_date)) }} <br>
                                            <strong>End Date:</strong> {{ date('d-m-Y', strtotime($sale->end_date)) }}<br>
                                            <strong>Price:</strong> {{ $sale->plan->price }}
                                        </td>
                                        <td>
                                            {{-- @if ($sale->domains->isNotEmpty())
                                                @foreach ($sale->domains as $key => $domain)
                                                    <strong>{{ ++$key }}.
                                                        {{ ucfirst(strtolower($domain->domain_name)) }}</strong><br>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="ml-2 text-info">Requests:
                                                        {{ number_format($domain->total_requests) }}</span><br>
                                                @endforeach
                                            @endif --}}
                                            <strong>Allowed Requests:</strong> {{ number_format($sale->allowed_requests) }}<br>
                                            <strong>Used Requests:</strong> {{ number_format($sale->requests_count) }}<br>
                                            <strong>Allowed:</strong> {{ $sale->allowed_domains }} Domains<br>
                                            <strong>Used:</strong> {{ $sale->domains_count }} Domains
                                        </td>
                                        <td>
                                            <span class="badge
                                                {{ $sale->status == 'expired' ? 'bg-danger' : ($sale->status == 'active' ? 'bg-success' : 'bg-warning') }} mb-2">
                                                {{ ucfirst($sale->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-sm upgradePlanBtn"
                                                data-id="{{ $sale->id }}" data-end_date="{{ $sale->end_date }}"
                                                data-allowed_domains="{{ $sale->allowed_domains }}"
                                                data-allowed_requests="{{ $sale->allowed_requests }}"
                                                data-status="{{ $sale->status }}">
                                                Upgrade
                                            </button>


                                            <button class="btn btn-outline-secondary btn-sm editSaleBtn"
                                                data-id="{{ $sale->id }}" data-client="{{ $sale->user_id }}"
                                                data-package="{{ $sale->plan_id }}"
                                                data-features="{{ $sale->plan->features }}">
                                                Edit
                                            </button>

                                            <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Delete this sale?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No sale found.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ONE MODAL – USED FOR BOTH CREATE & EDIT -->
    <div class="modal fade" id="saleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle">Create Sale</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="saleForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="sale_id">

                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- Client Select -->
                            <div class="col-md-12">
                                <label class="form-label">Client</label>
                                <select name="client_id" id="client_id" class="form-select select2" required>
                                    <option value="">Select Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">
                                            {{ $client->name }} ({{ $client->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Package Select -->
                            <div class="col-md-12">
                                <label class="form-label">Package</label>
                                <select name="package_id" id="package_id" class="form-select select2 package-change"
                                    required>
                                    <option value="">Select Package</option>
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}" data-features="{{ $package->features }}">
                                            {{ $package->name }} ({{ $package->price }})
                                        </option>
                                    @endforeach
                                </select>

                                <ul id="features_list" class="mt-3"></ul>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="submitBtn" type="submit" class="btn btn-primary">Create</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Upgrade Plan Modal -->
    <div class="modal fade" id="upgradeSaleModal" tabindex="-1" aria-labelledby="upgradeSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="upgradeSaleForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="upgradeSaleModalLabel">Upgrade Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="upgradeSaleId" name="sale_id">

                        <div class="mb-3">
                            <label for="upgradeEndDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="upgradeEndDate" name="end_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="upgradeAllowedRequests" class="form-label">Allowed Requests</label>
                            <input type="number" class="form-control" id="upgradeAllowedRequests" name="allowed_requests"
                                min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="upgradeAllowedDomains" class="form-label">Allowed Domains</label>
                            <input type="number" class="form-control" id="upgradeAllowedDomains" name="allowed_domains"
                                min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="upgradeStatus" class="form-label">Status</label>
                            <select name="status" id="upgradeStatus" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upgrade Plan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('js')
    <script src="{{ asset('backEnd/plugins/select2/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            function initSaleModalSelect2() {
                $('#client_id, #package_id').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }

                    $(this).select2({
                        width: '100%',
                        dropdownParent: $('#saleModal')
                    });
                });
            }

            initSaleModalSelect2();

            function renderFeatures(featuresJSON) {
                let list = $('#features_list');
                list.html('');
                if (!featuresJSON) return;

                let features = [];
                try {
                    features = (typeof featuresJSON === "string") ? JSON.parse(featuresJSON) : featuresJSON;
                } catch (e) {
                    console.error("Invalid JSON:", featuresJSON);
                    return;
                }

                let html = '';
                features.forEach(item => {
                    if (item.is_active == 1) html += `<li>${item.text}</li>`;
                });
                list.html(html);
            }

            // CREATE MODE
            $('#createSaleBtn').on('click', function() {
                $('#modalTitle').text('Create Sale');
                $('#submitBtn').text('Create');
                $('#saleForm').attr('action', '{{ route('admin.sales.store') }}');
                $('#saleForm input[name="_method"]').remove();

                $('#sale_id').val('');
                $('#client_id').val('').trigger('change');
                $('#package_id').val('').trigger('change');
                $('#features_list').html('');

                $('#saleModal').modal('show');
            });

            // EDIT MODE
            $('.editSaleBtn').on('click', function() {
                let id = $(this).data('id');
                let client = $(this).data('client');
                let packageId = $(this).data('package');
                let features = $(this).data('features');

                $('#modalTitle').text('Edit Sale');
                $('#submitBtn').text('Update');

                // Set action using route name
                let updateUrl = '{{ route('admin.sales.update', ':id') }}';
                updateUrl = updateUrl.replace(':id', id);
                $('#saleForm').attr('action', updateUrl);

                // Add PUT method for update
                if ($('#saleForm input[name="_method"]').length === 0) {
                    $('#saleForm').append('<input type="hidden" name="_method" value="PUT">');
                } else {
                    $('#saleForm input[name="_method"]').val('PUT');
                }

                $('#sale_id').val(id);
                $('#client_id').val(client).trigger('change');
                $('#package_id').val(packageId).trigger('change');

                renderFeatures(features);

                $('#saleModal').modal('show');
            });

            // Refresh Select2 on modal open
            $('#saleModal').on('shown.bs.modal', function() {
                initSaleModalSelect2();
                $('#client_id').trigger('change.select2');
                $('#package_id').trigger('change.select2');
            });

            // PACKAGE CHANGE
            $('.package-change').on('change', function() {
                let features = $(this).find(':selected').data('features');
                renderFeatures(features);
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            // 1️⃣ Open modal and pre-fill fields
            $('.upgradePlanBtn').on('click', function() {
                let id = $(this).data('id');
                let endDate = $(this).data('end_date');
                let allowedRequests = $(this).data('allowed_requests');
                let allowedDomains = $(this).data('allowed_domains');
                let status = $(this).data('status');

                $('#upgradeSaleId').val(id);
                $('#upgradeEndDate').val(endDate);
                $('#upgradeAllowedRequests').val(allowedRequests);
                $('#upgradeAllowedDomains').val(allowedDomains);
                $('#upgradeStatus').val(status);

                $('#upgradeSaleModal').modal('show'); // Bootstrap 5 modal
            });

            // 2️⃣ Handle form submission via AJAX
            $('#upgradeSaleForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#upgradeSaleId').val();
                let formData = $(this).serialize();
                let url = "{{ route('admin.sales.upgrade', ':id') }}".replace(':id', id);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.status) {
                            // alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message || 'Upgrade failed');
                        }
                    },
                    error: function(xhr) {
                        alert('Something went wrong! ' + xhr.responseText);
                    }
                });
            });

        });
    </script>
@endpush
