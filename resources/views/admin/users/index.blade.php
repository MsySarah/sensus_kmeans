@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-users-cog text-primary me-2"></i>Manajemen User
        </h3>
        <button class="btn btn-primary shadow-sm fw-bold"
                data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fas fa-user-plus me-1"></i> Tambah User
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3">{{ session('error') }}</div>
    @endif

    {{-- ── Filter ──────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter Pencarian Petugas</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('users.index') }}" method="GET">
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari Nama / Username..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">-- Semua Jabatan --</option>
                            @foreach(['pimpinan'=>'Pimpinan','admin'=>'Admin','pengawas_kab'=>'Pengawas Kab','koseka'=>'Koseka','pml'=>'PML'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ request('role')==$val?'selected':'' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2">
                    {{-- Kabupaten --}}
                    <div class="col-md-3">
                        <select name="f_kab" id="f_kab" class="form-select form-select-sm">
                            <option value="">-- Pilih Kabupaten --</option>
                            @foreach($kabupaten as $kab)
                                <option value="{{ $kab->kode_kab }}"
                                    {{ request('f_kab')==$kab->kode_kab?'selected':'' }}>
                                    {{ $kab->nama_kab }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kecamatan — repopulate dari server jika filter aktif --}}
                    <div class="col-md-3">
                        <select name="f_kec" id="f_kec" class="form-select form-select-sm">
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach($kecamatans as $kc)
                                <option value="{{ $kc->id }}"
                                    {{ request('f_kec')==$kc->id?'selected':'' }}>
                                    {{ $kc->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Desa — repopulate dari server jika filter aktif --}}
                    <div class="col-md-3">
                        <select name="f_desa" id="f_desa" class="form-select form-select-sm">
                            <option value="">-- Pilih Desa --</option>
                            @foreach($desas as $ds)
                                <option value="{{ $ds->id }}"
                                    {{ request('f_desa')==$ds->id?'selected':'' }}>
                                    {{ $ds->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('users.index') }}"
                           class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tabel ───────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4">NAMA & USERNAME</th>
                            <th class="text-center">JABATAN</th>
                            <th>WILAYAH TUGAS</th>
                            <th class="text-center pe-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $u->name }}</div>
                                <div class="small text-muted">{{ $u->username }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    $roleColor = [
                                        'admin'        => 'bg-dark',
                                        'pimpinan'     => 'bg-primary',
                                        'pengawas_kab' => 'bg-info text-dark',
                                        'koseka'       => 'bg-warning text-dark',
                                        'pml'          => 'bg-success',
                                    ][$u->role] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $roleColor }} text-uppercase"
                                      style="font-size:0.65rem;">{{ $u->role }}</span>
                            </td>
                            <td>
                                @if($u->kode_wilayah_tugas)
                                    <div class="small fw-bold text-primary">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $u->kode_wilayah_tugas }}
                                    </div>
                                    <div class="small text-muted" style="font-size:0.72rem;">
                                        @if($u->nama_wilayah)
                                            {{ $u->nama_wilayah }}
                                            @if($u->nama_kec) · Kec. {{ $u->nama_kec }} @endif
                                            @if($u->nama_kab) · {{ $u->nama_kab }} @endif
                                        @else
                                            <em class="text-warning">Nama wilayah tidak ditemukan</em>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">SEMUA WILAYAH</span>
                                @endif
                            </td>
                            
                            
                            <td class="text-center pe-4">
                                <button class="btn btn-sm btn-outline-info border-0"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetail-{{ $u->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning border-0"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit-{{ $u->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('users.destroy', $u->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger border-0">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal Detail --}}
                        <div class="modal fade" id="modalDetail-{{ $u->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h6 class="modal-title fw-bold">Detail: {{ $u->name }}</h6>
                                        <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-2 small">
                                            <div class="col-4 text-muted">Username</div>
                                            <div class="col-8 fw-bold">{{ $u->username }}</div>
                                            <div class="col-4 text-muted">Jabatan</div>
                                            <div class="col-8">
                                                <span class="badge {{ $roleColor }} text-uppercase">
                                                    {{ $u->role }}
                                                </span>
                                            </div>
                                            <div class="col-4 text-muted">Kode Wilayah</div>
                                            <div class="col-8 fw-bold text-primary">
                                                {{ $u->kode_wilayah_tugas ?? '-' }}
                                            </div>
                                            <div class="col-4 text-muted">Nama Wilayah</div>
                                            <div class="col-8">
                                                {{ $u->nama_sls ?? $u->nama_desa ?? $u->nama_kec ?? $u->nama_kab ?? 'Seluruh Wilayah' }}
                                            </div>
                                            @if($u->nama_kec)
                                            <div class="col-4 text-muted">Kecamatan</div>
                                            <div class="col-8">{{ $u->nama_kec }}</div>
                                            @endif
                                            @if($u->nama_kab)
                                            <div class="col-4 text-muted">Kabupaten</div>
                                            <div class="col-8">{{ $u->nama_kab }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Edit (struktur minimal, sesuaikan dengan modal edit kamu) --}}
                        <div class="modal fade" id="modalEdit-{{ $u->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h6 class="modal-title fw-bold">Edit: {{ $u->name }}</h6>
                                        <button type="button" class="btn-close"
                                                data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('users.update', $u->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body px-4">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="small fw-bold">Nama Lengkap</label>
                                                    <input type="text" name="name"
                                                           class="form-control"
                                                           value="{{ $u->name }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="small fw-bold">Username</label>
                                                    <input type="text" name="username"
                                                           class="form-control"
                                                           value="{{ $u->username }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="small fw-bold">Jabatan</label>
                                                    <select name="role" class="form-select" required>
                                                        @foreach(['pimpinan'=>'Pimpinan','admin'=>'Admin','pengawas_kab'=>'Pengawas Kab','koseka'=>'Koseka','pml'=>'PML'] as $val=>$lbl)
                                                            <option value="{{ $val }}"
                                                                {{ $u->role==$val?'selected':'' }}>
                                                                {{ $lbl }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Wilayah cascading --}}
                                                <div class="col-12 p-3 rounded-3"
                                                     style="background:#fff9f0;border:1px dashed #ffc107">
                                                    <label class="small fw-bold text-warning mb-2 d-block">
                                                        <i class="fas fa-map-marked-alt me-1"></i>
                                                        Update Wilayah Kerja
                                                    </label>
                                                    @if($u->kode_wilayah_tugas)
                                                    <div class="small text-muted mb-2">
                                                        Saat ini: <strong>{{ $u->kode_wilayah_tugas }}</strong>
                                                        ({{ $u->nama_sls ?? $u->nama_desa ?? $u->nama_kec ?? $u->nama_kab ?? '-' }})
                                                    </div>
                                                    @endif
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <select class="form-select form-select-sm edit-kab"
                                                                    data-uid="{{ $u->id }}">
                                                                <option value="">-- Kabupaten --</option>
                                                                @foreach($kabupaten as $kab)
                                                                    <option value="{{ $kab->kode_kab }}">
                                                                        {{ $kab->nama_kab }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select class="form-select form-select-sm"
                                                                    id="edit_kec_{{ $u->id }}"
                                                                    data-uid="{{ $u->id }}"
                                                                    disabled>
                                                                <option value="">-- Kecamatan --</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select class="form-select form-select-sm"
                                                                    id="edit_desa_{{ $u->id }}"
                                                                    data-uid="{{ $u->id }}"
                                                                    disabled>
                                                                <option value="">-- Desa/Kel --</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="id_sub_sls"
                                                                    class="form-select form-select-sm"
                                                                    id="edit_sls_{{ $u->id }}"
                                                                    disabled>
                                                                <option value="">-- RT / SLS --</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted mt-1 d-block"
                                                           style="font-size:0.65rem;">
                                                        *Biarkan kosong jika tidak ingin mengubah wilayah.
                                                    </small>
                                                </div>

                                                <div class="col-12">
                                                    <label class="small fw-bold">
                                                        Ganti Password
                                                        <span class="text-muted fw-normal">(opsional)</span>
                                                    </label>
                                                    <input type="password" name="password"
                                                           class="form-control"
                                                           placeholder="Kosongkan jika tidak ingin mengganti"
                                                           autocomplete="new-password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">Batal</button>
                                            <button type="submit"
                                                    class="btn btn-warning px-4 fw-bold">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL TAMBAH USER -->
                        <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-dark text-white">
                                        <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Registrasi Petugas Baru</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('users.store') }}" method="POST">
                                        @csrf
                                        <div class="modal-body px-4">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="small fw-bold">Nama Lengkap Petugas</label>
                                                    <input type="text" name="name" class="form-control" required placeholder="Contoh: Masayu Sarah Amelia">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Username BPS</label>
                                                    <input type="text" name="username" class="form-control" required placeholder="sarah_pml">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Password Akun</label>
                                                    <input type="password" name="password" class="form-control" required placeholder="******">
                                                </div>
                                                <div class="col-12">
                                                    <label class="small fw-bold">Jabatan (Role)</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="" disabled selected>-- Pilih Jabatan --</option>
                                                        <option value="pimpinan">Pimpinan / Kepala BPS</option>
                                                        <option value="admin">Administrator</option>
                                                        <option value="pengawas_kab">Pengawas Kabupaten</option>
                                                        <option value="koseka">Koseka</option>
                                                        <option value="pml">PML</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-12 mt-4 p-3 rounded-3" style="background-color: #f8f9fa; border: 1px dashed #dee2e6;">
                                                    <label class="small fw-bold text-primary mb-2 d-block"><i class="fas fa-map-marked-alt me-1"></i>Mapping Wilayah Kerja</label>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <select name="kode_kab" class="form-select form-select-sm" id="pilih_kab">
                                                                <option value="">-- Kabupaten --</option>
                                                                @foreach($kabupaten as $kab)
                                                                    <option value="{{ $kab->kode_kab }}">{{ $kab->nama_kab }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="kode_kec" class="form-select form-select-sm" id="pilih_kec" disabled><option value="">-- Kecamatan --</option></select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="kode_desa" class="form-select form-select-sm" id="pilih_desa" disabled><option value="">-- Desa/Kel --</option></select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="id_sub_sls" class="form-select form-select-sm" id="pilih_sls" disabled><option value="">-- RT / SLS --</option></select>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted d-block mt-2" style="font-size: 0.65rem;">*Kosongkan jika petugas memiliki akses penuh wilayah di atasnya.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Simpan Akun</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Tambahkan event listener untuk cascading di Modal TAMBAH 
    document.getElementById('pilih_kab').addEventListener('change', function() {
        if(!this.value) {
            resetDropdown(document.getElementById('pilih_kec'), 'Kecamatan');
            resetDropdown(document.getElementById('pilih_desa'), 'Desa/Kel');
            resetDropdown(document.getElementById('pilih_sls'), 'RT / SLS');
            return;
        }
        loadDropdown(document.getElementById('pilih_kec'), `/get-kecamatan/${this.value}`, 'Kecamatan');
    });

    document.getElementById('pilih_kec').addEventListener('change', function() {
        if(!this.value) {
            resetDropdown(document.getElementById('pilih_desa'), 'Desa/Kel');
            resetDropdown(document.getElementById('pilih_sls'), 'RT / SLS');
            return;
        }
        loadDropdown(document.getElementById('pilih_desa'), `/get-desa/${this.value}`, 'Desa/Kel');
    });

    document.getElementById('pilih_desa').addEventListener('change', function() {
        if(!this.value) {
            resetDropdown(document.getElementById('pilih_sls'), 'RT / SLS');
            return;
        }
        loadDropdown(document.getElementById('pilih_sls'), `/get-sls/${this.value}`, 'RT / SLS');
    });
// ── Helper ────────────────────────────────────────────────────────────────
function loadDropdown(el, url, defaultText) {
    el.innerHTML = '<option value="">Memuat...</option>';
    el.disabled  = true;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            let html = `<option value="">-- ${defaultText} --</option>`;
            data.forEach(d => { html += `<option value="${d.id}">${d.nama}</option>`; });
            el.innerHTML = html;
            el.disabled  = data.length === 0;
        })
        .catch(() => {
            el.innerHTML = `<option value="">Gagal memuat</option>`;
            el.disabled  = false;
        });
}

function resetDropdown(el, defaultText) {
    el.innerHTML = `<option value="">-- ${defaultText} --</option>`;
    el.disabled  = true;
}

// ── Filter cascading ──────────────────────────────────────────────────────
document.getElementById('f_kab').addEventListener('change', function () {
    resetDropdown(document.getElementById('f_desa'), 'Desa');
    if (!this.value) return resetDropdown(document.getElementById('f_kec'), 'Kecamatan');
    loadDropdown(document.getElementById('f_kec'), `/get-kecamatan/${this.value}`, 'Kecamatan');
});
document.getElementById('f_kec').addEventListener('change', function () {
    if (!this.value) return resetDropdown(document.getElementById('f_desa'), 'Desa');
    loadDropdown(document.getElementById('f_desa'), `/get-desa/${this.value}`, 'Desa');
});

// ── Edit cascading ────────────────────────────────────────────────────────
document.querySelectorAll('.edit-kab').forEach(sel => {
    sel.addEventListener('change', function () {
        const uid = this.dataset.uid;
        const kecEl  = document.getElementById(`edit_kec_${uid}`);
        const desaEl = document.getElementById(`edit_desa_${uid}`);
        const slsEl  = document.getElementById(`edit_sls_${uid}`);
        resetDropdown(desaEl, 'Desa/Kel');
        resetDropdown(slsEl,  'RT / SLS');
        if (!this.value) return resetDropdown(kecEl, 'Kecamatan');
        loadDropdown(kecEl, `/get-kecamatan/${this.value}`, 'Kecamatan');
    });
});
document.querySelectorAll('[id^="edit_kec_"]').forEach(sel => {
    sel.addEventListener('change', function () {
        const uid    = this.id.replace('edit_kec_', '');
        const desaEl = document.getElementById(`edit_desa_${uid}`);
        const slsEl  = document.getElementById(`edit_sls_${uid}`);
        resetDropdown(slsEl, 'RT / SLS');
        if (!this.value) return resetDropdown(desaEl, 'Desa/Kel');
        loadDropdown(desaEl, `/get-desa/${this.value}`, 'Desa/Kel');
    });
});
document.querySelectorAll('[id^="edit_desa_"]').forEach(sel => {
    sel.addEventListener('change', function () {
        const uid   = this.id.replace('edit_desa_', '');
        const slsEl = document.getElementById(`edit_sls_${uid}`);
        if (!this.value) return resetDropdown(slsEl, 'RT / SLS');
        loadDropdown(slsEl, `/get-sls/${this.value}`, 'RT / SLS');
    });
});


</script>
@endsection