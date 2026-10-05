<div class="card-soft p-3 mb-3 no-print">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="h6 fw-bold mb-0">{{ $slot ?? 'Daftar Data' }}</h3>
            <small class="text-muted">{{ $subtitle ?? '' }}</small>
        </div>
        <div class="d-flex gap-2">
            <input type="search" class="form-control form-control-sm" placeholder="Cari di tabel..."
                   data-table-search="#table-{{ $tableId ?? 'data' }}" style="min-width:190px">
            @isset($createUrl)
                <a href="{{ $createUrl }}" class="btn btn-sky btn-sm px-3">
                    <i class="bi bi-plus-lg"></i> {{ $createLabel ?? 'Tambah' }}
                </a>
            @endisset
        </div>
    </div>
</div>