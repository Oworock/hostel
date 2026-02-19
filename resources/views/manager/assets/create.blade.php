<x-dashboard-layout title="Add Asset">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page space-y-6">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Add Asset</h1>
            <p class="text-slate-600 dark:text-slate-300">Add new assets to hostels you manage.</p>
        </div>

        <section class="uniform-card p-6">
            <div class="rounded-lg border border-amber-300 bg-amber-50 text-amber-900 p-3 mb-5 dark:bg-amber-900/30 dark:border-amber-700 dark:text-amber-100">
                Assets created by managers cannot be edited by managers. If updates are needed, contact admin.
            </div>

            <form method="POST" action="{{ route('manager.assets.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <select name="hostel_id" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" required>
                    <option value="">Select hostel</option>
                    @foreach(auth()->user()->managedHostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="name" placeholder="Asset name" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900" required>
                <input type="text" name="asset_number" placeholder="Asset number" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="asset_code" placeholder="Asset code" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="category" placeholder="Category" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="brand" placeholder="Brand" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="model" placeholder="Model" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="manufacturer" placeholder="Manufacturer" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="serial_number" placeholder="Serial number" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="location" placeholder="Current location" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="supplier" placeholder="Supplier" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="invoice_reference" placeholder="Invoice/Reference" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="text" name="maintenance_schedule" placeholder="Maintenance schedule" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <input type="number" step="0.01" min="0" name="acquisition_cost" placeholder="Acquisition cost ({{ getCurrencySymbol() }})" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900">
                <select name="condition" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900" required>
                    <option value="good">Good</option>
                    <option value="excellent">Excellent</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                </select>
                <input type="file" name="image" accept="image/*" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 md:col-span-2">
                <textarea name="notes" rows="2" placeholder="Notes" class="md:col-span-3 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"></textarea>
                <div class="md:col-span-3 flex items-center gap-3">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Add Asset</button>
                    <a href="{{ route('manager.assets.index') }}" class="bg-slate-200 text-slate-900 px-4 py-2 rounded-lg hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Back to Assets</a>
                </div>
            </form>
        </section>
    </div>
</x-dashboard-layout>
