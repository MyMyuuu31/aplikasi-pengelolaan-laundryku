@if (session('success') || session('error') || session('warning'))
    <div class="position-fixed top-0 end-0 p-3 d-flex flex-column gap-2 no-print" style="z-index:1080;max-width:min(420px,92vw)">
        @if (session('success'))
            <div class="toast-sky show" role="alert" data-auto-dismiss="5000">
                <div class="d-flex align-items-start gap-2 p-3">
                    <i class="bi bi-check-circle-fill fs-5" style="color:#22c55e"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Berhasil</div>
                        <div class="small">{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast-sky toast-error show" role="alert" data-auto-dismiss="6000">
                <div class="d-flex align-items-start gap-2 p-3">
                    <i class="bi bi-x-octagon-fill fs-5" style="color:#ef4444"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Gagal</div>
                        <div class="small">{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="toast-sky toast-warning show" role="alert" data-auto-dismiss="6000">
                <div class="d-flex align-items-start gap-2 p-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5" style="color:#f59e0b"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Perhatian</div>
                        <div class="small">{{ session('warning') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            </div>
        @endif
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 fade-up no-print" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <div class="fw-bold">Periksa kembali isian Anda</div>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif