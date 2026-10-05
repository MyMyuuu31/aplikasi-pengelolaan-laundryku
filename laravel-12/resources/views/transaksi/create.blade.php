@extends('layouts.app')

@section('title', 'Entri Laundry Baru')
@section('page-title', 'Entri Laundry Baru')
@section('page-subtitle', 'Nomor invoice dibuat otomatis oleh sistem')

@section('content')
    <form method="POST" action="{{ route('transaksi.store') }}" id="formTransaksi">
        @csrf

        <div class="row g-3">
            {{-- Formulir paket --}}
            <div class="col-12 col-xl-8">
                <div class="card-soft p-3 p-lg-4 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h3 class="h6 fw-bold mb-0"><i class="bi bi-basket text-sky"></i> Item Laundry</h3>
                        <span class="badge rounded-pill text-bg-light border">{{ count($pakets) }} paket tersedia</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sky align-middle" id="tabelItem">
                            <thead>
                            <tr>
                                <th>Paket / Layanan</th>
                                <th style="width:130px">Harga</th>
                                <th style="width:110px">Qty</th>
                                <th style="width:150px" class="text-end">Subtotal</th>
                                <th style="width:60px"></th>
                            </tr>
                            </thead>
                            <tbody id="barisItem"></tbody>
                        </table>
                    </div>

                    <div id="itemKosong" class="text-center text-muted py-4">
                        <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                        Belum ada item. Pilih paket lalu tekan <strong>Tambah Item</strong>.
                    </div>

                    <button type="button" class="btn btn-outline-sky btn-sm mt-2" id="btnTambahItem">
                        <i class="bi bi-plus-lg"></i> Tambah Item
                    </button>
                </div>

                {{-- Ringkasan pembayaran --}}
                <div class="card-soft p-3 p-lg-4">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-calculator text-sky"></i> Ringkasan Pembayaran</h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="diskon_persen">Diskon (%)</label>
                            <input type="number" class="form-control" id="diskon_persen" name="diskon_persen"
                                   min="0" max="100" step="0.5" value="{{ old('diskon_persen', $diskonPersen) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pajak_persen">Pajak {{ config('laundry.pajak.nama') }} (%)</label>
                            <input type="number" class="form-control" id="pajak_persen" name="pajak_persen"
                                   min="0" max="100" step="0.5" value="{{ old('pajak_persen', $pajakPersen) }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <td class="text-muted">Total Harga</td>
                                <td class="text-end fw-semibold" id="totalHarga">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Diskon (<span id="labelDiskon">0</span>%)</td>
                                <td class="text-end fw-semibold text-danger" id="nilaiDiskon">- Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Pajak (<span id="labelPajak">0</span>%)</td>
                                <td class="text-end fw-semibold" id="nilaiPajak">Rp 0</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold">Total Bayar</td>
                                <td class="text-end fw-bold fs-5 text-sky" id="totalBayar">Rp 0</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Info transaksi --}}
            <div class="col-12 col-xl-4">
                <div class="card-soft p-3 p-lg-4 mb-3">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-info-circle text-sky"></i> Info Transaksi</h3>

                    <label class="form-label" for="id_member">Member</label>
                    <input type="hidden" name="id_member" id="id_member" value="{{ old('id_member') }}">
                    <input type="text" class="form-control mb-2" id="cariMember" autocomplete="off"
                           placeholder="Ketik nama / kode member...">
                    <div class="list-group position-absolute w-100 shadow d-none" id="hasilMember"
                         style="z-index:1050;max-height:240px;overflow:auto;margin-top:-8px"></div>
                    <div id="infoMember" class="small text-muted mt-2"></div>

                    <label class="form-label mt-3" for="metode_bayar">Metode Pembayaran</label>
                    <select class="form-select" id="metode_bayar" name="metode_bayar">
                        @foreach (config('laundry.transaksi.metode_bayar') as $m)
                            <option value="{{ $m }}" @selected(old('metode_bayar', 'tunai') === $m)>
                                {{ ucfirst($m) === 'Qris' ? 'QRIS' : ucfirst($m) }}
                            </option>
                        @endforeach
                    </select>

                    <label class="form-label mt-3" for="status">Status Laundry</label>
                    <select class="form-select" id="status" name="status">
                        @foreach (config('laundry.transaksi.status') as $key => $meta)
                            @if ($key !== 'dibatalkan')
                                <option value="{{ $key }}" @selected(old('status', 'proses') === $key)>{{ $meta['label'] }}</option>
                            @endif
                        @endforeach
                    </select>

                    <label class="form-label mt-3" for="uang_diterima">Uang Diterima (Rp)</label>
                    <input type="number" class="form-control" id="uang_diterima" name="uang_diterima" min="0" step="100"
                           value="{{ old('uang_diterima') }}" placeholder="0">

                    <div class="alert alert-info mt-3 py-2 mb-0 small d-flex justify-content-between">
                        <span>Kembalian</span>
                        <b id="nilaiKembali">Rp 0</b>
                    </div>

                    <label class="form-label mt-3" for="catatan">Catatan</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="2"
                              placeholder="Contoh: kirim sebelum jam 5 sore">{{ old('catatan') }}</textarea>
                </div>

                <div class="card-soft p-3 p-lg-4">
                    <button class="btn btn-sky w-100 py-2">
                        <i class="bi bi-check2-circle"></i> Simpan Transaksi
                    </button>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-outline-sky w-100 mt-2">Batal</a>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="bi bi-shield-check"></i>
                        Harga paket, diskon, dan pajak dihitung di server sehingga tidak dapat dimanipulasi dari browser.
                    </p>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @php
        $paketJson = $pakets->map(fn ($p) => [
            'id' => $p->id_paket,
            'nama' => $p->nama_paket,
            'satuan' => $p->satuan,
            'harga' => (float) $p->harga,
            'estimasi' => $p->estimasi_hari,
        ])->values();
    @endphp
    <script>
        const PAKET = @json($paketJson);

        const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(Math.round(n || 0));
        const tbody = document.getElementById('barisItem');
        const itemKosong = document.getElementById('itemKosong');

        function barisItem(paketId) {
            const p = PAKET.find((x) => String(x.id) === String(paketId)) || PAKET[0];
            if (!p) return '';

            const tr = document.createElement('tr');
            tr.dataset.harga = p.harga;
            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm" name="items[${counter}][id_paket]">
                        ${PAKET.map((x) => `<option value="${x.id}" ${x.id === p.id ? 'selected' : ''}>${x.nama} (${x.satuan})</option>`).join('')}
                    </select>
                    <div class="text-muted" style="font-size:.72rem">Estimasi ${p.estimasi} hari</div>
                </td>
                <td class="text-end fw-semibold">${rupiah(p.harga)}</td>
                <td><input type="number" class="form-control form-control-sm qty" name="items[${counter}][qty]"
                           value="1" min="0.01" step="0.01"></td>
                <td class="text-end fw-bold subtotal">${rupiah(p.harga)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
            counter++;
            tbody.appendChild(tr);
        }

        let counter = 0;

        function hapusBaris(btn) {
            btn.closest('tr').remove();
            hitungTotal();
        }

        function hitungTotal() {
            let totalHarga = 0;
            tbody.querySelectorAll('tr').forEach((tr) => {
                const select = tr.querySelector('select');
                const p = PAKET.find((x) => String(x.id) === String(select.value));
                tr.dataset.harga = p ? p.harga : 0;
                const qty = parseFloat(tr.querySelector('.qty').value) || 0;
                const sub = (p ? p.harga : 0) * qty;
                tr.querySelector('.subtotal').textContent = rupiah(sub);
                totalHarga += sub;
            });

            const diskonPersen = parseFloat(document.getElementById('diskon_persen').value) || 0;
            const pajakPersen = parseFloat(document.getElementById('pajak_persen').value) || 0;
            const diskon = totalHarga * diskonPersen / 100;
            const pajak = (totalHarga - diskon) * pajakPersen / 100;
            const totalBayar = totalHarga - diskon + pajak;

            document.getElementById('totalHarga').textContent = rupiah(totalHarga);
            document.getElementById('labelDiskon').textContent = diskonPersen;
            document.getElementById('nilaiDiskon').textContent = '- ' + rupiah(diskon);
            document.getElementById('labelPajak').textContent = pajakPersen;
            document.getElementById('nilaiPajak').textContent = rupiah(pajak);
            document.getElementById('totalBayar').textContent = rupiah(totalBayar);

            const uang = parseFloat(document.getElementById('uang_diterima').value) || 0;
            document.getElementById('nilaiKembali').textContent = rupiah(uang - totalBayar);

            itemKosong.style.display = tbody.children.length ? 'none' : '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (!PAKET.length) {
                document.getElementById('btnTambahItem').disabled = true;
                itemKosong.innerHTML = '<i class="bi bi-exclamation-triangle fs-2 d-block mb-2 text-warning"></i>' +
                    'Belum ada paket aktif. Tambahkan data paket terlebih dahulu.';
            } else {
                barisItem(PAKET[0].id);
            }

            document.getElementById('btnTambahItem').addEventListener('click', () => barisItem(PAKET[0].id));

            tbody.addEventListener('input', hitungTotal);
            tbody.addEventListener('change', hitungTotal);
            ['diskon_persen', 'pajak_persen', 'uang_diterima'].forEach((id) => {
                document.getElementById(id).addEventListener('input', hitungTotal);
            });

            document.getElementById('uang_diterima').addEventListener('focus', function () {
                if (!this.value) this.value = document.getElementById('totalBayar').textContent.replace(/[^0-9]/g, '');
            });

            /* Pencarian member */
            const cariMember = document.getElementById('cariMember');
            const hasilMember = document.getElementById('hasilMember');

            cariMember.addEventListener('input', async function () {
                const q = this.value.trim();
                if (q.length < 2) { hasilMember.classList.add('d-none'); return; }

                const res = await fetch(`{{ route('api.member-cari') }}?q=${encodeURIComponent(q)}`);
                const data = await res.json();

                if (!data.length) { hasilMember.classList.add('d-none'); return; }

                hasilMember.innerHTML = data.map((m) => `
                    <button type="button" class="list-group-item list-group-item-action"
                            data-id="${m.id_member}" data-nama="${m.nama}" data-poin="${m.poin}">
                        <div class="fw-semibold">${m.nama}</div>
                        <div class="small text-muted">${m.kode_member} ${m.no_hp ? '&middot; ' + m.no_hp : ''}</div>
                    </button>`).join('');
                hasilMember.classList.remove('d-none');
            });

            hasilMember.addEventListener('click', function (e) {
                const btn = e.target.closest('button[data-id]');
                if (!btn) return;
                document.getElementById('id_member').value = btn.dataset.id;
                cariMember.value = btn.dataset.nama;
                document.getElementById('infoMember').innerHTML =
                    `<i class="bi bi-check-circle-fill text-success"></i> ${btn.dataset.nama} &middot; ${btn.dataset.poin} poin`;
                hasilMember.classList.add('d-none');
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#hasilMember') && e.target !== cariMember) {
                    hasilMember.classList.add('d-none');
                }
            });

            hitungTotal();
        });
    </script>
@endpush