@extends('adminlte::page')

@section('title', 'API Documentation')

@section('content_header')
    <h1>API Documentation</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Introduction -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Introduction</h3>
                </div>
                <div class="card-body">
                    <p>The InvoiceAI API allows you to programmatically upload invoices, retrieve parsed data, and manage your invoice collection. All API requests require authentication using a Bearer token.</p>

                    <h5 class="mt-4">Base URL</h5>
                    <pre class="bg-light p-3 rounded"><code>{{ url('/api') }}</code></pre>

                    <h5 class="mt-4">Authentication</h5>
                    <p>Include your API token in the Authorization header of every request:</p>
                    <pre class="bg-light p-3 rounded"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>

                    <a href="{{ route('api.tokens.index') }}" class="btn btn-primary">
                        <i class="fas fa-key mr-2"></i> Manage API Tokens
                    </a>
                </div>
            </div>

            <!-- List Invoices -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">GET</span> /api/invoices
                    </h3>
                </div>
                <div class="card-body">
                    <p>Retrieve a paginated list of all your invoices.</p>

                    <h6>Query Parameters</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>per_page</code></td>
                                <td>integer</td>
                                <td>Number of results per page (default: 15)</td>
                            </tr>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td>Page number</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Example Request</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X GET "{{ url('/api/invoices') }}?per_page=10" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Example Response</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "invoice_number": "INV-001",
        "issuer_name": "Acme Corp",
        "grand_total": "1250.00",
        "currency": "USD",
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "per_page": 15,
    "total": 1
  }
}</code></pre>
                </div>
            </div>

            <!-- Upload Invoice -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">POST</span> /api/invoices
                    </h3>
                </div>
                <div class="card-body">
                    <p>Upload and parse a new invoice. The AI will automatically extract all structured data.</p>

                    <h6>Request Body (multipart/form-data)</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>file</code></td>
                                <td>file</td>
                                <td>Invoice file (JPG, PNG, or PDF, max 10MB)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Example Request</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X POST "{{ url('/api/invoices') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json" \
  -F "file=@/path/to/invoice.pdf"</code></pre>

                    <h6>Example Response</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "message": "Invoice parsed successfully",
  "data": {
    "id": 1,
    "invoice_number": "INV-001",
    "grand_total": 1250.00
  }
}</code></pre>
                </div>
            </div>

            <!-- Get Invoice -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">GET</span> /api/invoices/{id}
                    </h3>
                </div>
                <div class="card-body">
                    <p>Retrieve a specific invoice with all extracted data including line items, discounts, and charges.</p>

                    <h6>Path Parameters</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>id</code></td>
                                <td>integer</td>
                                <td>Invoice ID</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Example Request</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X GET "{{ url('/api/invoices/1') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Example Response</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "INV-001",
    "invoice_date": "2024-01-15",
    "issuer": {
      "name": "Acme Corp",
      "vat_number": "US123456789",
      "address": "123 Main St, City"
    },
    "customer": {
      "name": "John Doe",
      "vat_number": null,
      "address": "456 Oak Ave"
    },
    "currency": "USD",
    "line_items": [
      {
        "description": "Consulting Services",
        "quantity": 10,
        "unit_price": 100.00,
        "vat_rate": 20,
        "line_total": 1000.00
      }
    ],
    "discounts": [],
    "other_charges": [
      {
        "description": "Shipping",
        "amount": 50.00
      }
    ],
    "totals": {
      "subtotal": 1000.00,
      "vat_total": 200.00,
      "grand_total": 1250.00
    },
    "file_url": "/storage/invoices/abc123.pdf",
    "created_at": "2024-01-15T10:30:00+00:00"
  }
}</code></pre>
                </div>
            </div>

            <!-- Delete Invoice -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">DELETE</span> /api/invoices/{id}
                    </h3>
                </div>
                <div class="card-body">
                    <p>Delete an invoice and its associated file.</p>

                    <h6>Path Parameters</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>id</code></td>
                                <td>integer</td>
                                <td>Invoice ID</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Example Request</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X DELETE "{{ url('/api/invoices/1') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Example Response</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "message": "Invoice deleted successfully"
}</code></pre>
                </div>
            </div>

            <!-- Error Responses -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Error Responses</h3>
                </div>
                <div class="card-body">
                    <p>The API returns standard HTTP status codes and JSON error messages.</p>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-warning">401</span></td>
                                <td>Unauthenticated - Invalid or missing API token</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">404</span></td>
                                <td>Not Found - Invoice doesn't exist or doesn't belong to you</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">422</span></td>
                                <td>Validation Error - Invalid request data</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-danger">500</span></td>
                                <td>Server Error - Something went wrong</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Example Error Response</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": false,
  "message": "Invoice not found"
}</code></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Links -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Quick Links</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('api.tokens.index') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-key mr-2"></i> API Tokens
                    </a>
                    <a href="{{ route('invoices.index') }}" class="btn btn-default btn-block mb-2">
                        <i class="fas fa-file-invoice mr-2"></i> View Invoices
                    </a>
                    <a href="{{ route('invoices.create') }}" class="btn btn-default btn-block">
                        <i class="fas fa-upload mr-2"></i> Upload Invoice
                    </a>
                </div>
            </div>

            <!-- Code Examples -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Code Examples</h3>
                </div>
                <div class="card-body">
                    <h6>PHP (Guzzle)</h6>
                    <pre class="bg-light p-3 rounded small"><code>$client = new GuzzleHttp\Client();
$response = $client->post('{{ url('/api/invoices') }}', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
    ],
    'multipart' => [
        [
            'name' => 'file',
            'contents' => fopen('/path/to/invoice.pdf', 'r'),
        ],
    ],
]);</code></pre>

                    <h6 class="mt-3">JavaScript (Fetch)</h6>
                    <pre class="bg-light p-3 rounded small"><code>const formData = new FormData();
formData.append('file', fileInput.files[0]);

fetch('{{ url('/api/invoices') }}', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
    },
    body: formData
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>

                    <h6 class="mt-3">Python (requests)</h6>
                    <pre class="bg-light p-3 rounded small"><code>import requests

headers = {
    'Authorization': f'Bearer {token}'
}

files = {
    'file': open('/path/to/invoice.pdf', 'rb')
}

response = requests.post(
    '{{ url('/api/invoices') }}',
    headers=headers,
    files=files
)</code></pre>
                </div>
            </div>
        </div>
    </div>
@stop
