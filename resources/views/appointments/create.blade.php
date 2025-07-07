@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Buat Appointment Baru</h4>
                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('appointments.store') }}">
                        @csrf

                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD() || auth()->user()->isFrontOffice()))
                            <div class="mb-3">
                                <label for="patient_id" class="form-label">Pasien <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">Pilih Pasien</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} ({{ $patient->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">Pasien</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="treatment_id" class="form-label">Treatment <span class="text-danger">*</span></label>
                            <select class="form-select @error('treatment_id') is-invalid @enderror" id="treatment_id" name="treatment_id" required>
                                <option value="">Pilih Treatment</option>
                                @foreach($treatments as $treatment)
                                    <option value="{{ $treatment->id }}" 
                                            data-price="{{ $treatment->price }}"
                                            {{ (old('treatment_id', $selectedTreatment) == $treatment->id) ? 'selected' : '' }}>
                                        {{ $treatment->name }} - Rp {{ number_format($treatment->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('treatment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD() || auth()->user()->isFrontOffice()))
                            <div class="mb-3">
                                <label for="staff_id" class="form-label">Staff (Opsional)</label>
                                <select class="form-select @error('staff_id') is-invalid @enderror" id="staff_id" name="staff_id">
                                    <option value="">Pilih Staff</option>
                                    @foreach($staff as $staffMember)
                                        <option value="{{ $staffMember->id }}" {{ old('staff_id') == $staffMember->id ? 'selected' : '' }}>
                                            {{ $staffMember->name }} ({{ ucfirst($staffMember->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('staff_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kosongkan jika akan diassign nanti</div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Tanggal & Waktu Appointment <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   class="form-control @error('appointment_date') is-invalid @enderror" 
                                   id="appointment_date" 
                                   name="appointment_date" 
                                   value="{{ old('appointment_date') }}"
                                   min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                                   required>
                            @error('appointment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimal 1 jam dari sekarang</div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Catatan tambahan untuk appointment">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Ringkasan Appointment</h6>
                                    <div id="appointment-summary">
                                        <p class="mb-1"><strong>Treatment:</strong> <span id="selected-treatment">-</span></p>
                                        <p class="mb-0"><strong>Total Harga:</strong> <span id="total-price">Rp 0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Buat Appointment
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
    const treatmentSelect = document.getElementById('treatment_id');
    const selectedTreatmentSpan = document.getElementById('selected-treatment');
    const totalPriceSpan = document.getElementById('total-price');

    function updateSummary() {
        const selectedOption = treatmentSelect.options[treatmentSelect.selectedIndex];
        if (selectedOption.value) {
            const treatmentName = selectedOption.text.split(' - ')[0];
            const price = selectedOption.getAttribute('data-price');
            
            selectedTreatmentSpan.textContent = treatmentName;
            totalPriceSpan.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        } else {
            selectedTreatmentSpan.textContent = '-';
            totalPriceSpan.textContent = 'Rp 0';
        }
    }

    treatmentSelect.addEventListener('change', updateSummary);
    
    // Update on page load if there's a selected value
    if (treatmentSelect.value) {
        updateSummary();
    }
});
</script>
@endsection
