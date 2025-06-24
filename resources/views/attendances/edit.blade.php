@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Absensi - {{ $attendance->user->name }}</h4>
                    <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Karyawan</h6>
                            <p class="mb-1"><strong>Nama:</strong> {{ $attendance->user->name }}</p>
                            <p class="mb-1"><strong>Role:</strong> {{ ucfirst($attendance->user->role) }}</p>
                            <p class="mb-0"><strong>Tanggal:</strong> {{ $attendance->date->format('l, d F Y') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('attendances.update', $attendance) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in" class="form-label">Waktu Check In</label>
                                    <input type="time" 
                                           class="form-control @error('check_in') is-invalid @enderror" 
                                           id="check_in" 
                                           name="check_in" 
                                           value="{{ old('check_in', $attendance->check_in ? $attendance->check_in->format('H:i') : '') }}">
                                    @error('check_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Kosongkan jika tidak check in</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out" class="form-label">Waktu Check Out</label>
                                    <input type="time" 
                                           class="form-control @error('check_out') is-invalid @enderror" 
                                           id="check_out" 
                                           name="check_out" 
                                           value="{{ old('check_out', $attendance->check_out ? $attendance->check_out->format('H:i') : '') }}">
                                    @error('check_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Kosongkan jika belum check out</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Hadir</option>
                                <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="sick" {{ old('status', $attendance->status) == 'sick' ? 'selected' : '' }}>Sakit</option>
                                <option value="permission" {{ old('status', $attendance->status) == 'permission' ? 'selected' : '' }}>Izin</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Catatan tambahan...">{{ old('notes', $attendance->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Informasi Tambahan</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Jam Kerja Normal:</strong> 08:00 - 17:00<br>
                                            <strong>Batas Terlambat:</strong> Setelah 08:00
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Dicatat:</strong> {{ $attendance->created_at->format('d/m/Y H:i') }}<br>
                                            <strong>Update Terakhir:</strong> {{ $attendance->updated_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Absensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const statusSelect = document.getElementById('status');

    function updateStatus() {
        const checkIn = checkInInput.value;
        const status = statusSelect.value;

        // Auto-detect late status if check-in is after 08:00
        if (checkIn && checkIn > '08:00' && (status === 'present' || status === 'late')) {
            statusSelect.value = 'late';
        } else if (checkIn && checkIn <= '08:00' && status === 'late') {
            statusSelect.value = 'present';
        }
    }

    function validateTimes() {
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;

        if (checkIn && checkOut && checkOut <= checkIn) {
            checkOutInput.setCustomValidity('Waktu check out harus setelah check in');
        } else {
            checkOutInput.setCustomValidity('');
        }
    }

    checkInInput.addEventListener('change', function() {
        updateStatus();
        validateTimes();
    });

    checkOutInput.addEventListener('change', validateTimes);
});
</script>
@endsection
