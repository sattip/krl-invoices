@extends('adminlte::page')

@section('title', 'API Tokens')

@section('content_header')
    <h1>API Tokens</h1>
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
            <h5><i class="icon fas fa-exclamation-triangle"></i> Your New API Token</h5>
            <p>Make sure to copy your API token now. You won't be able to see it again!</p>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('new_token') }}" id="new-token" readonly>
                <div class="input-group-append">
                    <button class="btn btn-warning" onclick="copyToClipboard()">
                        <i class="fas fa-copy mr-1"></i> Copy
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
                    <h3 class="card-title">Create New Token</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('api.tokens.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name">Token Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" required placeholder="e.g., My App Integration">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i> Create Token
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Tokens -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your API Tokens</h3>
                </div>
                <div class="card-body p-0">
                    @if ($tokens->isEmpty())
                        <p class="text-muted text-center py-4">No API tokens created yet.</p>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tokens as $token)
                                    <tr>
                                        <td>
                                            <strong>{{ $token->name }}</strong>
                                            @if ($token->last_used_at)
                                                <br><small class="text-muted">Last used {{ $token->last_used_at->diffForHumans() }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $token->created_at->diffForHumans() }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('api.tokens.destroy', $token->id) }}" onsubmit="return confirm('Are you sure you want to delete this token?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
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
                    <h3 class="card-title">Quick Reference</h3>
                </div>
                <div class="card-body">
                    <p>Include your token in the Authorization header:</p>
                    <pre class="bg-light p-3 rounded"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>

                    <h6 class="mt-4">Available Endpoints</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge badge-success">GET</span> <code>/api/invoices</code></li>
                        <li><span class="badge badge-primary">POST</span> <code>/api/invoices</code></li>
                        <li><span class="badge badge-success">GET</span> <code>/api/invoices/{id}</code></li>
                        <li><span class="badge badge-danger">DELETE</span> <code>/api/invoices/{id}</code></li>
                    </ul>

                    <a href="{{ route('api.documentation') }}" class="btn btn-info btn-block mt-3">
                        <i class="fas fa-book mr-2"></i> View Full Documentation
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
        alert('Token copied to clipboard!');
    }
</script>
@stop
