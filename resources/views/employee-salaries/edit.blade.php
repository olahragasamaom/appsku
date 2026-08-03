@extends('layouts.admin')

@section('title', 'Edit Gaji Karyawan')

@section('breadcrumb')
    <a href="{{ route('employee-salaries.index') }}" class="text-slate-500 hover:text-primary-600">Gaji Karyawan</a>
    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-medium">Edit Gaji</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Edit Gaji Karyawan</h1>
            <p class="text-secondary-500 mt-1">Perbarui pengaturan gaji karyawan.</p>
        </div>
        <a href="{{ route('employee-salaries.index') }}" class="btn btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>
@endsection

@section('content')
    <form action="{{ route('employee-salaries.update', $employeeSalary) }}" method="POST"
          x-data="{ paymentMethod: '{{ old('payment_method', $employeeSalary->payment_method) }}' }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Info --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Dasar</h3>
                    </div>
                    <div class="card-body space-y-4">
                        {{-- Employee --}}
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-secondary-700 mb-1">
                                Karyawan <span class="text-danger-500">*</span>
                            </label>
                            <select name="employee_id" id="employee_id"
                                    class="input w-full @error('employee_id') border-danger-500 @enderror" required>
                                <option value="">Pilih Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id', $employeeSalary->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->full_name }} ({{ $employee->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Basic Salary --}}
                            <div x-data="currencyInput({{ old('basic_salary', $employeeSalary->basic_salary) }})">
                                <label for="basic_salary_display" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Gaji Pokok <span class="text-danger-500">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="basic_salary_display" x-model="display" @input="updateValue($event)"
                                           class="input @error('basic_salary') border-danger-500 @enderror"
                                           placeholder="0" inputmode="numeric" required>
                                    <input type="hidden" name="basic_salary" :value="value">
                                </div>
                                @error('basic_salary')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Effective Date --}}
                            <div>
                                <label for="effective_date" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Berlaku Sejak <span class="text-danger-500">*</span>
                                </label>
                                <input type="date" name="effective_date" id="effective_date" value="{{ old('effective_date', $employeeSalary->effective_date->format('Y-m-d')) }}"
                                       class="input w-full @error('effective_date') border-danger-500 @enderror" required>
                                @error('effective_date')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label for="notes" class="block text-sm font-medium text-secondary-700 mb-1">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="2"
                                      class="input w-full @error('notes') border-danger-500 @enderror"
                                      placeholder="Catatan opsional...">{{ old('notes', $employeeSalary->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Metode Pembayaran</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-3">
                                Metode <span class="text-danger-500">*</span>
                            </label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod"
                                           class="w-4 h-4 text-primary-600 border-secondary-300 focus:ring-primary-500">
                                    <span class="text-sm text-secondary-700">Transfer Bank</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="cash" x-model="paymentMethod"
                                           class="w-4 h-4 text-primary-600 border-secondary-300 focus:ring-primary-500">
                                    <span class="text-sm text-secondary-700">Tunai</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="paymentMethod === 'transfer'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Bank Name --}}
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Nama Bank <span class="text-danger-500">*</span>
                                </label>
                                <select name="bank_name" id="bank_name"
                                        class="input w-full @error('bank_name') border-danger-500 @enderror">
                                    <option value="">Pilih Bank</option>
                                    <option value="BCA" {{ old('bank_name', $employeeSalary->bank_name) === 'BCA' ? 'selected' : '' }}>BCA</option>
                                    <option value="Mandiri" {{ old('bank_name', $employeeSalary->bank_name) === 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                                    <option value="BNI" {{ old('bank_name', $employeeSalary->bank_name) === 'BNI' ? 'selected' : '' }}>BNI</option>
                                    <option value="BRI" {{ old('bank_name', $employeeSalary->bank_name) === 'BRI' ? 'selected' : '' }}>BRI</option>
                                    <option value="CIMB Niaga" {{ old('bank_name', $employeeSalary->bank_name) === 'CIMB Niaga' ? 'selected' : '' }}>CIMB Niaga</option>
                                    <option value="Permata" {{ old('bank_name', $employeeSalary->bank_name) === 'Permata' ? 'selected' : '' }}>Permata</option>
                                    <option value="Danamon" {{ old('bank_name', $employeeSalary->bank_name) === 'Danamon' ? 'selected' : '' }}>Danamon</option>
                                    <option value="Other" {{ old('bank_name', $employeeSalary->bank_name) === 'Other' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('bank_name')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Account Number --}}
                            <div>
                                <label for="bank_account_number" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Nomor Rekening <span class="text-danger-500">*</span>
                                </label>
                                <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $employeeSalary->bank_account_number) }}"
                                       class="input w-full @error('bank_account_number') border-danger-500 @enderror"
                                       placeholder="1234567890">
                                @error('bank_account_number')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Account Name --}}
                            <div class="md:col-span-2">
                                <label for="bank_account_name" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Nama Pemilik Rekening
                                </label>
                                <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $employeeSalary->bank_account_name) }}"
                                       class="input w-full @error('bank_account_name') border-danger-500 @enderror"
                                       placeholder="Nama sesuai buku rekening">
                                @error('bank_account_name')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary Components --}}
                @if($salaryComponents->count() > 0)
                    @php
                        $currentComponents = $employeeSalary->components->keyBy('salary_component_id');
                    @endphp
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Komponen Gaji</h3>
                        </div>
                        <div class="card-body">
                            {{-- Earnings --}}
                            @php $earnings = $salaryComponents->where('type', 'earning'); @endphp
                            @if($earnings->count() > 0)
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-secondary-700 mb-3 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-success-500"></span>
                                        Pendapatan / Tunjangan
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach($earnings as $comp)
                                            @php $existingAmount = $currentComponents->get($comp->id)?->amount ?? $comp->default_amount ?? 0; @endphp
                                            <div class="flex items-center gap-4" x-data="currencyInput({{ old("components.{$comp->id}.amount", $existingAmount) }})">
                                                <div class="flex-1">
                                                    <label class="text-sm text-secondary-600">
                                                        {{ $comp->name }}
                                                        @if($comp->code)
                                                            <span class="text-secondary-400">({{ $comp->code }})</span>
                                                        @endif
                                                    </label>
                                                </div>
                                                <div class="w-52">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" x-model="display" @input="updateValue($event)"
                                                               class="input" placeholder="0" inputmode="numeric">
                                                        <input type="hidden" name="components[{{ $comp->id }}][amount]" :value="value">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Deductions --}}
                            @php $deductions = $salaryComponents->where('type', 'deduction'); @endphp
                            @if($deductions->count() > 0)
                                <div>
                                    <h4 class="text-sm font-medium text-secondary-700 mb-3 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-danger-500"></span>
                                        Potongan
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach($deductions as $comp)
                                            @php $existingAmount = $currentComponents->get($comp->id)?->amount ?? $comp->default_amount ?? 0; @endphp
                                            <div class="flex items-center gap-4" x-data="currencyInput({{ old("components.{$comp->id}.amount", $existingAmount) }})">
                                                <div class="flex-1">
                                                    <label class="text-sm text-secondary-600">
                                                        {{ $comp->name }}
                                                        @if($comp->code)
                                                            <span class="text-secondary-400">({{ $comp->code }})</span>
                                                        @endif
                                                    </label>
                                                </div>
                                                <div class="w-52">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" x-model="display" @input="updateValue($event)"
                                                               class="input" placeholder="0" inputmode="numeric">
                                                        <input type="hidden" name="components[{{ $comp->id }}][amount]" :value="value">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500"
                                   {{ old('is_active', $employeeSalary->is_active) ? 'checked' : '' }}>
                            <div>
                                <label for="is_active" class="text-sm font-medium text-secondary-700">
                                    Aktif
                                </label>
                                <p class="text-sm text-secondary-500">Tandai sebagai gaji aktif karyawan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-ghost w-full mt-2">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
