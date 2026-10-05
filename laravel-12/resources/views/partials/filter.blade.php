<div class="card-soft p-3 mb-3 no-print">
    <form method="GET" action="{{ $action }}" class="row g-2 align-items-end">
        @foreach ($fields as $f)
            @if (($f['type'] ?? 'text') === 'select')
                <div class="{{ $f['col'] ?? 'col-12 col-md-3' }}">
                    <label class="form-label" for="filter-{{ $f['name'] }}">{{ $f['label'] }}</label>
                    <select class="form-select form-select-sm" id="filter-{{ $f['name'] }}" name="{{ $f['name'] }}"
                            data-auto-submit>
                        <option value="">Semua</option>
                        @foreach ($f['options'] as $value => $label)
                            <option value="{{ $value }}" @selected(request($f['name']) == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif (($f['type'] ?? 'text') === 'date')
                <div class="{{ $f['col'] ?? 'col-6 col-md-2' }}">
                    <label class="form-label" for="filter-{{ $f['name'] }}">{{ $f['label'] }}</label>
                    <input type="date" class="form-control form-control-sm" id="filter-{{ $f['name'] }}"
                           name="{{ $f['name'] }}" value="{{ request($f['name']) }}">
                </div>
            @else
                <div class="{{ $f['col'] ?? 'col-12 col-md-4' }}">
                    <label class="form-label" for="filter-{{ $f['name'] }}">{{ $f['label'] }}</label>
                    <input type="text" class="form-control form-control-sm" id="filter-{{ $f['name'] }}"
                           name="{{ $f['name'] }}" value="{{ request($f['name']) }}"
                           placeholder="{{ $f['placeholder'] ?? 'Cari...' }}">
                </div>
            @endif
        @endforeach

        <div class="col-12 col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-sky btn-sm px-3">
                <i class="bi bi-funnel"></i> Terapkan
            </button>
            <a href="{{ $action }}" class="btn btn-outline-sky btn-sm px-3">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </a>
            @isset($exportUrl)
                <a href="{{ $exportUrl }}" class="btn btn-outline-sky btn-sm px-3">
                    <i class="bi bi-filetype-csv"></i> Export CSV
                </a>
            @endisset
            @isset($printUrl)
                <button type="button" class="btn btn-outline-sky btn-sm px-3" onclick="window.open('{{ $printUrl }}','_blank')">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            @endisset
        </div>
    </form>
</div>