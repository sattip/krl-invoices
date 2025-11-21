@if(session()->has('impersonator_id'))
    <div class="alert alert-warning alert-dismissible mb-0" style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999; border-radius: 0;">
        <div class="container-fluid">
            <i class="fas fa-user-secret mr-2"></i>
            <strong>Impersonating:</strong> {{ auth()->user()->name }} ({{ auth()->user()->email }})
            <form action="{{ route('stop-impersonating') }}" method="POST" class="d-inline ml-3">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-sign-out-alt mr-1"></i> Stop Impersonating
                </button>
            </form>
            <span class="float-right text-muted">
                Logged in as: {{ session('impersonator_name') }}
            </span>
        </div>
    </div>
    <div style="height: 56px;"></div> {{-- Spacer to push content down --}}
@endif
