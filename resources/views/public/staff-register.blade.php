@extends('layouts.app')

@section('title', __('Staff Registration'))

@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Staff Registration') }}</h1>
        <p class="text-slate-600 dark:text-slate-300 mt-2">{{ $config['intro'] }}</p>

        @if (session('success'))
            <div class="mt-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <p class="font-semibold">{{ __('Please correct the errors and submit again.') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('staff.register.store', ['token' => $token]) }}" enctype="multipart/form-data" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ generalStaff: {{ old('is_general_staff', 1) ? 'true' : 'false' }} }">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_full_name'] }}</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_email'] }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_phone'] }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_bank_name'] }}</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_bank_account_name'] }}</label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_bank_account_number'] }}</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            </div>

            @if($config['show_department'])
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_department'] }}</label>
                    @if(!empty($config['department_options']))
                        <select name="department" @required($config['require_department']) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('Select option') }}</option>
                            @foreach($config['department_options'] as $department)
                                <option value="{{ $department }}" @selected((string) old('department') === (string) $department)>{{ $department }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="department" value="{{ old('department') }}" @required($config['require_department']) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    @endif
                </div>
            @endif

            @if(!empty($config['show_category']))
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_category'] }}</label>
                    @if(!empty($config['category_options']))
                        <select name="category" @required(!empty($config['require_category'])) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('Select option') }}</option>
                            @foreach($config['category_options'] as $category)
                                <option value="{{ $category }}" @selected((string) old('category') === (string) $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="category" value="{{ old('category') }}" @required(!empty($config['require_category'])) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    @endif
                </div>
            @endif

            @if($config['show_job_title'])
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_job_title'] }}</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}" @required($config['require_job_title']) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>
            @endif

            @if($config['show_address'])
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_address'] }}</label>
                    <textarea name="address" rows="3" @required($config['require_address']) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">{{ old('address') }}</textarea>
                </div>
            @endif

            @if($config['show_hostel_selector'] && !empty($hostels))
                <div class="md:col-span-2 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="is_general_staff" value="1" x-model="generalStaff" @checked(old('is_general_staff', 1)) class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500">
                        <span>{{ $config['label_general_staff'] }}</span>
                    </label>
                    <div x-show="!generalStaff" x-transition>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_assigned_hostel'] }}</label>
                        <select name="assigned_hostel_id" :required="!generalStaff && {{ !empty($config['require_hostel_selector']) ? 'true' : 'false' }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('Select hostel') }}</option>
                            @foreach($hostels as $hostelId => $hostelName)
                                <option value="{{ $hostelId }}" @selected((string) old('assigned_hostel_id') === (string) $hostelId)>{{ $hostelName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            @if($config['show_profile_image'])
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $config['label_profile_image'] }}</label>
                    <input type="file" name="profile_image" accept="image/*" @required($config['require_profile_image']) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>
            @endif

            @if(!empty($config['custom_fields']) && is_array($config['custom_fields']))
                @foreach($config['custom_fields'] as $field)
                    @php
                        $fieldKey = (string) ($field['key'] ?? '');
                        $fieldType = (string) ($field['type'] ?? 'text');
                        $fieldLabel = (string) ($field['label'] ?? ucfirst(str_replace('_', ' ', $fieldKey)));
                        $isRequired = !empty($field['required']);
                        $placeholder = (string) ($field['placeholder'] ?? '');
                        $options = is_array($field['options'] ?? null) ? $field['options'] : [];
                    @endphp
                    @if($fieldKey !== '')
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ $fieldLabel }}</label>
                            @if($fieldType === 'textarea')
                                <textarea name="custom[{{ $fieldKey }}]" rows="3" @required($isRequired) placeholder="{{ $placeholder }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">{{ old('custom.' . $fieldKey) }}</textarea>
                            @elseif($fieldType === 'select')
                                <select name="custom[{{ $fieldKey }}]" @required($isRequired) class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                    <option value="">{{ __('Select option') }}</option>
                                    @foreach($options as $option)
                                        <option value="{{ $option }}" @selected((string) old('custom.' . $fieldKey) === (string) $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ in_array($fieldType, ['email', 'number', 'date'], true) ? $fieldType : 'text' }}"
                                       name="custom[{{ $fieldKey }}]"
                                       value="{{ old('custom.' . $fieldKey) }}"
                                       @required($isRequired)
                                       placeholder="{{ $placeholder }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            @endif
                        </div>
                    @endif
                @endforeach
            @endif

            <div class="md:col-span-2">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">
                    {{ __('Submit Registration') }}
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
