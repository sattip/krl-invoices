@extends('adminlte::page')

@section('title', 'API tokens')

@section('content_header')
    <h1>API tokens</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('new_token'))
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Το νέο σας API token</h5>
            <p>Αντιγράψτε το API token τώρα και φυλάξτε το σε ασφαλές σημείο. Για λόγους ασφαλείας δεν θα εμφανιστεί ξανά.</p>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('new_token') }}" id="new-token" readonly>
                <div class="input-group-append">
                    <button class="btn btn-warning" onclick="copyToClipboard()">
                        <i class="fas fa-copy mr-1"></i> Αντιγραφή
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <!-- Create Token -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Δημιουργία νέου token</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('api.tokens.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name">Όνομα token</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" required placeholder="π.χ. Διασύνδεση με το ERP μας">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i> Δημιουργία token
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Tokens -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Τα API tokens σας</h3>
                </div>
                <div class="card-body p-0">
                    @if ($tokens->isEmpty())
                        <p class="text-muted text-center py-4">Δεν έχετε δημιουργήσει ακόμη API tokens.</p>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Όνομα</th>
                                    <th>Δημιουργήθηκε</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tokens as $token)
                                    <tr>
                                        <td>
                                            <strong>{{ $token->name }}</strong>
                                            @if ($token->last_used_at)
                                                <br><small class="text-muted">Τελευταία χρήση: {{ date_el($token->last_used_at, true) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ date_el($token->created_at, true) }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('api.tokens.destroy', $token->id) }}" onsubmit="return confirm('Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το token; Οι εφαρμογές που το χρησιμοποιούν θα χάσουν την πρόσβαση.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Διαγραφή token">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Quick Reference -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Σύντομη αναφορά</h3>
                </div>
                <div class="card-body">
                    <p>Συμπεριλάβετε το token στην κεφαλίδα Authorization κάθε αιτήματος:</p>
                    <pre class="bg-light p-3 rounded"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>

                    <h6 class="mt-4">Διαθέσιμα endpoints</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge badge-success">GET</span> <code>/api/invoices</code></li>
                        <li><span class="badge badge-primary">POST</span> <code>/api/invoices</code></li>
                        <li><span class="badge badge-success">GET</span> <code>/api/invoices/{id}</code></li>
                        <li><span class="badge badge-danger">DELETE</span> <code>/api/invoices/{id}</code></li>
                        <li><span class="badge badge-success">GET</span> <code>/api/user</code></li>
                    </ul>

                    <a href="{{ route('api.documentation') }}" class="btn btn-info btn-block mt-3">
                        <i class="fas fa-book mr-2"></i> Πλήρης τεκμηρίωση API
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function copyToClipboard() {
        const tokenInput = document.getElementById('new-token');
        tokenInput.select();
        document.execCommand('copy');
        alert('Το token αντιγράφηκε στο πρόχειρο.');
    }
</script>
@stop
