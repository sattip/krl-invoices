@extends('adminlte::page')

@section('title', 'Τεκμηρίωση API')

@section('content_header')
    <h1>Τεκμηρίωση API</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Introduction -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Εισαγωγή</h3>
                </div>
                <div class="card-body">
                    <p>Το API του InvoiceAI σας επιτρέπει να ανεβάζετε παραστατικά προγραμματιστικά, να ανακτάτε τα δεδομένα που εξήχθησαν και να διαγράφετε παραστατικά της εταιρείας σας. Όλα τα αιτήματα απαιτούν ταυτοποίηση με Bearer token και επιστρέφουν JSON.</p>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Σημαντικό:</strong> Τα δεδομένα εξάγονται αυτόματα με τεχνητή νοημοσύνη και ενδέχεται να περιέχουν λάθη ή παραλείψεις.
                        Πρέπει να ελέγχονται από εσάς ή τον λογιστή σας πριν χρησιμοποιηθούν σε λογιστικές εγγραφές ή φορολογικές δηλώσεις.
                    </div>

                    <h5 class="mt-4">Βασική διεύθυνση (Base URL)</h5>
                    <pre class="bg-light p-3 rounded"><code>{{ url('/api') }}</code></pre>

                    <h5 class="mt-4">Ταυτοποίηση</h5>
                    <p>Δημιουργήστε ένα token από τη σελίδα «API tokens» και συμπεριλάβετέ το στην κεφαλίδα Authorization κάθε αιτήματος. Το token αντιστοιχεί στον χρήστη που το δημιούργησε και δίνει πρόσβαση μόνο στα παραστατικά της εταιρείας του.</p>
                    <pre class="bg-light p-3 rounded"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>
                    <p>Συνιστάται να στέλνετε πάντα και την κεφαλίδα <code>Accept: application/json</code>, ώστε και τα μηνύματα σφάλματος να επιστρέφονται σε μορφή JSON.</p>

                    <a href="{{ route('api.tokens.index') }}" class="btn btn-primary">
                        <i class="fas fa-key mr-2"></i> Διαχείριση API tokens
                    </a>
                </div>
            </div>

            <!-- Current User -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">GET</span> /api/user
                    </h3>
                </div>
                <div class="card-body">
                    <p>Επιστρέφει τα στοιχεία του χρήστη στον οποίο ανήκει το token. Χρήσιμο για έλεγχο ότι το token είναι έγκυρο.</p>

                    <h6>Παράδειγμα αιτήματος</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X GET "{{ url('/api/user') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Παράδειγμα απάντησης (ενδεικτικά πεδία)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "id": 1,
  "company_id": 1,
  "name": "Γιώργος Παπαδόπουλος",
  "email": "user@example.com",
  "role": "owner"
}</code></pre>
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
                    <p>Επιστρέφει σελιδοποιημένη λίστα με τα παραστατικά της εταιρείας σας, από το νεότερο προς το παλαιότερο. Κάθε εγγραφή περιέχει τα βασικά αποθηκευμένα πεδία του παραστατικού (χωρίς γραμμές, εκπτώσεις και λοιπές χρεώσεις — για αυτά χρησιμοποιήστε το <code>GET /api/invoices/{id}</code>).</p>

                    <h6>Παράμετροι ερωτήματος (query)</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Παράμετρος</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>per_page</code></td>
                                <td>integer</td>
                                <td>Αριθμός αποτελεσμάτων ανά σελίδα (προεπιλογή: 15)</td>
                            </tr>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td>Αριθμός σελίδας</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Παράδειγμα αιτήματος</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X GET "{{ url('/api/invoices') }}?per_page=10" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Παράδειγμα απάντησης (συντομευμένη)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "invoice_number": "Α-001",
        "issuer_name": "Προμηθευτής Α.Ε.",
        "grand_total": "1250.00",
        "currency": "EUR",
        "created_at": "2026-01-15T10:30:00.000000Z"
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
                    <p>Ανεβάζει ένα παραστατικό και εξάγει αυτόματα τα δομημένα δεδομένα του (στοιχεία εκδότη και πελάτη, γραμμές, εκπτώσεις, λοιπές χρεώσεις, σύνολα). Απαιτείται ενεργή συνδρομή. Κάθε επιτυχημένη μεταφόρτωση προσμετράται στο μηνιαίο όριο του πακέτου σας.</p>

                    <h6>Σώμα αιτήματος (multipart/form-data)</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Πεδίο</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>file</code></td>
                                <td>file</td>
                                <td>Υποχρεωτικό. Αρχείο παραστατικού JPG, JPEG, PNG ή PDF, έως 10 MB.</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Παράδειγμα αιτήματος</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X POST "{{ url('/api/invoices') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json" \
  -F "file=@/path/to/invoice.pdf"</code></pre>

                    <h6>Παράδειγμα απάντησης (HTTP 201)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "message": "Το παραστατικό αναλύθηκε επιτυχώς",
  "data": {
    "id": 1,
    "invoice_number": "Α-001",
    "grand_total": 1250.00
  }
}</code></pre>

                    <h6>Πιθανά σφάλματα</h6>
                    <ul>
                        <li><strong>403</strong> με <code>"code": "no_subscription"</code> — δεν υπάρχει ενεργή συνδρομή.</li>
                        <li><strong>403</strong> με <code>"code": "invoice_limit_reached"</code> — συμπληρώθηκε το μηνιαίο όριο· η απάντηση περιλαμβάνει και το αντικείμενο <code>usage</code>.</li>
                        <li><strong>422</strong> — μη έγκυρο αρχείο ή αποτυχία επεξεργασίας του παραστατικού.</li>
                    </ul>
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
                    <p>Επιστρέφει ένα συγκεκριμένο παραστατικό με όλα τα δεδομένα που εξήχθησαν, μαζί με τις γραμμές, τις εκπτώσεις και τις λοιπές χρεώσεις.</p>

                    <h6>Παράμετροι διαδρομής (path)</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Παράμετρος</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>id</code></td>
                                <td>integer</td>
                                <td>Αναγνωριστικό (ID) παραστατικού</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Παράδειγμα αιτήματος</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X GET "{{ url('/api/invoices/1') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Παράδειγμα απάντησης</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "Α-001",
    "invoice_date": "2026-01-15",
    "issuer": {
      "name": "Προμηθευτής Α.Ε.",
      "vat_number": "EL123456789",
      "address": "Οδός Παραδείγματος 1, Αθήνα"
    },
    "customer": {
      "name": "Πελάτης Ε.Ε.",
      "vat_number": null,
      "address": "Οδός Δοκιμής 2, Θεσσαλονίκη"
    },
    "currency": "EUR",
    "line_items": [
      {
        "description": "Υπηρεσίες συμβούλου",
        "quantity": 10,
        "unit_price": 100.00,
        "vat_rate": 24,
        "line_total": 1000.00
      }
    ],
    "discounts": [],
    "other_charges": [
      {
        "description": "Μεταφορικά",
        "amount": 50.00
      }
    ],
    "totals": {
      "subtotal": 1000.00,
      "vat_total": 200.00,
      "grand_total": 1250.00
    },
    "file_url": "{{ url('/api/invoices/1/file') }}",
    "created_at": "2026-01-15T10:30:00+00:00"
  }
}</code></pre>
                </div>
            </div>

            <!-- Download original file -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">GET</span> /api/invoices/{id}/file
                    </h3>
                </div>
                <div class="card-body">
                    <p>Επιστρέφει το πρωτότυπο αρχείο (PDF/JPG/PNG) του παραστατικού. Απαιτεί το ίδιο Bearer token με τα υπόλοιπα endpoints·
                        τα αρχεία δεν είναι δημόσια προσβάσιμα. Το πεδίο <code>file_url</code> της απάντησης δείχνει σε αυτό το endpoint.</p>
<pre class="bg-dark text-light p-3 rounded"><code>curl -H "Authorization: Bearer YOUR_TOKEN" \
     -o invoice.pdf \
     {{ url('/api/invoices/1/file') }}</code></pre>
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
                    <p>Διαγράφει οριστικά ένα παραστατικό, τα δεδομένα του και το συνημμένο αρχείο. Η διαγραφή δεν μειώνει τη μηνιαία χρήση του πακέτου σας.</p>

                    <h6>Παράμετροι διαδρομής (path)</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Παράμετρος</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>id</code></td>
                                <td>integer</td>
                                <td>Αναγνωριστικό (ID) παραστατικού</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Παράδειγμα αιτήματος</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code>curl -X DELETE "{{ url('/api/invoices/1') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>

                    <h6>Παράδειγμα απάντησης</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "message": "Το παραστατικό διαγράφηκε"
}</code></pre>
                </div>
            </div>

            <!-- Error Responses -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Απαντήσεις σφάλματος</h3>
                </div>
                <div class="card-body">
                    <p>Το API χρησιμοποιεί τους τυπικούς κωδικούς κατάστασης HTTP και επιστρέφει μήνυμα σφάλματος σε JSON στο πεδίο <code>message</code>.</p>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Κωδικός</th>
                                <th>Περιγραφή</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-warning">401</span></td>
                                <td>Χωρίς ταυτοποίηση — το API token λείπει ή δεν είναι έγκυρο</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">403</span></td>
                                <td>Δεν επιτρέπεται — δεν υπάρχει ενεργή συνδρομή ή συμπληρώθηκε το μηνιαίο όριο (βλ. πεδίο <code>code</code>)</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">404</span></td>
                                <td>Δεν βρέθηκε — το παραστατικό δεν υπάρχει ή δεν ανήκει στην εταιρεία σας</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">422</span></td>
                                <td>Σφάλμα επικύρωσης ή επεξεργασίας — μη έγκυρα δεδομένα αιτήματος ή αποτυχία ανάλυσης του αρχείου</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-danger">500</span></td>
                                <td>Σφάλμα διακομιστή — παρουσιάστηκε απρόσμενο πρόβλημα</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6>Παράδειγμα απάντησης σφάλματος (HTTP 403)</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": false,
  "message": "Δεν υπάρχει ενεργή συνδρομή. Εγγραφείτε σε πακέτο για να ανεβάσετε παραστατικά.",
  "code": "no_subscription"
}</code></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Links -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Γρήγοροι σύνδεσμοι</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('api.tokens.index') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-key mr-2"></i> API tokens
                    </a>
                    <a href="{{ route('invoices.index') }}" class="btn btn-default btn-block mb-2">
                        <i class="fas fa-file-invoice mr-2"></i> Προβολή παραστατικών
                    </a>
                    <a href="{{ route('invoices.create') }}" class="btn btn-default btn-block">
                        <i class="fas fa-upload mr-2"></i> Μεταφόρτωση παραστατικού
                    </a>
                </div>
            </div>

            <!-- Code Examples -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Παραδείγματα κώδικα</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Ενδεικτικά παραδείγματα με κοινές βιβλιοθήκες HTTP. Δεν παρέχονται επίσημα SDK· το API καλείται απευθείας μέσω HTTP.</p>

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
